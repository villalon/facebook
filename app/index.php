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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * 
 *
 * @package    local
 * @subpackage facebook
 * @copyright  2013 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
 * @copyright  2015 Xiu-Fong Lin (xlin@alumnos.uai.cl)
 * @copyright  2015 Mihail Pozarski (mipozarski@alumnos.uai.cl)
 * @copyright  2015 Hans Jeria (hansjeria@gmail.com)
 * @copyright  2016 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @copyright  2016 Andrea Villarroel (avillarroel@alumnos.uai.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/facebook/locallib.php');
require_once ($CFG->dirroot."/local/facebook/app/Facebook/autoload.php");
global $DB, $USER, $CFG, $OUTPUT;
include "config.php";
use Facebook\FacebookResponse;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequire;
include "htmltoinclude/bootstrap.html";

?>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '633751800045647',
      xfbml      : true,
      version    : 'v2.5'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
<?php

//gets all facebook information needed
$appid = $CFG->fbkAppID;
$secretid = $CFG->fbkScrID;
$config = array(
		"app_id" => $appid,
		"app_secret" => $secretid,
		"default_graph_version" => "v2.5"
);
$fb = new Facebook\Facebook($config);

$helper = $fb->getCanvasHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (! isset($accessToken)) {
  echo 'No OAuth data could be obtained from the signed request. User has not authorized your app yet.';
  exit;
}

$facebookdata = $helper->getSignedRequest();

$user_data = $fb->get("/me?fields=id",$accessToken);
$user_profile = $user_data->getGraphUser();
$facebook_id = $user_profile["id"];

$app_name= $CFG->fbkAppNAME;
$app_email= $CFG->fbkemail;
$tutorial_name=$CFG->fbktutorialsN;
$tutorial_link=$CFG->fbktutorialsL;
$messageurl= new moodle_url('/message/edit.php');
$connecturl= new moodle_url('/local/facebook/connect.php');

//gets the UAI left side bar of the app
include 'htmltoinclude/sidebar.html';

//search for the user facebook information
$userfacebookinfo = $DB->get_record('facebook_user',array('facebookid'=>$facebook_id,'status'=>1));

// if the user exist then show the app, if not tell him to connect his facebook account
if ($userfacebookinfo != false) {
	$moodleid = $userfacebookinfo->moodleid;
	$lastvisit = $userfacebookinfo->lasttimechecked;
	$user_info = $DB->get_record('user', array(
			'id'=>$moodleid
	));
	$usercourse = enrol_get_users_courses($moodleid);

	//generates an array with all the users courses
	$courseidarray = array();
	foreach ($usercourse as $courses){
		$courseidarray[] = $courses->id;
	}

	// get_in_or_equal used after in the IN ('') clause of multiple querys
	list($sqlin, $param) = $DB->get_in_or_equal($courseidarray);

	// list the 3 arrays returned from the funtion
	list($totalresource, $totalurl, $totalpost) = get_total_notification($sqlin, $param, $lastvisit);
	$dataarray = get_data_post_resource_link($sqlin, $param);

	//foreach that generates each course square
	echo '<div style="line-height: 4px"><br></div>';
	foreach($usercourse as $courses){
			
		$fullname = $courses->fullname;
		$courseid = $courses->id;
		$shortname = $courses->shortname;
		$totals = 0;
		// tests if the array has something in it
		if (isset($totalresource[$courseid])){
			$totals += intval($totalresource[$courseid]);
		}
		// tests if the array has something in it
		if (isset($totalurl[$courseid])){
			$totals += intval($totalurl[$courseid]);
		}
		// tests if the array has something in it
		if (isset($totalpost[$courseid])){
			$totals += intval($totalpost[$courseid]);
		}
		/*echo '<div class="panel panel-default" style="padding-left: 5px; background: linear-gradient(white, gainsboro);">
				<a class="inline link_curso" href="#'.$courseid.'">
				<p class="name" style="color: black; font-weight:bold; text-decoration: none; font-size:15px;">
				<img src="images/lista_curso.png">'.$fullname.'</p></a></div>';*/
		echo '<div class="block" style="height: 4em;"><button type="button" class="btn btn-info btn-lg" style="white-space: normal; width: 90%; height: 90%; border: 1px solid lightgray; background: linear-gradient(white, gainsboro);" courseid="'.$courseid.'" fullname="'.$fullname.'" component="button">';
		
		// If there is something to notify, show the number of new things
		
		echo '<p class="name" style="position: relative; height: 3em; overflow: hidden; color: black; font-weight: bold; text-decoration: none; font-size:13px; word-wrap: initial;" courseid="'.$courseid.'" component="button">
				'.$fullname.'</p>';
		if ($totals>0){
			echo '<span class="badge" style="color: white; background-color: red; position: relative; right: -58%; top: -64px; margin-right:9%;" courseid="'.$courseid.'" component="button">'.$totals.'</span>';
		}
		echo '</button></div>';
		
		
		
		//include "htmltoinclude/tableheaderindex.html";
	}
	echo "<p></p>";
	echo "</div>";
	include 'htmltoinclude/likebutton.html';
	//include 'htmltoinclude/news.html';
	echo "</div>";
	
	
	echo "<div class='col-md-10 col-sm-9 col-xs-12'>";
	foreach($usercourse as $courses){
			
		$fullname = $courses->fullname;
		$courseid = $courses->id;
		
		?>
      	<div style="display: none;" id="c<?php echo $courseid; ?>">
      		
      		<div class="panel panel-default">
      		
			  	<div class="panel"><nav>
				  <ul><p class="small;"></p><p><b style="font-size: 120%; color: #727272;"><?php echo $fullname; ?></b></p>
				  </ul>
				  <ul class="pagination pagination-sm">
    				<li>
      					<a href="#" aria-label="Previous">
        				<span aria-hidden="true">&laquo;</span>
      					</a>
    				</li>
    				<li><a href="#">1</a></li>
    				<li><a href="#">2</a></li>
    				<li><a href="#">3</a></li>
    				<li><a href="#">4</a></li>
    				<li><a href="#">5</a></li>
    				<li>
      					<a href="#" aria-label="Next">
        				<span aria-hidden="true">&raquo;</span>
      					</a>
    				</li>
  				</ul>
				</nav>
  				</div>
  				
			<table class="tablesorter" border="0" width="100%" style="font-size: 13px">
				<thead>
					<tr>
						<th width="8%" style= "border: 0px  #212121;border-radius: 8px 0px 0px 0px"></th>
						<th width="37%"><?php echo get_string('rowtittle', 'local_facebook'); ?></th>
						<th width="20%"><?php echo get_string('rowdate', 'local_facebook'); ?></th>						
						<th width="20%"><?php echo get_string('rowfrom', 'local_facebook'); ?></th>
						<th width="8%" 	style= "border: 0px  #212121;border-radius: 0px 8px 0px 0px">Share</th>						
					</tr>
				</thead>
				<tbody>
			<?php 
			//foreach that gives the corresponding image to the new and old items created(resource,post,forum), and its title, how upload it and its link
			foreach($dataarray as $data){
				$discussionId = null;
				$markid = null;
				$assignid = null;
				if($data['course'] == $courseid){
					$date = date("d/m/Y H:i", $data['date']);
					
					echo '<tr><td ';
					if ($data['date'] > $lastvisit) {
						echo 'style="margin-left:10px; border-left:3px solid #2a2a2a;"';
					}
					echo '><center>';
					
					if($data['image'] == FACEBOOK_IMAGE_POST){
						echo '<img src="images/post.png">';
						$discussionId = $data['discussion'];
					}
					elseif($data['image'] == FACEBOOK_IMAGE_RESOURCE){
						echo '<img src="images/resource.png">';
					}					
					elseif($data['image'] == FACEBOOK_IMAGE_LINK){
						echo '<img src="images/link.png">';
					}
					elseif($data['image'] == FACEBOOK_IMAGE_EMARKING) {
						echo '<img src="images/emarking.png">';
						$markid = $data['id'];
					}
					elseif($data['image'] == FACEBOOK_IMAGE_ASSIGN) {
						echo '<img src="images/assign.png">';
						$assignid = $data['id'];
					}

					
					if($discussionId != null) {
						echo '</center></td><td>';
						if($data['date'] > $lastvisit) {
							echo '<b><a href="#" discussionid="'.$discussionId.'" component="forum">'.$data['title'].'</a>
								</td><td><b>'.$date.'</td><td style="font-size:13px"><b>'.$data ['from'].'</td></b>
								<td><button type="button" class="btn btn-primary btn-sm" style="color:#E5E3FB">
								<img src="images/facebook_1.png" style="height: 70%; width: auto;"></img><b>| share
								</b></button></td></tr>';
						} else {
 							echo '<a href="#" discussionid="'.$discussionId.'" component="forum">'.$data['title'].'</a>
								</td><td>'.$date.'</td><td style="font-size: 13px"><b>'.$data ['from'].'</b></td>
								<td><button type="button" class="btn btn-default btn-sm" style="color:#909090">
								<img src="images/facebook_2.png" style="height: 70%; width: auto;"></img><b>| share
								</b></button></td></tr>';
						}
						
						$postData = get_posts_from_discussion($discussionId);
						?>
						<!-- Modal -->
						<div class="modal fade" id="m<?php echo $discussionId; ?>" tabindex="-1" role="dialog" aria-labelledby="modal">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						      <div class="modal-body">
						      <?php
						        foreach($postData as $post) {
						        	$date = $post['date'];
						        	echo "<div align='left'>".$post['message']."</div>";
						        	echo "<div align='right'>".$post['user'].", ".date('l d-F-Y', $date)."</div><br>";
						        }
						      ?>
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal" component="close-modal" modalid="m<?php echo $discussionId; ?>">Close</button>
						      </div>
						    </div>
						  </div>
						</div>
						<?php
					} elseif ($markid != null) {
						echo '</center></td><td><a href="#" emarkingid="'.$markid.'" component="emarking">'.$data['title'].'</a>
									</td><td>'.$date.'</td><td style="font-size:11px"><b>'.$data ['from'].'</b></td></tr>';
						?>
						<!-- Modal -->
						<div class="modal fade" id="e<?php echo $markid; ?>" tabindex="-1" role="dialog" aria-labelledby="modal">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
							    <div class="modal-header">
							    	<h4 class="modal-title"><?php $course = $DB->get_record('course', array('id' => $data['course'])); echo $course->fullname; ?></h4>
							    	<?php echo $data['title']; ?>
							    </div>
						  		<div class="modal-body">
						  			<div class="row">
						  				<div class="col-md-4">
						  					<b><?php echo get_string('name', 'local_facebook'); ?></b>
						  					<br>
						  					<?php echo $data['from']; ?>
						  				</div>
						  				<div class="col-md-2">
						  					<b><?php echo get_string('grade', 'local_facebook'); ?></b>
						  					<br>
						  					<?php
						  					if($data['status'] >= EMARKING_STATUS_PUBLISHED) {
						  						echo $data['grade'];
						  					} else {
						  						echo "-";
						  					}
						  					?>
						  				</div>
						  				<div class="col-md-3">
						  					<b><?php echo get_string('status', 'local_facebook'); ?></b>
						  					<br>
						  					<?php
						  					if($data['status'] >= EMARKING_STATUS_PUBLISHED) {
						  						echo get_string('published', 'local_facebook');
						  					} else if($data['status'] >= EMARKING_STATUS_SUBMITTED) {
						  						echo get_string('submitted', 'local_facebook');
						  					} else {
						  						echo get_string('absent', 'local_facebook');
						  					}
						  					?>
						  				</div>
						  				<div class="col-md-3">
						  					<br>
						  					<?php 
						  						echo '<a href="'.$data['link'].'" target="_blank">'.get_string('viewexam', 'local_facebook').'</a>';
						  					?>
						  				</div>
						  			</div>
						      	</div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal" component="close-modal" modalid="e<?php echo $markid; ?>">Close</button>
						      </div>
						    </div>
						  </div>
						</div>
						<?php
					} elseif($assignid != null) {
						echo '</center></td><td><a href="#" assignid="'.$assignid.'" component="assign">'.$data['title'].'</a>
									</td><td>'.$date.'</td></tr>';
						
						?>
						<!-- Modal -->
						<div class="modal fade" id="a<?php echo $assignid; ?>" tabindex="-1" role="dialog" aria-labelledby="modal">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						    	<div class="modal-header">
							    	<h4 class="modal-title"><?php echo $data['title']; ?></h4>
							    	<?php echo $data['intro']; ?>
							    </div>
						      <div class="modal-body">
						      	<div class="row">
						      		<div class="col-md-6">
						      			<b><?php echo get_string('submitstatus', 'local_facebook'); ?></b>
						      			<br>
						      			<b><?php echo get_string('gradestatus', 'local_facebook'); ?></b>
						      			<br>
						      			<b><?php echo get_string('duedate', 'local_facebook'); ?></b>
						      			<br>
						      			<b><?php echo get_string('timeleft', 'local_facebook'); ?></b>
						      			<br>
						      			<b><?php echo get_string('lastmodified', 'local_facebook'); ?></b>
						      		</div>
						      		<div class="col-md-6">
						      			<?php
				      					if($data['status'] == 'view') {
				      						echo "No entregado<br>";
				      					} else {
				      						echo "Enviado para calificar<br>";
				      					}
				      					
				      					if($data['grade'] != null) {
				      						echo "Calificado<br>";
				      					} else {
				      						echo "Sin calificar<br>";
				      					}
				      					
				      					$duedate = date('r', $data['due']);
				      					echo $duedate."<br>";
				      					
				      					$interval = $data['due'] - time();
				      					if($interval > 0) {
				      						$timeleft = ceil($interval / (60 * 60 * 24));
				      						echo $timeleft." días<br>";
				      					} else {
				      						echo "Se ha acabado el tiempo<br>";
				      					}
				      					
				      					$lastmodified = date('r', $data['date']);
				      					echo $lastmodified;
						      			?>
						      			</div>
						      		</div>
						      	</div>
						      <div class="modal-footer">
						      	<a href="<?php echo $data['link']; ?>"
						      		<button type="button" class="btn btn-default">Ver en moodle</button>
						      	</a>
						        <button type="button" class="btn btn-default" data-dismiss="modal" component="close-modal" modalid="a<?php echo $assignid; ?>">Close</button>
						      </div>
						    </div>
						  </div>
						</div>
						<?php
					} else {
						echo '</center></td><td><a href="'.$data['link'].'" target="_blank">'.$data['title'].'</a>
									</td><td>'.$date.'</td><td style="font-size:11px"><b>'.$data ['from'].'</b></td></tr>';
					}
				}
			}
		echo "</tbody></table></div></div>";
	}
	
	?>
	
	<!-- Display engine -->
	<script type="text/javascript">
	var courseId = null;
	var discussionId = null;
	var emarkingId = null;
	var assignId = null;

	$("*", document.body).click(function(event) {
		event.stopPropagation();

		if(($(this).attr('component') == "button") && ($(this).attr('courseid') != courseId)) {
			$('#c' + courseId).fadeOut(300);
			courseId = $(this).attr('courseid');
			$('#c' + courseId).delay(300).fadeIn(300);
		}

		else if($(this).attr('component') == "forum") {
			discussionId = $(this).attr('discussionid');
			$('#m' + discussionId).modal('show');
		}

		else if($(this).attr('component') == "close-modal") {
			modalId = $(this).attr('modalid');
			$('#' + modalId).modal('hide');
		}

		else if($(this).attr('component') == "emarking") {
			emarkingId = $(this).attr('emarkingid');
			$('#e' + emarkingId).modal('show');
		}

		else if($(this).attr('component') == "assign") {
			assignId = $(this).attr('assignid');
			$('#a' + assignId).modal('show');
		}
	});

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
	
	<?php
 	echo "</div></div><br><br><br><br><br><br><br><br><br><br><br><br><br>";
	include 'htmltoinclude/spacer.html';
	echo '<div id="overlay"></div>';

	//updates the user last time in the app
	$userfacebookinfo->lasttimechecked = time();
	$DB->update_record('facebook_user', $userfacebookinfo);

} else{
	echo '<div class="cuerpo"><h1>'.get_string('existtittle', 'local_facebook').'</h1>
		     <p>'.get_string('existtext', 'local_facebook').'<a  target="_blank" href="'.$connecturl.'" >'.get_string('existlink', 'local_facebook').'</a></p></div>';
	include 'htmltoinclude/spacer.html';
}