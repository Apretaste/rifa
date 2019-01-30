<?php

class Service
{
	/**
	 * Get the latest raffle
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _main (Request $request, Response $response)
	{
		// get the current raffle
		$raffle = Utils::getCurrentRaffle();

		// show message if there is no open raffle
		if(empty($raffle)) {
 			$response->setCache("300");
			$response->setLayout('piropazo.ejs');
			return $response->setTemplate('message.ejs', []);
		}

		// @TODO make tickets table to use ID instead of email

		// get number of tickets adquired by the user
		$userTickets = Connection::query("SELECT count(ticket_id) as tickets FROM ticket WHERE raffle_id is NULL AND email = '{$request->person->email}'");
		$userTickets = $userTickets[0]->tickets;

		// create a json object to send to the template
		$content = array(
			"description" => $raffle->item_desc,
			"startDate" => $raffle->start_date,
			"endDate" => $raffle->end_date,
			"tickets" => $raffle->tickets,
			"image" => $raffle->image,
			"userTickets" => $userTickets
		);

		// calculate minutes till the end of raffle
		$monthEnd = strtotime(date("Y-m-t 23:59:59"));
		$minsUntilMonthEnd = ceil(($monthEnd - time())/60);

		// create the final user Response
		$response->setCache($minsUntilMonthEnd);
		$response->setTemplate("basic.tpl", $content, array($raffle->image));
		return $response;
	}

	/**
	 * Open the Hall of Fame
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function _ganadores (Request $request, Response $response)
	{
		// get all raffles
		$raffles = Connection::query("
			SELECT start_date, winner_1, winner_2, winner_3
			FROM raffle
			WHERE winner_1 <> ''
			ORDER BY start_date DESC
			LIMIT 6");

		$images = array();
		foreach ($raffles as $raffle)
		{
			// get username
			$raffle->winner_1 = Utils::getPerson($raffle->winner_1);
			$raffle->winner_2 = Utils::getPerson($raffle->winner_2);
			$raffle->winner_3 = Utils::getPerson($raffle->winner_3);

			// get images
			if($raffle->winner_1->picture) $images[] = $raffle->winner_1->picture_internal;
			if($raffle->winner_2->picture) $images[] = $raffle->winner_2->picture_internal;
			if($raffle->winner_3->picture) $images[] = $raffle->winner_3->picture_internal;
		}

		// calculate minutes till the end of raffle
		$monthEnd = strtotime(date("Y-m-t 23:59:59"));
		$minsUntilMonthEnd = ceil(($monthEnd - time())/60);

		// create the final user Response
		$response->setCache($minsUntilMonthEnd);
		$response->setTemplate("ganadores.tpl", array("raffles"=>$raffles), $images);
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
		$transfer = Connection::query($query);
	}
}
