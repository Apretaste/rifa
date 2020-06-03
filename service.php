<?php

use Apretaste\Challenges;
use Apretaste\Money;
use Apretaste\Request;
use Apretaste\Response;
use Framework\Alert;
use Framework\Database;
use Apretaste\Person;
use Apretaste\Level;

class Service
{
	/**
	 * Get the current raffle
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _main(Request $request, Response &$response)
	{
		// get the current raffle
		$raffle = Database::query('SELECT * FROM raffle WHERE CURRENT_TIMESTAMP BETWEEN start_date AND end_date');

		// show notice if there is no open raffle
		if (empty($raffle)) {
			$response->setCache('300');
			return $response->setTemplate('message.ejs', [
				'header' => 'No hay rifas abiertas',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => 'Lo sentimos, no hay ninguna Rifa abierta ahora mismo. Pruebe nuevamente en algunos días.',
				'button' => ['href' => 'RIFA GANADORES', 'caption' => 'Ver ganadores']
			]);
		}

		// get the image of the raffle
		$raffle = $raffle[0];
		$image = SHARED_PUBLIC_PATH . "raffle/" . md5($raffle->raffle_id) . ".jpg";
		$raffle->end_date = strftime('%e de %B del %Y', strtotime($raffle->end_date));
		$raffle->image = basename($image);

		// get number of tickets adquired by the user
		$userTickets = Database::query("SELECT COUNT(ticket_id) AS tickets FROM ticket WHERE raffle_id is NULL AND person_id = '{$request->person->id}'");
		$raffle->tickets = (int) $userTickets[0]->tickets;

		// calculate minutes till the end of raffle
		$monthEnd = strtotime(date('Y-m-t 23:59:59'));
		$minsUntilMonthEnd = ceil(($monthEnd - time()) / 60);

		// data to send to the view
		$content = ['raffle' => $raffle, 'credit' => $request->person->credit];

		// create the user Response
		$response->setCache($minsUntilMonthEnd);
		$response->setTemplate('home.ejs', $content, [$image]);
	}

	/**
	 * Sell tickets for the raffle
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _tickets(Request $request, Response &$response)
	{
		// create content structure
		$content = ['credit' => $request->person->credit];

		// create the user Response
		$response->setCache('year');
		$response->setTemplate('tickets.ejs', $content);
	}

	/**
	 * Display the list of winners
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _ganadores(Request $request, Response &$response)
	{
		// get all raffles
		$raffles = Database::query("
			SELECT start_date, 
				(select email from person where person.id = raffle.winner1) AS winner1, 
				(select email from person where person.id = raffle.winner2) AS winner2,
				(select email from person where person.id = raffle.winner3) AS winner3
			FROM raffle
			WHERE winner1 <> ''
			ORDER BY start_date DESC
			LIMIT 6");

		// create content to send to the view
		$winners = [];
		foreach ($raffles as $raffle) {
			// create the item for the content
			$item = new \stdClass();
			$item->startDate = ucfirst(strftime('%B %Y', strtotime($raffle->start_date)));

			// get winner #1 details
			$winner1 = Person::find($raffle->winner1);
			$item->w1Username = $winner1->username;
			$item->w1Avatar = $winner1->avatar;
			$item->w1AvatarColor = $winner1->avatarColor;

			// get winner #2 details
			$winner2 = Person::find($raffle->winner2);
			$item->w2Username = $winner2->username;
			$item->w2Avatar = $winner2->avatar;
			$item->w2AvatarColor = $winner2->avatarColor;

			// get winner #3 details
			$winner3 = Person::find($raffle->winner3);
			$item->w3Username = $winner3->username;
			$item->w3Avatar = $winner3->avatar;
			$item->w3AvatarColor = $winner3->avatarColor;

			// add to the content
			$winners[] = $item;
		}

		// calculate minutes till the end of raffle
		$monthEnd = strtotime(date('Y-m-t 23:59:59'));
		$minsUntilMonthEnd = ceil(($monthEnd - time()) / 60);

		// create the final user Response
		$response->setCache($minsUntilMonthEnd);
		$response->setTemplate('winners.ejs', ['winners' => $winners]);
	}

	/**
	 * Pay for an item and add the items to the database
	 *
	 * @param Request
	 * @param Response
	 *
	 * @return Response
	 * @throws Exception
	 */
	public function _pay(Request $request, Response &$response)
	{
		// get the amulet to purchase
		$code = $request->input->data->code;
		$isError = false;

		// check the code exists
		$codes = ['1TICKET' => 1, '5TICKETS' => 5, '10TICKETS' => 10];
		if (!isset($codes[$code])) {
			$isError = true;
		}

		// process the payment
		try {
			Money::purchase($request->person->id, $code);

			Challenges::complete('buy-raffle-tickets', $request->person->id);
		} catch (Exception $e) {
			if ($e->getCode() === 532) {
				$response->setTemplate('message.ejs', [
				  'header' => 'No tienes suficiente cr&eacute;dito',
				  'icon' => 'sentiment_very_dissatisfied',
				  'text' => 'Tu cr&eacute;dito es insuficiente para comprar tickets',
				  'button' => ['href' => 'CREDITO', 'caption' => 'Revisa tu cr&eacute;dito']
				]);
				return;
			}

			$response->setTemplate('message.ejs', [
			  'header' => 'Error inesperado',
			  'icon' => 'sentiment_very_dissatisfied',
			  'text' => 'Hemos encontrado un error procesando su canje. Por favor intente nuevamente, si el problema persiste, escríbanos al soporte.',
			  'button' => ['href' => 'RIFA TICKETS', 'caption' => 'Reintentar']
			]);

			// post message for the developers
			$alert = new Alert($e->getCode(), 'RIFA: ' . $e->getMessage());
			return $alert->post();
		}

		// create SQL to add the tickets
		$vals = [];
		for ($i = 0; $i < $codes[$code]; $i++) {
			$vals[] = "('PURCHASE','{$request->person->id}')";
		}
		$sql = implode(',', $vals);

		// add tickets to the database
		Database::query("INSERT INTO ticket (origin,person_id) VALUES $sql;");

		// add the experience
		Level::setExperience('RAFFLE_BUY_FIRST_TICKET', $request->person->id);

		// possitive response (with seed to avoid cache)
		$seed = date('Hms') . rand(100, 999);

		return $response->setTemplate('message.ejs', [
			"header" => "Canje realizado",
			"icon" => "sentiment_very_satisfied",
			"text" => "Su canje se ha realizado satisfactoriamente. Usted ha recibido {$codes[$code]} ticket(s) para la rifa en curso. ¡Buena suerte!",
			"button" => ["href" => "RIFA $seed", "caption" => "Ver rifa"]
		]);
	}
}
