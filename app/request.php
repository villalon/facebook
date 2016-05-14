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
 * @package    local
 * @subpackage facebook
 * @copyright  2016 Jorge Cabané (jcabane@alumnos.uai.cl)
 * @copyright  2016 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//define("AJAX_SCRIPT", true);
//define("NO_DEBUG_DISPLAY", true);

require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');
require_once ($CFG->dirroot . '/local/facebook/locallib.php');
require_once $CFG->libdir . '/accesslib.php';
global $DB;

$action       = required_param ('action', PARAM_ALPHAEXT);
$moodleid     = optional_param ('moodleid', null , PARAM_RAW_TRIMMED);
$courseid     = optional_param ('courseid', null , PARAM_RAW_TRIMMED);
$discussionid = optional_param ('discussionid', null, PARAM_RAW_TRIMMED);
$emarkingid   = optional_param ('emarkingid', null, PARAM_RAW_TRIMMED);
$lastvisit    = optional_param ('lastvisit', null , PARAM_RAW_TRIMMED);
$moduleid     = optional_param ('moduleid', null, PARAM_RAW_TRIMMED);

if ($action == 'get_course_data') {

	$totaldata = get_course_data($moodleid, $courseid);
	$course = $DB->get_record('course', array('id' => $courseid));
	
	$htmltable = "";
	
	$htmltable .= '<div align="left"><h2 id="coursename">'.$course->fullname.'</h2></div>';
	
	if (empty($totaldata)) {
		$htmltable .= '<tr><div class="col-md-10 col-md-offset-1"><div class="alert alert-info" role="alert">No hay recursos dentro de este curso</div></div><tr>';
	}
	
	else {
		$htmltable .= '<table class="tablesorter" border="0" width="100%" style="font-size: 13px; margin-left: 9px;">
						<thead>
							<tr>
								<th width="1%" style="border-top-left-radius: 8px;"></th>
								<th width="4%"></th>
								<th width="32%">Título</th>
								<th width="30%">De</th>
								<th width="30%" style="border-top-right-radius: 8px;">Fecha</th>
								<th width="3%" style="background-color: transparent;"></th>
							</tr>
						</thead>
						<tbody>';
	
		foreach ($totaldata as $module) {
			$date = date ( "d/m/Y H:i", $module ['date'] );
			$component = '';
			$link = '';
			$id = 0;
			$new = 0;
			
			$htmltable .= "<tr><td>";
			
			if ($module['date'] >= $lastvisit) {
				$htmltable .= "<center><span class='glyphicon glyphicon-option-vertical' aria-hidden='true' style='color: #2a2a2a;'></span></center>&nbsp&nbsp";
				$new = 1;			
			}
			
			$htmltable .= "</td><td>";
			
			if ($module ['image'] == FACEBOOK_IMAGE_POST) {
				$htmltable .= '<img src="images/post.png">';
				$component = 'forum';
				$link = "href='#'";
				$id = "discussionid='".$module ['discussion']."' moduleid='".$module['moduleid']."'";
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_RESOURCE) {
				$htmltable .= '<img src="images/resource.png">';
				$link = "href='".$module['link']."' target='_blank'";
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_LINK) {
				$htmltable .= '<img src="images/link.png">';
				$link = "href='".$module['link']."' target='_blank'";
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_EMARKING) {
				$htmltable .= '<img src="images/emarking.png">';
				$component = 'emarking';
				$link = "href='#'";
				$id = "emarkingid='".$module['id']."'";
				
				$emarkingmodal = "<div class='modal fade' id='e".$module['id']."' tabindex='-1' role='dialog' aria-labelledby='modal'>
									<div class='modal-dialog' role='document'>
										<div class='modal-content'>
											<div class='modal-title' align='center'><h4>".$module['title']."</h4></div>
											<div class='modal-body' id='emarking-modal-body'>
												<div class='row'>
													<div class='col-md-4'>
								  						<b>".get_string('name', 'local_facebook')."</b>
									  					<br>".$module['from']."
									  				</div>
									  				<div class='col-md-3'>
									  					<b>".get_string('grade', 'local_facebook')."</b>
									  					<br>";
				
				if($module['status'] >= 20) {
					$emarkingmodal .= $module['grade'];
				} else {
					$emarkingmodal .= "-";
				}
				
				$emarkingmodal .= "</div>
				  				<div class='col-md-3'>
				  					<b>".get_string('status', 'local_facebook')."</b>
				  					<br>";
					
				if($module['status'] >= 20) {
					$emarkingmodal .= get_string('published', 'local_facebook');
				} else if($module['status'] >= 10) {
					$emarkingmodal .= get_string('submitted', 'local_facebook');
				} else {
					$emarkingmodal .= get_string('absent', 'local_facebook');
				}
				
				$emarkingmodal .= "</div>
				  				<div class='col-md-2'>
				  					<br>
				  					<a href='".$module['link']."' target='_blank'>".get_string('viewexam', 'local_facebook')."</a>
				  				</div>
				  			</div>
	  					</div>
						<div class='modal-footer'>
							<button type='button' class='btn btn-default' data-dismiss='modal' component='close-modal'>".get_string('close', 'local_facebook')."</button>
						</div>
					</div>
				</div>
			</div>";
				
				$htmltable .= $emarkingmodal;
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_ASSIGN) {
				$htmltable .= '<img src="images/assign.png">';
				$id = "assignid='".$module ['id']."'";
				$component = 'assign';
				$link = "href='#'";
				
				$assignmodal = "<div class='modal fade' id='a".$module['id']."' tabindex='-1' role='dialog' aria-labelledby='modal'>
									<div class='modal-dialog' role='document'>
										<div class='modal-content'>
											<div class='modal-title' align='center'><h4>".$module['title']."</h4></div>
											<div class='modal-body' id='emarking-modal-body'>
												<div class='row'>
													<div class='col-md-5 col-md-offset-1'>
														<b>".get_string('submitstatus', 'local_facebook')."</b>
															<br>
															<br>
														<b>".get_string('gradestatus', 'local_facebook')."</b>
															<br>
															<br>
														<b>".get_string('duedate', 'local_facebook')."</b>
															<br>
															<br>
														<b>".get_string('lastmodified', 'local_facebook')."</b>
													</div>
													<div class='col-md-5'>
														".$module['status']."
															<br>
															<br>
														".$module['grade']."
															<br>
															<br>
														".$module['due']."
															<br>
															<br>
														".$module['modified']."
													</div>
												</div>
											</div>
											<div class='modal-footer'>
												<a class='btn btn-primary' href='".$module['link']."' role='button' target='_blank'>".get_string('viewassign', 'local_facebook')."</a>
												<button type='button' class='btn btn-default' data-dismiss='modal' component='close-modal'>".get_string('close', 'local_facebook')."</button>
											</div>
										</div>
									</div>
								</div>";
				
				$htmltable .= $assignmodal;
			}

			if ($new == 1) {
				$htmltable .= "</td><td><a style='font-weight:bold;' $link component=$component $id>".$module['title']."</a></td>
						<td>". $module['from'] ."</td><td>". $date ."</td></tr>";
			}
			else{
				$htmltable .= "</td><td><a $link component=$component $id>".$module['title']."</a></td>
						<td>". $module['from'] ."</td><td>". $date ."</td></tr>";
			}
			
		}
	}
	$htmltable .= "</tbody></table>";
	
	$jsfunction = "<script type='text/javascript' src='js/component.js'></script>";
	
	$htmltable .= $jsfunction;
	
	echo $htmltable;
} 

