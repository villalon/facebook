<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package    local
 * @subpackage facebook
 * @copyright  2013 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
 * @copyright  2015 Xiu-Fong Lin (xlin@alumnos.uai.cl)
 * @copyright  2015 Mihail Pozarski (mipozarski@alumnos.uai.cl)
 * @copyright  2015 Hans Jeria (hansjeria@gmail.com)
 * @copyright  2016 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @copyright  2016 Andrea Villarroel (avillarroel@alumnos.uai.cl)
 * @copyright  2016 Jorge Cabané (jcabane@alumnos.uai.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
require_once ($CFG->dirroot . '/local/facebook/locallib.php');
require_once ($CFG->dirroot . "/local/facebook/app/Facebook/autoload.php");
global $DB, $USER, $CFG, $OUTPUT;
require_once ("config.php");
use Facebook\FacebookResponse;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequire;

require_once ("htmltoinclude/bootstrap.html");

// gets all facebook information needed
$appid = $CFG->fbkAppID;
$secretid = $CFG->fbkScrID;
$config = array (
		"app_id" => $appid,
		"app_secret" => $secretid,
		"default_graph_version" => "v2.5" 
);

$fb = new Facebook\Facebook ( $config );

$helper = $fb->getCanvasHelper ();

try {
	$accessToken = $helper->getAccessToken ();
} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
	// When Graph returns an error
	echo 'Graph returned an error: ' . $e->getMessage ();
	exit ();
} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage ();
	exit ();
}

if (! isset ( $accessToken )) {
	echo 'No OAuth data could be obtained from the signed request. User has not authorized your app yet.';
	exit ();
}

$facebookdata = $helper->getSignedRequest ();

$user_data = $fb->get ( "/me?fields=id", $accessToken );
$user_profile = $user_data->getGraphUser ();
$facebook_id = $user_profile ["id"];

$app_name = $CFG->fbkAppNAME;
$app_email = $CFG->fbkemail;
$tutorial_name = $CFG->fbktutorialsN;
$tutorial_link = $CFG->fbktutorialsL;
$messageurl = new moodle_url ( '/message/edit.php' );
$connecturl = new moodle_url ( '/local/facebook/connect.php' );

// gets the UAI left side bar of the app
include 'htmltoinclude/sidebar.html';

// search for the user facebook information
$userfacebookinfo = $DB->get_record ( 'facebook_user', array (
		'facebookid' => $facebook_id,
		'status' => 1 
) );

