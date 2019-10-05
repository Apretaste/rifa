<?php

use Apretaste\Model\Query;

/**
 * Function executed when a payment is finalized
 * Add new tickets to the database
 *
 * @param Payment $payment
 * @return boolean
 * @author kumahacker
 */
function payment(Payment $payment)
{
	// check the code exists
	$codes = ['1TICKET' => 1, '5TICKETS' => 5, '10TICKETS' => 10];
	if(!isset($codes[$payment->code])) return false;

	// create SQL to add the tickets
	$vals = [];
	for ($i=0; $i<$codes[$payment->code]; $i++) $vals[] = "('PURCHASE','{$payment->buyer}')";
	$sql = implode(",", $vals);

	// add tickets to the database
	Connection::query("INSERT INTO ticket (origin,person_id) VALUES $sql;");
	return true;
}