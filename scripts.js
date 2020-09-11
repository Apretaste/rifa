var currentCode = false;
var share;

$(document).ready(function () {
	$('.tabs').tabs();
	$('.modal').modal();
});

// show the modal popup
function openModal(code) {
	currentCode = code;
	$('#modal').modal('open');
}

// execute the transfer
function buy() {
	apretaste.send({
		command: "RIFA PAY",
		data: {'code': currentCode},
		redirect: true
	});
}

// create a teaser text for the popup
function teaser(text) {
	return text.length <= 50 ? text : text.substr(0, 50) + "...";
}

// inits a share popup
function init(raffle) {
	share = {
		text: teaser('RIFA ' + moment(raffle.start_date).format('MMMM D, Y') + ': ' + raffle.item_desc),
		icon: 'ticket-alt',
		send: function () {
			apretaste.send({
				command: 'PIZARRA PUBLICAR',
				redirect: false,
				callback: {
					name: 'toast',
					data: 'La rifa fue compartida en Pizarra'
				},
				data: {
					text: $('#message').val(),
					image: '',
					link: {
						command: btoa(JSON.stringify({
							command: 'RIFA',
							data: {id: raffle.raffle_id}
						})),
						icon: share.icon,
						text: share.text
					}
				}
			})
		}
	};
}

function toast(message){
	M.toast({html: message});
}
