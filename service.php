<?php

use Phalcon\DI\FactoryDefault;

class Service
{
	/**
	 * Get the current raffle
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _main(Request $request, Response $response)
	{
		// get the current raffle
		$raffle = Connection::query("SELECT * FROM raffle WHERE CURRENT_TIMESTAMP BETWEEN start_date AND end_date");

		// show notice if there is no open raffle
		if (empty($raffle)) {
			$response->setCache("300");
			return $response->setTemplate('message.ejs', [
				"header"=>"No hay rifas abiertas",
				"icon"=>"sentiment_very_dissatisfied",
				"text" => "Lo sentimos, no hay ninguna Rifa abierta ahora mismo. Pruebe nuevamente en algunos días.",
				"button" => ["href"=>"RIFA GANADORES", "caption"=>"Ver ganadores"]
			]);
		}

		// get the image of the raffle
		$raffle = $raffle[0];
		$di = FactoryDefault::getDefault();
		$image = $di->get('path')['root']."/public/raffle/".md5($raffle->raffle_id).".jpg";
		$raffle->image = basename($image);

		// get number of tickets adquired by the user
		$userTickets = Connection::query("SELECT COUNT(ticket_id) AS tickets FROM ticket WHERE raffle_id is NULL AND person_id = '{$request->person->id}'");
		$raffle->tickets = (int) $userTickets[0]->tickets;

		// calculate minutes till the end of raffle
		$monthEnd = strtotime(date("Y-m-t 23:59:59"));
		$minsUntilMonthEnd = ceil(($monthEnd - time()) / 60);

		// create the user Response
		$response->setCache($minsUntilMonthEnd);
		$content = ["raffle" => $raffle, "credit" => $request->person->credit];
		$response->setTemplate("home.ejs", $content, [$image]);
	}

	/**
	 * Sell tickets for the raffle
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _tickets(Request $request, Response $response)
	{
		// create content structure
		$content = ["credit" => $request->person->credit];

		// create the user Response
		$response->setCache("year");
		$response->setTemplate("tickets.ejs", $content);
	}

	/**
	 * Display the list of winners
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _ganadores(Request $request, Response $response)
	{
		// get all raffles
		$raffles = Connection::query("
			SELECT start_date, winner_1, winner_2, winner_3
			FROM raffle
			WHERE winner_1 <> ''
			ORDER BY start_date DESC
			LIMIT 6");

		$images = [];
		foreach ($raffles as $raffle) {
			// get username
			$raffle->winner_1 = Social::prepareUserProfile(Utils::getPerson($raffle->winner_1));
			$raffle->winner_2 = Social::prepareUserProfile(Utils::getPerson($raffle->winner_2));
			$raffle->winner_3 = Social::prepareUserProfile(Utils::getPerson($raffle->winner_3));

			// get images
			if ($raffle->winner_1->picture) $images[] = $raffle->winner_1->picture;
			if ($raffle->winner_2->picture) $images[] = $raffle->winner_2->picture;
			if ($raffle->winner_3->picture) $images[] = $raffle->winner_3->picture;
		}

		// calculate minutes till the end of raffle
		$monthEnd = strtotime(date("Y-m-t 23:59:59"));
		$minsUntilMonthEnd = ceil(($monthEnd - time()) / 60);

		// create the final user Response
		$response->setCache($minsUntilMonthEnd);
		$response->setTemplate("winners.ejs", ["winners" => $raffles], $images);
	}

	/**
	 * Pay for an item and add the items to the database
	 *
	 * @param Request
	 * @param Response
	 * @throws Exception
	 */
	public function _pay(Request $request, Response $response)
	{
		// get the amulet to purchase
		$code = $request->input->data->code;
		$isError = false;

		// check the code exists
		$codes = ['1TICKET' => 1, '5TICKETS' => 5, '10TICKETS' => 10];
		if(!isset($codes[$code])) $isError = true;

		// process the payment
		try {
			MoneyNew::buy($request->person->id, $code);

			Challenges::complete("buy-raffle-tickets", $request->person->id);

		} catch (Exception $e) { $isError = true; }

		// message if errors were found
		if($isError) {
			return $response->setTemplate('message.ejs', [
				"header"=>"Error inesperado",
				"icon"=>"sentiment_very_dissatisfied",
				"text" => "Hemos encontrado un error procesando su canje. Por favor intente nuevamente, si el problema persiste, escríbanos al soporte.",
				"button" => ["href"=>"RIFA TICKETS", "caption"=>"Reintentar"]
			]);
		}

		// create SQL to add the tickets
		$vals = [];
		for ($i=0; $i<$codes[$code]; $i++) $vals[] = "('PURCHASE','{$request->person->id}')";
		$sql = implode(",", $vals);

		// add tickets to the database
		Connection::query("INSERT INTO ticket (origin,person_id) VALUES $sql;");

		// add the experience
		Level::setExperience('RAFFLE_BUY_FIRST_TICKET', $request->person->id);

		// possitive response (with seed to avoid cache)
		$seed = date('Hms') . rand(100, 999);
		return $response->setTemplate('message.ejs', [
			"header"=>"Canje realizado",
			"icon"=>"sentiment_very_satisfied",
			"text" => "Su canje se ha realizado satisfactoriamente. Usted ha recibido {$codes[$code]} ticket(s) para la rifa en curso. ¡Buena suerte!",
			"button" => ["href"=>"RIFA $seed", "caption"=>"Ver rifa"]
		]);
	}
}
