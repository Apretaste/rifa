<?php

use Apretaste\Model\Query;

/**
 * Function executed when a payment is finalized
 * Add new tickets to the database
 *
 * @param Payment $payment
 *
 * @return boolean
 * @author kumahacker
 */
function payment(Payment $payment)
{
    $available = [
        '1TICKET' => 1,
        '5TICKETS' => 5,
        '10TICKETS' => 10
    ];

    if (isset($available[(string) $payment->code])) {
        $numberTickets = $available[$payment->code];
        for ($i=0; $i<$numberTickets; $i++) {
            q(Query::simpleInsert('ticket', [
                'email'     => $payment->buyer->email,
                'person_id' => $payment->buyer->id,
                'origin'    => 'PURCHASE' // TODO: check this, did you mean 'RAFFLE'
            ]));
        }

        return true;
    }

    return false;

}
