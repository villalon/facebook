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

global $CFG, $DB, $OUTPUT, $PAGE, $USER;
$action = required_param ( 'action', PARAM_ALPHA );
$moodleid = optional_param ( 'moodleid', null , PARAM_RAW_TRIMMED );
$courseid = optional_param ( 'courseid', null , PARAM_RAW_TRIMMED );
//$lastvisit = optional_param ( 'lastvisit', null , PARAM_RAW_TRIMMED );

//switch ($action) {

	//case 'get_course_data':

		
		global $DB;
		/*
		// Parameters for post query
		$paramspost = array (
				$moodleid,
				$courseid,
				FACEBOOK_COURSE_MODULE_VISIBLE
		);
		
		// Query for the posts information
		$datapostsql = "SELECT fp.id AS postid, us.firstname AS firstname, us.lastname AS lastname, fp.subject AS subject,
			fp.modified AS modified, discussions.course AS course, discussions.id AS dis_id
			FROM {forum_posts} AS fp
			INNER JOIN {forum_discussions} AS discussions ON (fp.discussion=discussions.id)
			INNER JOIN {forum} AS forum ON (forum.id=discussions.forum)
			INNER JOIN {user} AS us ON (us.id=discussions.userid AND us.id = ?)
			INNER JOIN {course_modules} AS cm ON (cm.instance=forum.id AND cm.course = ?)
			WHERE cm.visible = ?
			GROUP BY fp.id";
		
		// Get the data from the above query
		$datapost = $DB->get_records_sql ( $datapostsql, $paramspost );
		
		// Parameters for resource query
		$paramsresource = array (
				$courseid,
				$moodleid,
				'resource',
				FACEBOOK_COURSE_MODULE_VISIBLE
		);
		
		// Query for the resource information
		$dataresourcesql = "SELECT cm.id AS coursemoduleid, r.id AS resourceid, r.name AS resourcename, r.timemodified,
			  r.course AS resourcecourse, cm.visible, cm.visibleold, CONCAT(u.firstname,' ',u.lastname) as user
			  FROM {resource} AS r
              INNER JOIN {course_modules} AS cm ON (cm.instance = r.id AND cm.course = ?)
              INNER JOIN {modules} AS m ON (cm.module = m.id)
              LEFT JOIN {logstore_standard_log} AS log ON (log.objectid = cm.id AND log.action = 'created' AND log.target = 'course_module')
              INNER JOIN {user} AS u ON (u.id = log.userid AND u.id = ?)
			  WHERE m.name = ?
			  AND cm.visible = ?
              GROUP BY cm.id";
		// Get the data from the above query
		$dataresource = $DB->get_records_sql ( $dataresourcesql, $paramsresource );
		
		// Parameters for the link query
		$paramslink = array (
				$courseid,
				$moodleid,
				'url',
				FACEBOOK_COURSE_MODULE_VISIBLE
		);
		
		// query for the link information
		$datalinksql = "SELECT url.id AS id, url.name AS urlname, url.externalurl AS externalurl, url.timemodified AS timemodified,
	          url.course AS urlcourse, cm.visible AS visible, cm.visibleold AS visibleold, CONCAT(u.firstname,' ',u.lastname) as user
		      FROM {url} AS url
              INNER JOIN {course_modules} AS cm ON (cm.instance = url.id AND cm.course = ?)
              INNER JOIN {modules} AS m ON (cm.module = m.id)
              LEFT JOIN {logstore_standard_log} AS log ON (log.objectid = cm.id AND log.action = 'created' AND log.target = 'course_module')
              INNER JOIN {user} AS u ON (u.id = log.userid AND u.id = ?)
		      WHERE m.name = ?
		      AND cm.visible = ?
              GROUP BY url.id";
		
		// Get the data from the above query
		$datalink = $DB->get_records_sql ( $datalinksql, $paramslink );
		
		// Query for getting eMarkings by course
		$dataemarkingsql = "SELECT CONCAT(s.id,e.id,s.grade) AS ids,
			s.id AS id,
			e.id AS emarkingid,
			e.course AS course,
			e.name AS testname,
			d.grade AS grade,
			d.status AS status,
			d.timemodified AS date,
			s.teacher AS teacher,
			cm.id as moduleid,
			CONCAT(u.firstname,' ',u.lastname) AS user
			FROM {emarking_draft} AS d JOIN {emarking} AS e ON (e.id = d.emarkingid AND e.type in (1,5,0))
			INNER JOIN {emarking_submission} AS s ON (d.submissionid = s.id AND d.status IN (20,30,35,40) AND s.student = ?)
			INNER JOIN {user} AS u ON (u.id = s.student)
			INNER JOIN {course_modules} AS cm ON (cm.instance = e.id AND cm.course = ?)
			INNER JOIN {modules} AS m ON (cm.module = m.id AND m.name = 'emarking')";
		
		// $emarkingparams = $param;
		$paramsemarking = array (
				$moodleid,
				$courseid
		);
		
		// Get the data from the query
		$dataemarking = $DB->get_records_sql ( $dataemarkingsql, $paramsemarking );
		

		
		$sqlparams = array (
				FACEBOOK_COURSE_MODULE_VISIBLE,
				FACEBOOK_COURSE_MODULE_VISIBLE
		);
		
		// $assignparams = array_merge($userid,$param,$sqlparams,$userid);
		// $dataassign = $DB->get_records_sql($dataassignmentsql, $assignparams);
		
		$totaldata = array ();
		// Foreach used to fill the array with the posts information
		foreach ( $datapost as $post ) {
			$posturl = new moodle_url ( '/mod/forum/discuss.php', array (
					'd' => $post->dis_id
			) );
		
			$totaldata [] = array (
					'image' => FACEBOOK_IMAGE_POST,
					'discussion' => $post->dis_id,
					'link' => $posturl,
					'title' => $post->subject,
					'from' => $post->firstname . ' ' . $post->lastname,
					'date' => $post->modified,
					'course' => $post->course
			);
		}
		
		// Foreach used to fill the array with the resource information
		foreach ( $dataresource as $resource ) {
			$date = date ( "d/m H:i", $resource->timemodified );
			$resourceurl = new moodle_url ( '/mod/resource/view.php', array (
					'id' => $resource->coursemoduleid
			) );
		
			if ($resource->visible == FACEBOOK_COURSE_MODULE_VISIBLE && $resource->visibleold == FACEBOOK_COURSE_MODULE_VISIBLE) {
				$totaldata [] = array (
						'image' => FACEBOOK_IMAGE_RESOURCE,
						'link' => $resourceurl,
						'title' => $resource->resourcename,
						'from' => $resource->user,
						'date' => $resource->timemodified,
						'course' => $resource->resourcecourse
				);
			}
		}
		// Foreach used to fill the array with the link information
		foreach ( $datalink as $link ) {
			$date = date ( "d/m H:i", $link->timemodified );
		
			if ($link->visible == FACEBOOK_COURSE_MODULE_VISIBLE && $link->visibleold == FACEBOOK_COURSE_MODULE_VISIBLE) {
				$totaldata [] = array (
						'image' => FACEBOOK_IMAGE_LINK,
						'link' => $link->externalurl,
						'title' => $link->urlname,
						'from' => $link->user,
						'date' => $link->timemodified,
						'course' => $link->urlcourse
				);
			}
		}
		
		foreach ( $dataemarking as $emarking ) {
			$emarkingurl = new moodle_url ( '/mod/emarking/view.php', array (
					'id' => $emarking->moduleid
			) );
		
			$totaldata [] = array (
					'image' => FACEBOOK_IMAGE_EMARKING,
					'link' => $emarkingurl,
					'title' => $emarking->testname,
					'from' => $emarking->user,
					'date' => $emarking->date,
					'course' => $emarking->course,
					'id' => $emarking->id,
					'grade' => $emarking->grade,
					'status' => $emarking->status,
					'teacherid' => $emarking->teacher
			);
		}
		/*
		 * foreach($dataassign as $assign){
		 * $assignurl = new moodle_url('/mod/assign/view.php', array(
		 * 'id'=>$assign->moduleid
		 * ));
		 *
		 * $totaldata[] = array(
		 * 'image'=>FACEBOOK_IMAGE_ASSIGN,
		 * 'link'=>$assignurl,
		 * 'title'=>$assign->name,
		 * 'intro'=>$assign->intro,
		 * 'date'=>$assign->due,
		 * 'due'=>$assign->due,
		 * 'course'=>$assign->course,
		 * 'status'=>$assign->status,
		 * 'grade'=>$assign->grade,
		 * 'id'=>$assign->id
		 * );
		 * }
		 */
		// Returns the final array ordered by date to index.php
		
		$totaldata = get_course_data($moodleid, $courseid);
		//$dataarray = record_sort ( $totaldata, 'date', 'true' );
		
		


