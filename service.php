<?php

class Rifa extends Service
{
	/**
	 * Get the latest raffle
	 *
	 * @param Request
	 * @return Response
	 */
	public function _main(Request $request)
	{
		// set Spanish so the date come in Spanish
		setlocale(LC_TIME, "es_ES");

		// get the current raffle
		$raffle = $this->utils->getCurrentRaffle();

		// show message if there is no open raffle
		if(empty($raffle))
		{
			$response = new Response();
			$response->subject = "No hay ninguna Rifa abierta";
			$response->createFromText("Lo sentimos, no hay ninguna Rifa abierta ahora mismo. Pruebe nuevamente en algunos d&iacute;as.");
			return $response;
		}

		// get number of tickets adquired by the user
		$connection = new Connection();
		$userTickets = $connection->query("SELECT count(ticket_id) as tickets FROM ticket WHERE raffle_id is NULL AND email = '{$request->email}'");
		$userTickets = $userTickets[0]->tickets;

		// create a json object to send to the template
		$responseContent = array(
			"description" => $raffle->item_desc,
			"startDate" => $raffle->start_date,
			"endDate" => $raffle->end_date,
			"tickets" => $raffle->tickets,
			"image" => $raffle->image,
			"userTickets" => $userTickets
		);

		// create the final user Response
		$response = new Response();
		$response->subject = "La Rifa de Apretaste";
		$response->createFromTemplate("basic.tpl", $responseContent, array($raffle->image));
		return $response;
	}

	/**
	 * Open the Hall of Fame
	 *
	 * @param Request
	 * @return Response
	 */
	public function _ganadores (Request $request)
	{
		// set Spanish so the date come in Spanish
		setlocale(LC_TIME, "es_ES");

		// get all raffles
		$connection = new Connection();
		$raffles = $connection->query("
			SELECT start_date, winner_1, winner_2, winner_3
			FROM raffle
			WHERE winner_1 <> ''
			ORDER BY start_date DESC
			LIMIT 6");

		$images = array();
		foreach ($raffles as $raffle)
		{
			// get username
			$raffle->winner_1 = $this->utils->getPerson($raffle->winner_1);
			$raffle->winner_2 = $this->utils->getPerson($raffle->winner_2);
			$raffle->winner_3 = $this->utils->getPerson($raffle->winner_3);

			// get images
			if($raffle->winner_1->picture) $images[] = $raffle->winner_1->picture_internal;
			if($raffle->winner_2->picture) $images[] = $raffle->winner_2->picture_internal;
			if($raffle->winner_3->picture) $images[] = $raffle->winner_3->picture_internal;
		}

		// create the final user Response
		$response = new Response();
		$response->subject = "Ganadores de la Rifa";
		$response->createFromTemplate("ganadores.tpl", array("raffles"=>$raffles), $images);
		return $response;
	}

	/**
	 * Function executed when a payment is finalized
	 * Add new tickets to the database when the user pays
	 *
	 *  @author salvipascual
	 */
	public function payment(Payment $payment)
	{
		// get the number of times the loop has to iterate
		$numberTickets = null;
		if($payment->code == "1TICKET") $numberTickets = 1;
		if($payment->code == "5TICKETS") $numberTickets = 5;
		if($payment->code == "10TICKETS") $numberTickets = 10;

		// do not give tickets for wrong codes
		if(empty($numberTickets)) return false;

		// create as many tickets as necesary
		$query = "INSERT INTO ticket(email,origin) VALUES ";
		for ($i=0; $i<$numberTickets; $i++)
		{
			$query .= "('{$payment->buyer}','PURCHASE')";
			$query .= $i < $numberTickets-1 ? "," : ";";
		}

		// save the tickets in the database
		$connection = new Connection();
		$transfer = $connection->query($query);
	}
}
