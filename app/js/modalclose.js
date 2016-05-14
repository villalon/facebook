$('button')click(function () {
	if($(this).attr('component') == "close-modal") {		
		modalId = $(this).attr('modalid');		
		$('#' + modalId).modal('hide');		
	}
});