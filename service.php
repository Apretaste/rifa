<?php

use Apretaste\Level;
use Apretaste\Money;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Tutorial;
use Apretaste\Challenges;
use Apretaste\Notifications;
use Framework\Database;

class Service
{
	private $currentRaffle;

	/**
	 * pick the right raffle, if before 7PM, today, else tomorrow
	 *
	 * @author salvipascual
	 */
	public function __construct()
	{
		$this->currentRaffle = (date('H') < 19) ? date('Y-m-d') : date('Y-m-d', strtotime('tomorrow'));
	}

	/**
	 * Play the raffle
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 */
	public function _main(Request $request, Response $response)
	{
		// get values your tickets
		$res = Database::queryFirst("SELECT tickets FROM _rifa_tickets WHERE person_id = {$request->person->id} AND raffle = '{$this->currentRaffle}'");
		$tickets = empty($res->tickets) ? 0 : $res->tickets;

		// get all the tickets playing
		$res = Database::queryFirst("SELECT SUM(tickets) AS total FROM _rifa_tickets WHERE raffle = '{$this->currentRaffle}'");
		$playing = empty($res->total) ? 0 : $res->total;

		// create content structure
		$content = [
			'raffle' => $this->currentRaffle,
			'playing' => $playing,
			'tickets' => $tickets,
			'chances' => ($playing > 0) ? ($tickets * 100) / $playing : 0,
			'credit' => $request->person->credit
		];

		// send data to the view
		$response->setCache();
		$response->setTemplate('rifa.ejs', $content);
	}

	/**
	 * List of winners
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 */
	public function _ganadores(Request $request, Response $response)
	{
		// get last 20 winners
		$ganadores = Database::query("
			SELECT A.raffle, A.person_tickets, B.gender, B.username, B.avatar, B.avatarColor
			FROM _rifa_winners A JOIN person B
			ON A.person_id = B.id
			ORDER BY A.raffle DESC
			LIMIT 18");

		// create the final user Response
		$response->setCache();
		$response->setTemplate('ganadores.ejs', ["ganadores" => $ganadores]);
	}

	/**
	 * Read the rules
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 */
	public function _reglas(Request $request, Response $response)
	{
		$response->setCache('year');
		$response->setTemplate('reglas.ejs');
	}

	/**
	 * Purchase tickets
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 */
	public function _comprar(Request $request, Response $response)
	{
		// get amounts of tickets to purchase
		$tickets = (int) $request->input->data->tickets;

		// create the message
		$message = "$tickets tickets para la rifa del " . strftime("%e %B");

		try {
			// process the payment
			Money::send($request->person->id, Money::BANK, $tickets, $message);
		} catch (Exception $e) {
			return $response->setTemplate('message.ejs', [
				'header' => 'Error inesperado',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => 'Hemos encontrado un error canjeando sus tickets. Por favor intente nuevamente, si el problema persiste, escríbanos al soporte.',
				'button' => ['href' => 'RIFA', 'caption' => 'Regresar']
			]);
		}

		// notify the buyer
		Notifications::alert($request->person->id, "Canjeó $message", 'local_play', '{"command":"RIFA"}');

		// complete the challenge
		Challenges::complete('buy-raffle-tickets', $request->person->id);

		// complete tutorial
		Tutorial::complete($request->person->id, 'raffle_ticket');

		// add the experience
		Level::setExperience('RAFFLE_BUY_FIRST_TICKET', $request->person->id);

		// add tickets to the database
		Database::query("
			INSERT INTO _rifa_tickets (raffle, person_id, tickets) 
			VALUES ('{$this->currentRaffle}', {$request->person->id}, $tickets)
			ON DUPLICATE KEY UPDATE tickets = tickets + $tickets");

		// possitive response (with seed to avoid cache)
		$seed = date('Hms') . rand(100, 999);
		return $response->setTemplate('message.ejs', [
			"header" => "¡Tickets adquiridos!",
			"icon" => "sentiment_very_satisfied",
			"text" => "Su canje se ha realizado satisfactoriamente y usted ha obtenido $message. ¡Buena suerte!",
			"button" => ["href" => "RIFA $seed", "caption" => "Ver rifa"]
		]);
	}
}