else if ($action == 'get_discussion') {
	
	$discussionposts = get_posts_from_discussion($discussionid);
	$htmlmodal = "<div class='modal-body' id='modal-body'>";
	
	$moodlelink = new moodle_url('/mod/forum/view.php', array (
			'id' => $moduleid
	));
	
	foreach ($discussionposts as $post) {
		$date = $post['date'];
		$htmlmodal .= "<div align='left' style='background-color: #E6E6E6; border-radius: 4px 4px 0 0; padding: 4px; color: #333333;'>
						<img src='images/post.png'>
							<b>&nbsp&nbsp".$post['subject']."<br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp".$post['user'].", ".date('l d-F-Y', $date)."</b>
					    </div>
					<div align='left' style='border-radius: 0 0 4px 4px; word-wrap: break-word;'>".$post['message']."</div><br>";
	}
	
	$htmlmodal .= "</div>
		   		<div class='modal-footer'>
				   	<a class='btn btn-primary' href='".$moodlelink."' role='button' target='_blank'>".get_string('viewforum', 'local_facebook')."</a>
					<button type='button' class='btn btn-default' data-dismiss='modal' component='close-modal' modalid='modal'>".get_string('close', 'local_facebook')."</button>
				</div>";
		
	echo $htmlmodal;
} 

else if ($action == 'get_emarking') {
	
	$emarkingsql = "SELECT s.id AS id,
			s.grade AS grade,
			s.status AS status,
			cm.id as moduleid,
			CONCAT(u.firstname,' ',u.lastname) AS user
			FROM {emarking_submission} AS s
			INNER JOIN {user} AS u ON (u.id = s.student AND u.id = ?)
			INNER JOIN {course_modules} AS cm ON (cm.instance = s.emarking AND s.emarking = ?)";
	
	$paramsemarking = array(
			$moodleid,
			$emarkingid
	);
	
	$emarkingdata = $DB->get_records_sql($emarkingsql, $paramsemarking);
	$htmlmodal = '';
	
	foreach ($emarkingdata as $emarking) {
		$emarkingurl = new moodle_url('/mod/emarking/view.php', array(
				'id' => $emarking->moduleid
		));
		
		$htmlmodal .= "<div class='row'>
						<div class='col-md-4'>
	  						<b>".get_string('name', 'local_facebook')."</b>
		  					<br>".$emarking->user."
		  				</div>
		  				<div class='col-md-2'>
		  					<b>".get_string('grade', 'local_facebook')."</b>
		  					<br>";
		
	  	if($emarking->status >= 20) {
	  		$htmlmodal .= $emarking->grade;
	 	} else {
	 		$htmlmodal .= "-";
	  	}
		
	  	$htmlmodal .= "</div>
		  				<div class='col-md-3'>
		  					<b>".get_string('status', 'local_facebook')."</b>
		  					<br>";
		  					
	  	if($emarking->status >= 20) {
	  		$htmlmodal .= get_string('published', 'local_facebook');
	  	} else if($emarking->status >= 10) {
	  		$htmlmodal .= get_string('submitted', 'local_facebook');
	  	} else {
	  		$htmlmodal .= get_string('absent', 'local_facebook');
	  	}
	  	
	  	$htmlmodal .= "</div>
		  				<div class='col-md-3'>
		  					<br>
		  						<a href='".$emarkingurl."' target='_blank'>".get_string('viewexam', 'local_facebook')."</a>
		  				</div>
		  			</div>";
	}
  	
  	echo $htmlmodal;
}