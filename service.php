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
			return $response->setTemplate('message.ejs');
		}
		$raffle = $raffle[0];

		// get the image of the raffle
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
}