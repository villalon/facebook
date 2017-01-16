$(document).ready(function () {
	var courseId = null;
	var discussionId = null;
	var emarkingId = null;

	$("*", document.body).click(function(event) {
		event.stopPropagation();

		var courseid = $(this).parent().parent().attr('courseid');
		var badgecourseid = $( "button[courseid='"+courseid+"']" ).parent().find('.badge');
		var aclick = $(this).parent().attr('style');
		

		if (($(this).attr('component') == "button") && ($(this).attr('courseid') != courseId)) {
			
			courseId = $(this).attr('courseid');
			$('.advert').remove();
			$('#table-body').empty();
			var moodleId = $(this).attr('moodleid');
			var lastVisit = $(this).attr('lastvisit');
			var url = $('#divurl').attr('url');
			
			// Ajax fix
			jQuery.ajax({
				url : url+"?action=get_course_data&moodleid=" + moodleId + "&courseid=" + courseId + "&lastvisit=" + lastVisit,
				async : true,
				data : {},
				beforeSend: function(){
					$("#loadinggif").show();
				},
				success : function(response) {
					$('#table-body').empty();
					$('#table-body').hide();
					$('#table-body').append('<div>' + response + '</div>');
					$('#table-body').fadeIn(300);
				},
				complete: function(){
					$("#loadinggif").hide();
				}
			});
		}

		else if($(this).attr('component') == "assign") {
			assignId = $(this).attr('assignid');
			$('#a' + assignId).modal('show');

			if(aclick == 'font-weight:bold'){			
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
		}
		else if($(this).attr('component') == "other") {
			
			if(aclick == 'font-weight:bold'){
				
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
		}
	});
});