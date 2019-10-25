var currentCode = false;

var colors = {
	'Azul': '#99F9FF',
	'Verde': '#9ADB05',
	'Rojo': '#FF415B',
	'Morado': '#58235E',
	'Naranja': '#F38200',
	'Amarillo': '#FFE600'
};

var avatars = {
	'Rockera': 'F',
	'Tablista': 'F',
	'Rapero': 'M',
	'Guapo': 'M',
	'Bandido': 'M',
	'Encapuchado': 'M',
	'Rapear': 'M',
	'Inconformista': 'M',
	'Coqueta': 'F',
	'Punk': 'M',
	'Metalero': 'M',
	'Rudo': 'M',
	'Señor': 'M',
	'Nerd': 'M',
	'Hombre': 'M',
	'Cresta': 'M',
	'Emo': 'M',
	'Fabulosa': 'F',
	'Mago': 'M',
	'Jefe': 'M',
	'Sensei': 'M',
	'Rubia': 'F',
	'Dulce': 'F',
	'Belleza': 'F',
	'Músico': 'M',
	'Rap': 'M',
	'Artista': 'M',
	'Fuerte': 'M',
	'Punkie': 'M',
	'Vaquera': 'F',
	'Modelo': 'F',
	'Independiente': 'F',
	'Extraña': 'F',
	'Hippie': 'M',
	'Chica Emo': 'F',
	'Jugadora': 'F',
	'Sencilla': 'F',
	'Geek': 'F',
	'Deportiva': 'F',
	'Moderna': 'F',
	'Surfista': 'M',
	'Señorita': 'F',
	'Rock': 'F',
	'Genia': 'F',
	'Gótica': 'F',
	'Sencillo': 'M',
	'Hawaiano': 'M',
	'Ganadero': 'M',
	'Gótico': 'M'
};

$(document).ready(function () {
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
	var months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
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

function getAvatar(avatar, serviceImgPath, size) {
	var index = Object.keys(avatars).indexOf(avatar);
	var fullsize = size * 7;
	var x = index % 7 * size;
	var y = Math.floor(index / 7) * size;
	return "background-image: url(" + serviceImgPath + "/avatars.png);" + "background-size: " + fullsize + "px " + fullsize + "px;" + "background-position: -" + x + "px -" + y + "px;";
}
