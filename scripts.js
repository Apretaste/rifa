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