// if the user exist then show the app, if not tell him to connect to his facebook account
if ($userfacebookinfo != false) {
	$moodleid = $userfacebookinfo->moodleid;
	$lastvisit = $userfacebookinfo->lasttimechecked;
	$user_info = $DB->get_record ( 'user', array (
			'id' => $moodleid 
	) );
	$usercourse = enrol_get_users_courses ( $moodleid );
	
	// generates an array with all the users courses
	$courseidarray = array ();
	foreach ( $usercourse as $courses ) {
		$courseidarray [] = $courses->id;
	}
	
	// get_in_or_equal used after in the IN ('') clause of multiple querys
	list ( $sqlin, $param ) = $DB->get_in_or_equal ( $courseidarray );
	
	// list the 3 arrays returned from the funtion
	list ( $totalresource, $totalurl, $totalpost, $totalemarkingperstudent ) = get_total_notification ( $sqlin, $param, $lastvisit, $moodleid );
	//$dataarray = get_data_post_resource_link ( $sqlin, $param, $moodleid );
	
	// foreach that reorganizes array
	foreach ( $usercourse as $courses ) {
		$courses->totalnotifications = 0;
		
		if (isset ( $totalresource [$courses->id] )) {
			$courses->totalnotifications += intval ( $totalresource [$courses->id] );
		}
		
		if (isset ( $totalurl [$courses->id] )) {
			$courses->totalnotifications += intval ( $totalurl [$courses->id] );
		}
		
		if (isset ( $totalpost [$courses->id] )) {
			$courses->totalnotifications += intval ( $totalpost [$courses->id] );
		}
		
		if (isset ( $totalemarkingperstudent [$courses->id] )) {
			$courses->totalnotifications += intval ( $totalemarkingperstudent [$courses->id] );
		}
	}
	
	// reorganizes the courses by notifications
	usort ( $usercourse, 'cmp' );
	
	// foreach that generates each course square
	echo '<div style="line-height: 4px"><br></div>';
	foreach ( $usercourse as $courses ) {
		
		$fullname = $courses->fullname;
		$courseid = $courses->id;
		$shortname = $courses->shortname;
		$totals = $courses->totalnotifications;
		
		echo '<div class="block" style="height: 4em;"><button type="button" class="btn btn-info btn-lg" style="white-space: normal; width: 90%; height: 90%; border: 1px solid lightgray; background: linear-gradient(white, gainsboro);" courseid="' . $courseid . '" fullname="' . $fullname . '" component="button">';
		
		if ($totals > 0) {
			echo '<p class="name" style="position: relative; height: 3em; overflow: hidden; color: black; font-weight: bold; text-decoration: none; font-size:13px; word-wrap: initial;" courseid="' . $courseid . '" component="button">
 				' . $fullname . '</p><span class="badge" style="color: white; background-color: red; position: relative; right: -58%; top: -64px; margin-right:9%;" courseid="' . $courseid . '" component="button">' . $totals . '</span></button></div>';
		} else {
			echo '<p class="name" style="position: relative; height: 3em; overflow: hidden; color: black; font-weight: bold; text-decoration: none; font-size:13px; word-wrap: initial;" courseid="' . $courseid . '" component="button">
 				' . $fullname . '</p></button></div>';
		}
	}
	echo "<p></p>";
	echo "</div>";
	include 'htmltoinclude/likebutton.html';
	// include 'htmltoinclude/news.html';
	echo "</div>";
	
	echo "<div class='col-md-10 col-sm-9 col-xs-12'>";
	echo "<div class='advert'><div style='position: relative;'><img src='images/jpg_an_1.jpg'style='margin-top:10%; margin-left:8%; width:35%'><img src='images/jpg_an_2.jpg' style='margin-top:10%; margin-left:5%; width:35%'></div></div>";
	echo "<div id='loadinggif' align='center' style='margin-top: 10%; text-align: center; display:none;'><img src='https://webcursos-d.uai.cl/local/facebook/app/images/ajaxloader.gif'></div>";
	echo "<div id='table-body'></div>";
	
	echo "<div id='modal-body'></div>";

	?>
	
	<!-- Display engine -->
	<script>
	$(document).ready(function () {
	    $(document).ajaxStart(function () {
	        $("#loadinggif").show();
	    }).ajaxStop(function () {
	        $("#loadinggif").hide();
	    });
	});
	</script>

	<script type="text/javascript">
	$(document).ready(function () {
		var courseId = null;
		var discussionId = null;
		var emarkingId = null;
		var assignId = null;
		var moodleId = "<?php echo $moodleid; ?>";
	
		$("*", document.body).click(function(event) {
			event.stopPropagation();

			alert("clicked: " + event.target.nodeName);
	
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
					url : "https://webcursos-d.uai.cl/local/facebook/app/request.php?action=get_course_data&moodleid=" + moodleId + "&courseid=" + courseId,
					async : true,
					data : {},
					success : function(response) {
						$('#table-body').empty();
						$('#table-body').hide();
						$('#table-body').append('<div>' + response + '</div>');
						$('#table-body').fadeIn(300);
					}
				});
			}
	

	
			else if($(this).attr('component') == "emarking") {
				emarkingId = $(this).attr('emarkingid');
				$('#e' + emarkingId).modal('show');
	
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
	</script>
	<script>
//script for searching courses
	$("#search").on('change keyup paste', function() {
		var searchValue = $('#search').val();
		$("button").each(function() {
			var buttonId = $(this).attr('courseid');

			if($(this).attr('fullname').toLowerCase().indexOf(searchValue) == -1) {
				$(this).hide();
				$(this).parent().css('height', '0');
			} else {
				$(this).show();
				$(this).parent().css('height', '4em');
			}
		});
	});
	</script>
	<script>
	$('.modal').click(function(event) {

		var discussionId = $(this).parent().attr('discussionid');
		alert(discussionId);
		//$('#m' + discussionId).modal('show');
		
		jQuery.ajax({
			url : "https://webcursos-d.uai.cl/local/facebook/app/request.php?action=get_discussion&discussionid=" + discussionId,
			async : true,
			data : {},
			success : function (response) {
				alert("ajax bien");
				$('#modal-body').append('<div>' + response + '</div>');
			}
		});

		$('#m' + discussionId).modal('show');
	});
	</script>
	
	
	
	<?php
	echo "</div></div>";
	include 'htmltoinclude/spacer.html';
	
	// updates the user last time in the app
	$userfacebookinfo->lasttimechecked = time ();
	$DB->update_record ( 'facebook_user', $userfacebookinfo );
} else {
	echo '</div></div>';
	echo '<div class="popup" role="dialog" aria-labelledby="modal">';
	echo '<div class="cuerpo" style="margin:200px"><h1>' . get_string ( 'existtittle', 'local_facebook' ) . '</h1>
    <p>Para enlazar tu cuenta click <a  target="_blank" href="' . $connecturl . '" >Aquí</a></p></div>';
	echo '</div>';
	include 'htmltoinclude/spacer.html';
}