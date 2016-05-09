$(function(){
$("#overlay,.close").click(function(){$(".popup_curso,#overlay").fadeOut();});
$(".link_curso").click(function(e){e.preventDefault();$($(this).attr("href")+",#overlay").fadeIn();});
$(document).keydown(function(e){
	var code = (e.keyCode ? e.keyCode : e.which);
	if(code == 27) {$("#overlay").click();} 
});
});