// aqui parte haciendo la tabla para cada curso, osea esto es lo que hay que poner dentro de request ------------------------------------------------------------->>Z!"�!"�%(%$!"�/U%&$%&/$%&�"!LK;
// el nombre quizas lo pueda sacar del mismo cuadradito del que se hizo click

//	$fullname = $courses->fullname;

		// turn output buffering on
		//ob_start();
		$htmltable = "";
	/*
<div id="c<?php echo $courseid; ?>">

	<div class="panel panel-default"
		style="margin-right: 20px; margin-top: 20px;">

		<div class="panel">
			<nav>
				<ul>
					<p class="small;"></p>
					<p>
						<b style="font-size: 120%; color: #727272;"><span class="coursefullname"></span><?php // AQUI FALTA EL NOMBRE DEL CURSOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO  OOOOOOOOOO    echo $fullname; ?></b>
					</p>
				</ul>
			</nav>
		</div>
		<div class="scroll" style="font-size: 13px; height: 90% !important;">

			<table class="tablesorter" border="0" width="100%"
				style="font-size: 13px; margin-left: 9px;">
				<thead>
					<tr>
						<th width="1%" style="border-top-left-radius: 8px;"></th>
						<th width="5%"></th>
						<th width="33%"><?php echo get_string('rowtittle', 'local_facebook'); ?></th>
						<th width="30%"><?php echo get_string('rowfrom', 'local_facebook'); ?></th>
						<th width="20%"><?php echo get_string('rowdate', 'local_facebook'); ?></th>
						<!--  					<th width="10%" style= "border-top-right-radius: 8px;">Share</th> -->
						<th width="1%" style="background-color: transparent"></th>
					</tr>
				</thead>
				<tbody>
			<?php
		// foreach that gives the corresponding image to the new and old items created(resource,post,forum), and its title, how upload it and its link
		
			
		//este data array es lo que se obtiene de la funcion de arriba (el return viejo)------------------------------------------------------------------------------------------------------------	
		foreach ( $dataarray as $data ) {
			$discussionId = null;
			$markid = null;
			$assignid = null;
			if ($data ['course'] == $courseid) {
				$date = date ( "d/m/Y H:i", $data ['date'] );
				echo '<tr courseid="' . $courseid . '"><td';
				if ($data ['date'] > $lastvisit) {
					echo '><center><span class="glyphicon glyphicon-option-vertical" aria-hidden="true" style="color: #2a2a2a;"></span>&nbsp&nbsp';
				} else {
					echo '><center><span class="glyphicon glyphicon-option-vertical" aria-hidden="true" style="color: transparent;"></span>&nbsp&nbsp';
				}
				echo '</td><td> ';
				
				if ($data ['image'] == FACEBOOK_IMAGE_POST) {
					echo '<img src="images/post.png">';
					$discussionId = $data ['discussion'];
				} else if ($data ['image'] == FACEBOOK_IMAGE_RESOURCE) {
					echo '<img src="images/resource.png">';
				} 

				else if ($data ['image'] == FACEBOOK_IMAGE_LINK) {
					echo '<img src="images/link.png">';
				} 

				else if ($data ['image'] == FACEBOOK_IMAGE_EMARKING) {
					echo '<img src="images/emarking.png">';
					$markid = $data ['id'];
				} 

				else if ($data ['image'] == FACEBOOK_IMAGE_ASSIGN) {
					echo '<img src="images/assign.png">';
					$assignid = $data ['id'];
				}
				
				if ($discussionId != null) {
					echo '</center></td><td';
					if ($data ['date'] > $lastvisit) {
						echo ' style="font-weight:bold;"><a href="#" discussionid="' . $discussionId . '" component="forum">' . $data ['title'] . '</a>
		 									</td><td style="font-size:13px; font-weight:bold;">' . $data ['from'] . '</td><td style="font-weight:bold;">' . $date . '</td>';
						// <td><button type="button" class="btn btn-primary btn-sm" style="color:#E5E3FB">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share
						// </b></button></td>
						echo '</tr>';
					} else {
						echo '><a href="#" discussionid="' . $discussionId . '" component="forum">' . $data ['title'] . '</a>
 									</td><td style="font-size:13px">' . $data ['from'] . '</td><td>' . $date . '</td>';
						// <td> <button type="button" class="btn btn-default btn-sm" style="color:#909090;">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share
						// </b></button></td>
						echo '</tr>';
					}
					
					$postData = get_posts_from_discussion ( $discussionId );
					?>
						<!-- Modal -->
					<div class="modal fade" id="m<?php echo $discussionId; ?>"
						tabindex="-1" role="dialog" aria-labelledby="modal">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-body">
						      <?php
					foreach ( $postData as $post ) {
						$date = $post ['date'];
						echo "<div align='left'style='background-color:#E6E6E6; border-radius: 4px 4px 0 0; padding:4px; color:#333333;'><img src='images/post.png'>
 									<b>&nbsp&nbsp" . $data ['title'] . "<br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp" . $post ['user'] . ", " . date ( 'l d-F-Y', $date ) . "</b></div>";
						echo "<div align='left' style='border-radius: 0 0 4px 4px; 	word-wrap: break-word;'>" . $post ['message'] . "</div>";
						echo "<br>";
					}
					?>
						      </div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default"
										data-dismiss="modal" component="close-modal"
										modalid="m<?php echo $discussionId; ?>">Close</button>
								</div>
							</div>
						</div>
					</div>
						<?php
				} elseif ($markid != null) {
					echo '</center></td><td';
					if ($data ['date'] > $lastvisit) {
						echo ' style="font-weight:bold"><a href="#" emarkingid="' . $markid . '" component="emarking">' . $data ['title'] . '</a>
 							</td><td style="font-size:13px; font-weight:bold;">' . $data ['from'] . '</td><td style="font-size:14px; font-weight:bold;">' . $date . '</td>';
						// <td><button type="button" class="btn btn-primary btn-sm" style="color:#E5E3FB;">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share
						// </b></button></td>
						echo '</tr>';
					} else {
						echo '><a href="#" emarkingid="' . $markid . '" component="emarking">' . $data ['title'] . '</a>
 							</td><td style="font-size:13px">' . $data ['from'] . '</td><td style="font-size:14px">' . $date . '</td>';
						// <td><button type="button" class="btn btn-default btn-sm" style="color:#909090;">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share
						// </b></button></td>
						echo '</tr>';
					}
					?>
						<!-- Modal -->
					<div class="modal fade" id="e<?php echo $markid; ?>" tabindex="-1"
						role="dialog" aria-labelledby="modal">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h4 class="modal-title"><?php $course = $DB->get_record('course', array('id' => $data['course'])); echo $course->fullname; ?></h4>
							    	<?php echo $data['title']; ?>
							    </div>
								<div class="modal-body">
									<div class="row">
										<div class="col-md-4">
											<b><?php echo get_string('name', 'local_facebook'); ?></b> <br>
						  					<?php echo $data['from']; ?>
						  				</div>
										<div class="col-md-2">
											<b><?php echo get_string('grade', 'local_facebook'); ?></b> <br>
						  					<?php
					if ($data ['status'] >= 20) {
						echo $data ['grade'];
					} else {
						echo "-";
					}
					?>
						  				</div>
										<div class="col-md-3">
											<b><?php echo get_string('status', 'local_facebook'); ?></b>
											<br>
						  					<?php
					if ($data ['status'] >= 20) {
						echo get_string ( 'published', 'local_facebook' );
					} else if ($data ['status'] >= 10) {
						echo get_string ( 'submitted', 'local_facebook' );
					} else {
						echo get_string ( 'absent', 'local_facebook' );
					}
					?>
						  				</div>
										<div class="col-md-3">
											<br>
						  					<?php
					echo '<a href="' . $data ['link'] . '" target="_blank">' . get_string ( 'viewexam', 'local_facebook' ) . '</a>';
					?>
						  				</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default"
										data-dismiss="modal" component="close-modal"
										modalid="e<?php echo $markid; ?>">Close</button>
								</div>
							</div>
						</div>
					</div>
						<?php
				} elseif ($assignid != null) {
					echo '</center></td><td';
					if ($data ['date'] > $lastvisit) {
						echo ' style="font-weight:bold"><a href="#" assignid="' . $assignid . '" component="assign">' . $data ['title'] . '</a>
									</td><td style="font-size:13px; font-weight:bold;"></td><td style="font-size:14px; font-weight:bold;">' . $date . '</td>';
						// <td><button type="button" class="btn btn-primary btn-sm" style="color:#E5E3FB;">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share</b></button></td>
						echo '</tr>';
					} else {
						echo '><a href="#" assignid="' . $assignid . '" component="assign">' . $data ['title'] . '</a>
									</td><td style="font-size:13px"></td><td>' . $date . '</td>';
						// <td style="font-size:14px"><button type="button" class="btn btn-default btn-sm" style="color:#909090;">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share</b></button></td>
						echo '</tr>';
					}
					
					?>
						<!-- Modal -->
					<div class="modal fade" id="a<?php echo $assignid; ?>"
						tabindex="-1" role="dialog" aria-labelledby="modal">
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
											<br> <b><?php echo get_string('gradestatus', 'local_facebook'); ?></b>
											<br> <b><?php echo get_string('duedate', 'local_facebook'); ?></b>
											<br> <b><?php echo get_string('timeleft', 'local_facebook'); ?></b>
											<br> <b><?php echo get_string('lastmodified', 'local_facebook'); ?></b>
										</div>
										<div class="col-md-6">
						      			<?php
					if ($data ['status'] != 'submitted') {
						echo "No entregado<br>";
					} else {
						echo "Enviado para calificar<br>";
					}
					
					if ($data ['grade'] != null) {
						echo "Calificado<br>";
					} else {
						echo "Sin calificar<br>";
					}
					
					$duedate = date ( 'H:i - d/m/Y', $data ['due'] );
					echo $duedate . "<br>";
					
					$interval = $data ['due'] - time ();
					if ($interval > 0) {
						$daysleft = floor ( $interval / (60 * 60 * 24) );
						// echo $interval;
						$hoursleft = floor ( ($interval - ($daysleft * 24 * 60 * 60)) / (60 * 60) );
						echo $daysleft . " días y " . $hoursleft . " horas<br>";
					} else {
						echo "Se ha acabado el tiempo<br>";
					}
					
					$lastmodified = date ( 'H:i - d/m/Y', $data ['date'] );
					echo $lastmodified;
					?>
						      			</div>
									</div>
								</div>
								<div class="modal-footer">
									<a href="<?php echo $data['link']; ?>" target="_blank">
										<button type="button" class="btn btn-default">Ver en moodle</button>
									</a>
									<button type="button" class="btn btn-default"
										data-dismiss="modal" component="close-modal"
										modalid="a<?php echo $assignid; ?>">Close</button>
								</div>
							</div>
						</div>
					</div>
						<?php
				} else {
					echo '</center></td><td';
					if ($data ['date'] > $lastvisit) {
						echo ' style="font-weight:bold"><a href="' . $data ['link'] . '" target="_blank" component="other">' . $data ['title'] . '</a>
 									</td><td style="font-size:13px; font-weight:bold;">' . $data ['from'] . '</td><td style="font-size:14px; font-weight:bold;">' . $date . '</td>';
						// <td><button type="button" class="btn btn-primary btn-sm" style="color:#E5E3FB;">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share
						// </b></button></td>
						echo '</tr>';
					} else {
						echo '><a href="' . $data ['link'] . '" target="_blank" component="other">' . $data ['title'] . '</a>
 									</td><td style="font-size:13px">' . $data ['from'] . '</td><td style="font-size:14px">' . $date . '</td>';
						// <td><button type="button" class="btn btn-default btn-sm" style="color:#909090;">
						// <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>&nbsp<b> share
						// </b></button></td>
						echo '</tr>';
					}
				}
			}
		}
		echo "</tbody></table></div><div></div></div></div>";
		// aqui termina haciendo la tabla para cada curso, osea esto es lo que hay que poner dentro de request ------------------------------------------------------------->>Z!"�!"�%(%$!"�/U%&$%&/$%&�"!LK;
		
		
		
		// store buffer to variable and turn output buffering offer
		$html = ob_get_clean();*/
		
		$htmltable .= '<table class="tablesorter" border="0" width="100%" style="font-size: 13px; margin-left: 9px;">
						<thead>
							<tr>
								<th width="3%" style="border-top-left-radius: 8px;"></th>
								<th width="34%">Título</th>
								<th width="30%">De</th>
								<th width="30%">Fecha</th>
								<th width="3%" style="background-color: transparent"></th>
							</tr>
						</thead>
						<tbody>';
		
		foreach ($totaldata as $module) {
			$date = date ( "d/m/Y H:i", $module ['date'] );
			$htmltable .= "<tr><td>";
			if ($module ['image'] == FACEBOOK_IMAGE_POST) {
				$htmltable .= '<img src="images/post.png">';
				$discussionId = $module ['discussion'];
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_RESOURCE) {
				$htmltable .= '<img src="images/resource.png">';
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_LINK) {
				$htmltable .= '<img src="images/link.png">';
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_EMARKING) {
				$htmltable .= '<img src="images/emarking.png">';
				$markid = $module ['id'];
			}
		
			else if ($module ['image'] == FACEBOOK_IMAGE_ASSIGN) {
				$htmltable .= '<img src="images/assign.png">';
				$assignid = $module ['id'];
			}
			$link = $module['link'];
			$htmltable .= "</td><td><a href='".$link."'>". $module['title'] ."</a></td>
			<td>". $module['from'] ."</td><td>". $date ."</td></tr>";
		}
		
		$htmltable .= "</tbody></table>";
		
		echo $htmltable;
		
		// recall the buffered content
		//return $html; 
		
		
		//break;
		
		//}
		//end of actions
	