var currentCode = false;

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

var share;

function init(raffle) {
	share = {
		text: 'RIFA del ' + raffle.start_date + ': ' + raffle.item_desc.substr(0,100) + '...',
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
							command: 'RIFA VER',
							data: {
								id: raffle.raffle_id
							}
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