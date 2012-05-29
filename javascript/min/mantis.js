$(document).ready(function() {


//PESTAÑA ACTIVA

var uf = window.location.pathname;
$('.nav li a[href|="'+uf+'"]').parent().addClass('active');
	


//NAVEGACION EN TOP
// fix sub nav on scroll
var $win = $(window)
, $nav = $('.subnav')
, navTop = $('.subnav').length && $('.subnav').offset().top - 40
, isFixed = 0
processScroll()
// hack sad times - holdover until rewrite for 2.1
$nav.on('click', function () {
if (!isFixed) setTimeout(function () { $win.scrollTop($win.scrollTop() - 47) }, 10)
})
$win.on('scroll', processScroll)
function processScroll() {
var i, scrollTop = $win.scrollTop()
if (scrollTop >= navTop && !isFixed) {
isFixed = 1
$nav.addClass('subnav-fixed')
} else if (scrollTop <= navTop && isFixed) {
isFixed = 0
$nav.removeClass('subnav-fixed')
}
}

});


//SELECCIONAR PROYECTO

function projectsel(idpro){
	$('.projselect select').val(idpro);
	$('.projselect').submit();
}

