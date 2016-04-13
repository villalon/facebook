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
 * @package local_facebook
 * @copyright 2016 Jorge Cabaé (jcabane@alumnos.uai.cl)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;
$action = required_param ( 'action', PARAM_ALPHA );
$moodleid = optional_param ( 'moodleid', null , PARAM_RAW_TRIMMED );
$courseid = optional_param ( 'courseid', null , PARAM_RAW_TRIMMED );
//$lastvisit = optional_param ( 'lastvisit', null , PARAM_RAW_TRIMMED );

switch ($action) {

	case 'get_course_data':

		
		global $DB;
		
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
		
		/*
		 * $dataassignmentsql = "SELECT CONCAT(a.id,a.duedate) AS ids,
		 * a.id,
		 * a.name,
		 * a.duedate AS due,
		 * a.course,
		 * a.intro,
		 * a.allowsubmissionsfromdate AS date,
		 * asub.id AS submissionid,
		 * asub.timemodified AS submissiontime,
		 * asub.status,
		 * ag.grade,
		 * cm.id AS moduleid
		 * FROM {assign} AS a LEFT JOIN {assign_submission} AS asub ON (a.id = asub.assignment AND asub.userid = ?)
		 * JOIN {course_modules} AS cm ON (a.course = cm.course AND a.course $sqlin AND cm.visible = ?)
		 * JOIN {modules} AS m ON (m.id = cm.module AND m.visible = ? AND m.name = 'assign')
		 * LEFT JOIN {assign_grades} AS ag ON (a.id = ag.assignment)
		 * INNER JOIN {role_assignments} AS ra ON (ra.userid = ?)
		 * INNER JOIN {context} AS ct ON (ct.id = ra.contextid)
		 * INNER JOIN {course} AS c ON (c.id = ct.instanceid AND c.id = a.id)
		 * INNER JOIN {role} AS r ON (r.id = ra.roleid)";
		*/
		/*
		 * $userid = array($moodleid);
		 *
		 * $dataassignmentsql = "SELECT *
		 * FROM mdl_assign AS a INNER JOIN mdl_assign_submission AS asub ON (a.id = asub.assignment AND asub.userid = ?)
		 * INNER JOIN mdl_course_modules AS cm ON (a.course = cm.course AND a.course $sqlin AND cm.visible = ?)
		 * JOIN {modules} AS m ON (m.id = cm.module AND m.visible = ? AND m.name = 'assign')
		 * GROUP BY a.id";
		 *
		 * $count = $DB->count_records_sql($dataassignmentsql,array_merge($userid,$param,array(1,1)));
		 * echo "<h3>$count</h3>";
		*/
		
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
		return record_sort ( $totaldata, 'date', 'true' );
		
		
		break;

}
//end of actions