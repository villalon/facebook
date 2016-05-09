$(document).ready(function () {
		var courseId = null;
		var discussionId = null;
		var emarkingId = null;
		var assignId = null;
		var moodleId = "<?php echo $moodleid; ?>";
		var lastVisit = "<?php echo $lastvisit; ?>";
	
		$("*", document.body).click(function(event) {
			event.stopPropagation();
	
			var courseid = $(this).parent().parent().attr('courseid');
			var badgecourseid = $( "button[courseid='"+courseid+"']" ).parent().find('.badge');
			var aclick = $(this).parent().attr('style');
			var advert = $(this).parent().parent().parent().parent().parent().find('.advert');
			
	
			if (($(this).attr('component') == "button") && ($(this).attr('courseid') != courseId)) {
				
				courseId = $(this).attr('courseid');
				advert.remove();
				$('#table-body').empty();
	
				// Ajax fix
				jQuery.ajax({
					url : "https://webcursos-d.uai.cl/local/facebook/app/request.php?action=get_course_data&moodleid=" + moodleId + "&courseid=" + courseId + "&lastvisit=" + lastVisit,
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
 				 			
 			else if($(this).attr('component') == "close-modal") {		
 				modalId = $(this).attr('modalid');		
 				$('#' + modalId).modal('hide');		
 			}
	
			else if($(this).attr('component') == "assign") {
				assignId = $(this).attr('assignid');
				$('#a' + assignId).modal('show');
	
				if(aclick == 'font-weight:bold'){			
					 $(this).parent().parent().children("td").css('font-weight','normal');
	//				 $(this).parent().parent().children("td").children("button").removeClass("btn btn-primary");
	//				 $(this).parent().parent().children("td").children("button").addClass("btn btn-default");
					 $(this).parent().parent().children("td").children("center").children("span").css('color','transparent');
					 $(this).parent().parent().children("td").children("button").css('color','#909090');
					 				
					 if(badgecourseid.text() == 1) { 
					 	badgecourseid.remove(); 
					 }
					 else{ 
					 	badgecourseid.text(badgecourseid.text()-1); 
					 }
				}
			}
			else if($(this).attr('component') == "other") {
				
				if(aclick == 'font-weight:bold'){
					
					$(this).parent().parent().children("td").css('font-weight','normal');
	//				$(this).parent().parent().children("td").children("button").removeClass("btn btn-primary");
	//				$(this).parent().parent().children("td").children("button").addClass("btn btn-default");
					$(this).parent().parent().children("td").children("center").children("span").css('color','transparent');
					$(this).parent().parent().children("td").children("button").css('color','#909090');
					
					if(badgecourseid.text() == 1) { 
						badgecourseid.remove(); 
					}
					else{ 
						badgecourseid.text(badgecourseid.text()-1); 
					}
				}
			}
		});
	});