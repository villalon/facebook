$('a').click(function () {
	var aclick = $(this).attr('style');

	if ($(this).attr('component') == 'forum') {
		discussionId = $(this).attr('discussionid');

		jQuery.ajax({
			url : 'https://webcursos-d.uai.cl/local/facebook/app/request.php?action=get_discussion&discussionid=' + discussionId,
			async : true,
			data : {},
			success : function (response) {
				$('#modal-body').empty();
				$('#modal-body').append(response);
				$('#modal').modal();
			}
		});
	}

	else if($(this).attr('component') == 'emarking') {
		emarkingId = $(this).attr('emarkingid');

		$('#e' + emarkingId).modal();
	}

	else if ($(this).attr('component') == 'assign') {
		assignId = $(this).attr('assignid');
		
		$('#a' + assignId).modal();
	}
	
	if(aclick == 'font-weight:bold'){
		var badgecourseid = $( "'button[courseid='"+courseid+"']'" ).parent().find('.badge');
		$(this).css('font-weight','normal');
		$(this).parent().parent().children('td').children('center').children('span').css('color','transparent');
		$(this).parent().parent().children('td').children('button').css('color','#909090');
		
		if(badgecourseid.text() == 1) {
			badgecourseid.remove();
		}
		else{
			badgecourseid.text(badgecourseid.text()-1);
		}
	}
});