var currentCode = false;

$(document).ready(function(){
	$('.tabs').tabs();
	$('.modal').modal();
});

function formatDate(dateStr) {
	var date = new Date(dateStr);
	var year = date.getFullYear();
	var month = (1 + date.getMonth()).toString().padStart(2, '0');
	var day = date.getDate().toString().padStart(2, '0');
	return day + '/' + month + '/' + year;
}

function formatDateText(dateStr) {
	var months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
	var date = new Date(dateStr);
	return months[date.getMonth()] + ' ' + date.getFullYear();
}

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