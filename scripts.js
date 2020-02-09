var currentCode = false;

var colors = {
	'azul': '#99F9FF',
	'verde': '#9ADB05',
	'rojo': '#FF415B',
	'morado': '#58235E',
	'naranja': '#F38200',
	'amarillo': '#FFE600'
};

var avatars = {
	apretin: {caption: "Apretín", gender: 'M'},
	apretina: {caption: "Apretina", gender: 'F'},
	artista: {caption: "Artista", gender: 'M'},
	bandido: {caption: "Bandido", gender: 'M'},
	belleza: {caption: "Belleza", gender: 'F'},
	chica: {caption: "Chica", gender: 'F'},
	coqueta: {caption: "Coqueta", gender: 'F'},
	cresta: {caption: "Cresta", gender: 'M'},
	deportiva: {caption: "Deportiva", gender: 'F'},
	dulce: {caption: "Dulce", gender: 'F'},
	emo: {caption: "Emo", gender: 'M'},
	oculto: {caption: "Oculto", gender: 'M'},
	extranna: {caption: "Extraña", gender: 'F'},
	fabulosa: {caption: "Fabulosa", gender: 'F'},
	fuerte: {caption: "Fuerte", gender: 'M'},
	ganadero: {caption: "Ganadero", gender: 'M'},
	geek: {caption: "Geek", gender: 'F'},
	genia: {caption: "Genia", gender: 'F'},
	gotica: {caption: "Gótica", gender: 'F'},
	gotico: {caption: "Gótico", gender: 'M'},
	guapo: {caption: "Guapo", gender: 'M'},
	hawaiano: {caption: "Hawaiano", gender: 'M'},
	hippie: {caption: "Hippie", gender: 'M'},
	hombre: {caption: "Hombre", gender: 'M'},
	atento: {caption: "Atento", gender: 'M'},
	libre: {caption: "Libre", gender: 'F'},
	jefe: {caption: "Jefe", gender: 'M'},
	jugadora: {caption: "Jugadora", gender: 'F'},
	mago: {caption: "Mago", gender: 'M'},
	metalero: {caption: "Metalero", gender: 'M'},
	modelo: {caption: "Modelo", gender: 'F'},
	moderna: {caption: "Moderna", gender: 'F'},
	musico: {caption: "Músico", gender: 'M'},
	nerd: {caption: "Nerd", gender: 'M'},
	punk: {caption: "Punk", gender: 'M'},
	punkie: {caption: "Punkie", gender: 'M'},
	rap: {caption: "Rap", gender: 'M'},
	rapear: {caption: "Rapear", gender: 'M'},
	rapero: {caption: "Rapero", gender: 'M'},
	rock: {caption: "Rock", gender: 'M'},
	rockera: {caption: "Rockera", gender: 'F'},
	rubia: {caption: "Rubia", gender: 'F'},
	rudo: {caption: "Rudo", gender: 'M'},
	sencilla: {caption: "Sencilla", gender: 'F'},
	sencillo: {caption: "Sencillo", gender: 'M'},
	sennor: {caption: "Señor", gender: 'M'},
	sennorita: {caption: "Señorita", gender: 'F'},
	sensei: {caption: "Sensei", gender: 'M'},
	surfista: {caption: "Surfista", gender: 'M'},
	tablista: {caption: "Tablista", gender: 'F'},
	vaquera: {caption: "Vaquera", gender: 'F'}
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

function getAvatar(avatar, serviceImgPath) {
	return "background-image: url(" + serviceImgPath + "/" + avatar + ".png);";
}
