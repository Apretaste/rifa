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
	$('#code').val(code);
	$('#modal').modal('open');
}

// start a new purchase
function buy() {
	var code = $('#code').val();

	// execute the transfer
	apretaste.send({
		command: "CREDITO PURCHASE", 
		data: {'item': code},
		redirect: true
	});
}