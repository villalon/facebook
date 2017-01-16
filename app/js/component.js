$('a').click(function () {
	var aclick = $(this).attr('style');

	if ($(this).attr('component') == 'forum') {
		discussionId = $(this).attr('discussionid');
		moduleId = $(this).attr('moduleid');
		var url = $('#divurl').attr('url');
		jQuery.ajax({
			url : url+'?action=get_discussion&discussionid=' + discussionId + '&moduleid=' + moduleId,
			async : true,
			data : {},
			success : function (response) {
				$('#modal-content').empty();
				$('#modal-content').append(response);
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
		alert("me hiciste click y soy feo");
		var coursename = $('#coursename');
		var badgecourse = $("p:contains('"+coursename+"')").parent().find('.badge');
		$(this).css('font-weight','normal');
		$(this).parent().parent().children('td').children('center').children('span').css('color','transparent');
		$(this).parent().parent().children('td').children('button').css('color','#909090');
		
		if(badgecourse.text() == 1) {
			badgecourse.remove();
		}
		else{
			badgecourse.text(badgecourse.text()-1);
		}
	}
});