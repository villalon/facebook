$('.btn-default').click(function () {
	if($(this).attr('component') == "close-modal") {		
		var modalId = $(this).attr('modalid');		
		$('#' + modalId).modal('hide');		
	}
});

$('#close').click( function () {
	alert('close');
	$('#forum-modal').modal('hide');
});