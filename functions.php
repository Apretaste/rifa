<?php

/**
 * Function executed when a payment is finalized
 * Add new tickets to the database
 *
 * @author salvipascual
 * @param Payment $payment
 * @return boolean
 */
function payment(Payment $payment)
{
	// get the number of times the loop has to iterate
	$numberTickets = null;
	if($payment->code == "1TICKET") $numberTickets = 1;
	if($payment->code == "5TICKETS") $numberTickets = 5;
	if($payment->code == "10TICKETS") $numberTickets = 10;

	// do not give tickets for wrong codes
	if(empty($numberTickets)) return false;

	// create as many tickets as needed
	$query = "INSERT INTO ticket (email,origin) VALUES ";
	for ($i=0; $i<$numberTickets; $i++) {
		$query .= "('{$payment->buyer->email}','PURCHASE')";
		$query .= $i < $numberTickets-1 ? "," : ";";
	}

	// save the tickets in the database
	Connection::query($query);
	return true;
}
