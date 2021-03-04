$(document).ready(function () {
	$('.tabs').tabs();
	$('.modal').modal();
});

// open the buy modal
function openTicketsModal() {
	$('#ticketsModal').modal('open');
	$('#tickets').focus().val('');
}

// execute the transfer
function buy() {
	// get tickets
	var tickets = Number.parseFloat($('#tickets').val());
	var credit = Number.parseFloat($('#credit').val());

	// check if valid number of tickets
	if(isNaN(tickets) || tickets <= 0 || tickets % 1 !== 0) {
		M.toast({html: 'El valor no parece ser correcto'});
		$('#tickets').focus();
		return false;
	}

	// check if you have enough credit
	if(tickets > credit) {
		M.toast({html: 'No tiene suficientes créditos'});
		$('#tickets').focus();
		return false;
	}

	// block click to avoid double send
	$('#buy').attr('onclick', '');
	$('#tickets').prop('disabled', true);

	// enviar a comprar los tickets
	apretaste.send({
		command: "RIFA COMPRAR",
		data: {'tickets': tickets},
		redirect: true
	});
}

// shorten a name to fit in the box
function short(username) {
	if (username.length > 9) return username.substring(0, 6) + '...';
	return username;
}
