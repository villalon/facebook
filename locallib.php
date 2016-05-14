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
 * @copyright  2015 Xiu-Fong Lin (xlin@alumnos.uai.cl)
 * @copyright  2015 Mihail Pozarski (mipozarski@alumnos.uai.cl)
 * @copyright  2015 Hans Jeria (hansjeria@gmail.com)
 * @copyright  2016 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
/**
 * Constants
 */
// Visible Course Module
define('FACEBOOK_COURSE_MODULE_VISIBLE', 1);
define('FACEBOOK_COURSE_MODULE_NOT_VISIBLE', 0);
// Visible Module
define('FACEBOOK_MODULE_VISIBLE', 1);
define('FACEBOOK_MODULE_NOT_VISIBLE', 0);
// Image
define('FACEBOOK_IMAGE_POST', 'post');
define('FACEBOOK_IMAGE_RESOURCE', 'resource');
define('FACEBOOK_IMAGE_LINK', 'link');
define('FACEBOOK_IMAGE_EMARKING', 'emarking');
define('FACEBOOK_IMAGE_ASSIGN', 'assign');
define('MODULE_EMARKING', 24);
define('MODULE_ASSIGN', 1);
/**
 * This function gets al the notification pending since the last check.
 * @param $sqlin from get_in_or_equal used in "IN ('')" clause    
 * @param $param from get_in_or_equal parameters      	
 * @param date $lastvisit        	
 * @return 3 arrays
 */
function get_total_notification($sqlin, $param, $lastvisit, $moodleid){
	global  $DB;
	
	//sql that counts all the new of recently modified resources
	$totalresourceparams = array(
			'resource',
			FACEBOOK_COURSE_MODULE_VISIBLE,
			FACEBOOK_MODULE_VISIBLE,
			$lastvisit
	);
	// Merge with the params that the function brings
	$paramsresource = array_merge($param,$totalresourceparams);
	
	// Sql that counts all the resourses since the last time the app was used
	$totalresourcesql = "SELECT cm.course AS idcoursecm, COUNT(cm.module) AS countallresource
			     FROM {course_modules} AS cm
			     INNER JOIN {modules} AS m ON (cm.module = m.id)
	     		 INNER JOIN {resource} AS r ON (cm.instance=r.id)
			     WHERE cm.course $sqlin 
			     AND m.name IN (?)
  			     AND cm.visible = ?
 			     AND m.visible = ?
			     AND  r.timemodified >= ?
			     GROUP BY cm.course";
	// Gets the information of the above query
	$totalresource = $DB->get_records_sql($totalresourcesql, $paramsresource);
	
	$resourcepercourse = array();
	
	// If the query brings something generate an array with all the course ids
	if($totalresource){
		foreach($totalresource as $totalresources){
			$resourcepercourse[$totalresources->idcoursecm] = $totalresources->countallresource;
		}
	}
	
	//Parameters of the urls
	$totalurlparams = array(
			'url',
			FACEBOOK_COURSE_MODULE_VISIBLE,
			FACEBOOK_MODULE_VISIBLE,
			$lastvisit
	);
	
	// Merge with the params that the function brings
	$paramsurl = array_merge($param,$totalurlparams);
	
	// Sql that counts all the urls since the last time the app was used
	$totalurlsql = "SELECT cm.course AS idcoursecm, COUNT(cm.module) AS countallurl
			FROM {course_modules} AS cm
			INNER JOIN {modules} AS m ON (cm.module = m.id)
			INNER JOIN {url} AS u ON (cm.instance=u.id)
			WHERE cm.course $sqlin
			AND m.name IN (?)
			AND cm.visible = ?
			AND m.visible = ?
			AND  u.timemodified >= ?
			GROUP BY cm.course";
	
	// Gets the infromation of the above query
	$totalurl = $DB->get_records_sql($totalurlsql, $paramsurl);
	
	$urlpercourse = array();
	
	// Makes an array that associates the course id with the counted items
	if($totalurl){
		foreach($totalurl as $totalurls){
			$urlpercourse[$totalurls->idcoursecm] = $totalurls->countallurl;
		}
	}
	
	// Post parameters for query
	$totalpostparams = array(
			$lastvisit
	);
	// Merge with the params that the function brings
	$paramsallpost = array_merge($param, $totalpostparams);
	
	// Sql that counts all the posts since the last time the app was conected.
	$totalpostsql = "SELECT fd.course AS idcoursefd, COUNT(fp.id) AS countallpost
			 FROM {forum_posts} AS fp
			 INNER JOIN {forum_discussions} AS fd ON (fp.discussion=fd.id)
			 WHERE fd.course $sqlin 
			 AND fp.modified > ?
			 GROUP BY fd.course ";
	
	$totalpost = $DB->get_records_sql($totalpostsql, $paramsallpost);
	
	$totalpostpercourse = array();
	
	// Makes an array that associates the course id with the counted items
	if($totalpost){
		foreach($totalpost as $objects){
			$totalpostpercourse[$objects->idcoursefd] = $objects->countallpost;
		}
	}
	
	$dataemarkingsql= "SELECT CONCAT(s.id,e.id,s.grade) AS ids,
		COUNT(s.id) AS total,
		e.id AS emarkingid,
		e.course AS course,
		e.name AS testname,
		d.grade AS grade,
		d.status AS status,
		d.timemodified AS date,
		s.teacher AS teacher,
		cm.id as moduleid,
		CONCAT(u.firstname,' ',u.lastname) AS user
		FROM {emarking_draft} AS d JOIN {emarking} AS e ON (e.id = d.emarkingid AND e.course $sqlin AND e.type in (1,5,0))
		JOIN {emarking_submission} AS s ON (d.submissionid = s.id AND d.status IN (20,30,35,40) AND s.student = ?)
		JOIN {user} AS u ON (u.id = s.student)
		JOIN {course_modules} AS cm ON (cm.instance = e.id AND cm.course  $sqlin)
		JOIN {modules} AS m ON (cm.module = m.id AND m.name = 'emarking')
		WHERE d.timemodified >= ?";
	
	$emarkingparams = array_merge($param,array($moodleid),$param, array($lastvisit));
	
	$totalemarkingperstudent = array();
	
	if($totalemarking = $DB->get_records_sql($dataemarkingsql, $emarkingparams)){
		foreach($totalemarking as $objects){
			$totalemarkingperstudent[$objects->course] = $objects->total;
		}
	}
	
	
	return array($resourcepercourse, $urlpercourse, $totalpostpercourse, $totalemarkingperstudent);
}
/**
 * Sort the records by the field inside record.
 * @param array $records        	
 * @param string $field        	
 * @param string $reverse        	
 * @return the records sorted
 */
function record_sort($records, $field, $reverse = false){
	
	$hash = array();
	foreach($records as $record){
		$hash[$record[$field]] = $record;
	}
	
	($reverse) ? krsort ($hash) : ksort ($hash);
	
	$records = array();
	foreach($hash as $record){
		$records[] = $record;
	}
	
	return $records;
}
/**
 * This Function gets all the posts resources and links, posted recently in the course ordered by date.
 * @param $sqlin from get_in_or_equal used in "IN ('')" clause    
 * @param $param from get_in_or_equal parameters      	
 * @return array
 */
function get_course_data ($moodleid, $courseid) {
	global $DB;
	
	// Parameters for post query
	$paramspost = array(
			$courseid,
			FACEBOOK_COURSE_MODULE_VISIBLE
	);
	
	// Query for the posts information
	$datapostsql = "SELECT fp.id AS postid, us.firstname AS firstname, us.lastname AS lastname, fp.subject AS subject,
			fp.modified AS modified, discussions.course AS course, discussions.id AS dis_id, cm.id AS moduleid
			FROM {forum_posts} AS fp
			INNER JOIN {forum_discussions} AS discussions ON (fp.discussion = discussions.id AND discussions.course = ?)
			INNER JOIN {forum} AS forum ON (forum.id = discussions.forum)
			INNER JOIN {user} AS us ON (us.id = fp.userid)
			INNER JOIN {course_modules} AS cm ON (cm.instance = forum.id)
			WHERE cm.visible = ? 
			GROUP BY fp.id";
	
	// Get the data from the above query
	$datapost = $DB->get_records_sql($datapostsql, $paramspost);
	
	// Parameters for resource query
	$paramsresource = array(
			$courseid,
			FACEBOOK_COURSE_MODULE_VISIBLE,
			'resource'
	);
	
	// Query for the resource information
	$dataresourcesql = "SELECT cm.id AS coursemoduleid, r.id AS resourceid, r.name AS resourcename, r.timemodified, 
			  r.course AS resourcecourse, cm.visible, cm.visibleold
			  FROM {resource} AS r 
              INNER JOIN {course_modules} AS cm ON (cm.instance = r.id AND cm.course = ? AND cm.visible = ?)
              INNER JOIN {modules} AS m ON (cm.module = m.id AND m.name = ?)
              GROUP BY cm.id";
	// Get the data from the above query
	$dataresource = $DB->get_records_sql($dataresourcesql, $paramsresource);
	
	// Parameters for the link query
	$paramslink = array(
			$courseid,
			FACEBOOK_COURSE_MODULE_VISIBLE,
			'url'
	);
	
	//query for the link information
	$datalinksql="SELECT url.id AS id, url.name AS urlname, url.externalurl AS externalurl, url.timemodified AS timemodified,
	          url.course AS urlcourse, cm.visible AS visible, cm.visibleold AS visibleold
		      FROM {url} AS url
              INNER JOIN {course_modules} AS cm ON (cm.instance = url.id AND cm.course = ? AND cm.visible = ?)
              INNER JOIN {modules} AS m ON (cm.module = m.id AND m.name = ?)
              GROUP BY url.id";
	
	// Get the data from the above query
	$datalink = $DB->get_records_sql($datalinksql, $paramslink);
	
	// Query for getting eMarkings by course
	$dataemarkingsql= "SELECT CONCAT(s.id,e.id,s.grade) AS ids,
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
	
	//$emarkingparams = $param;
	$paramsemarking = array(
			$moodleid,
			$courseid
	);
	
	// Get the data from the query
	$dataemarking = $DB->get_records_sql($dataemarkingsql, $paramsemarking);
	
	
	$dataassignmentsql = "SELECT a.id AS id,
			s.status AS status,
			a.timemodified AS date,
			a.duedate AS duedate,
			s.timemodified AS lastmodified,
			a.name AS assignmentname,
			cm.id AS moduleid
			FROM {assign} AS a
			INNER JOIN {course} AS c ON (a.course = c.id AND c.id = ?)
			INNER JOIN {enrol} AS e ON (c.id = e.courseid)
			INNER JOIN {user_enrolments} AS ue ON (e.id = ue.enrolid AND ue.userid = ?)
			INNER JOIN {course_modules} AS cm ON (c.id = cm.course AND cm.module = ? AND cm.visible = ?)
			INNER JOIN {assign_submission} AS s ON (a.id = s.assignment)
			GROUP BY a.id";
	
	$paramsassignment = array(
			$courseid,
			$moodleid,
			MODULE_ASSIGN,
			FACEBOOK_COURSE_MODULE_VISIBLE
	);
	
	$dataassign = $DB->get_records_sql($dataassignmentsql, $paramsassignment);
	
	//$assignparams = array_merge($userid,$param,$sqlparams,$userid);	
	//$dataassign = $DB->get_records_sql($dataassignmentsql, $assignparams);
	
	$totaldata = array();
	// Foreach used to fill the array with the posts information
	foreach($datapost as $post){
		$posturl = new moodle_url('/mod/forum/discuss.php', array(
				'd'=>$post->dis_id 
		));
		
		$totaldata[] = array(
				'image'=>FACEBOOK_IMAGE_POST,
				'discussion'=>$post->dis_id,
				'link'=>$posturl,
				'title'=>$post->subject,
				'from'=>$post->firstname . ' ' . $post->lastname,
				'date'=>$post->modified,
				'course'=>$post->course,
				'moduleid'=>$post->moduleid
		);
	}
	
	// Foreach used to fill the array with the resource information	
	foreach($dataresource as $resource){
		$date = date("d/m H:i", $resource->timemodified);
		$resourceurl = new moodle_url('/mod/resource/view.php', array(
				'id'=>$resource->coursemoduleid
		));
		
		if($resource->visible == FACEBOOK_COURSE_MODULE_VISIBLE && $resource->visibleold == FACEBOOK_COURSE_MODULE_VISIBLE){
			$totaldata[] = array (
					'image'=>FACEBOOK_IMAGE_RESOURCE,
					'link'=>$resourceurl,
					'title'=>$resource->resourcename,
					'from'=>'',
					'date'=>$resource->timemodified,
					'course'=>$resource->resourcecourse 
			);
		}
	}
	// Foreach used to fill the array with the link information
	foreach($datalink as $link){
		$date = date("d/m H:i", $link->timemodified);
		
		if($link->visible == FACEBOOK_COURSE_MODULE_VISIBLE && $link->visibleold == FACEBOOK_COURSE_MODULE_VISIBLE){
			$totaldata[] = array(
					'image'=>FACEBOOK_IMAGE_LINK,
					'link'=>$link->externalurl,
					'title'=>$link->urlname,
					'from'=>'',
					'date'=>$link->timemodified,
					'course'=>$link->urlcourse 
			);
		}
	}
	
	foreach($dataemarking as $emarking){
		$emarkingurl = new moodle_url('/mod/emarking/view.php', array(
				'id' => $emarking->moduleid
		));
		
		$totaldata[] = array(
				'image'=>FACEBOOK_IMAGE_EMARKING,
				'link'=>$emarkingurl,
				'title'=>$emarking->testname,
				'from'=>$emarking->user,
				'date'=>$emarking->date,
				'course'=>$emarking->course,
				'id'=>$emarking->id,
				'grade'=>$emarking->grade,
				'status'=>$emarking->status,
				'teacherid'=>$emarking->teacher
		);
	}
	
	foreach($dataassign as $assign){
		$assignurl = new moodle_url('/mod/assign/view.php', array(
				'id'=>$assign->moduleid
		));
		
		$duedate = date("d/m H:i", $assign->duedate);
		$date = date("d/m H:i", $assign->lastmodified);
		
		if ($assign->status == 'submitted') {
			$status = get_string('submitted', 'local_facebook');
		} else {
			$status = get_string('notsubmitted', 'local_facebook');
		}
		
		if ($DB->record_exists('assign_grades', array(
				'assignment' => $assign->id,
				'userid' => $moodleid
		))) {
			$totaldata[] = array(
					'id'=>$assign->id,
					'image'=>FACEBOOK_IMAGE_ASSIGN,
					'link'=>$assignurl,
					'title'=>$assign->assignmentname,
					'date'=>$assign->date,
					'due'=>$duedate,
					'from'=>'',
					'modified'=>$date,
					'status'=>$status,
					'grade'=>get_string('graded', 'local_facebook')
			);
		} else {
			$totaldata[] = array(
					'id'=>$assign->id,
					'image'=>FACEBOOK_IMAGE_ASSIGN,
					'link'=>$assignurl,
					'title'=>$assign->assignmentname,
					'date'=>$assign->date,
					'due'=>$duedate,
					'from'=>'',
					'modified'=>$date,
					'status'=>$status,
					'grade'=>get_string('notgraded', 'local_facebook')
			);
		}
	
		
	}
	
	// Returns the final array ordered by date to index.php
	return record_sort($totaldata, 'date', 'true');
}
function facebook_connect_table_generator($facebook_id, $link, $first_name, $middle_name, $last_name, $appname) {
	$imagetable = new html_table ();
	$infotable = new html_table ();
	$infotable->data [] = array (
			get_string ( "fbktablename", "local_facebook" ),
			$first_name." ".$middle_name." ".$last_name
	);
	$infotable->data [] = array (
			get_string ( "profile", "local_facebook" ),
			"<a href='" . $link . "' target=_blank>" . $link . "</a>"
	);
	if ($appname != null) {
		$infotable->data [] = array (
				"Link a la app",
				"<a href='http://apps.facebook.com/" . $appname . "' target=_blank>http://apps.facebook.com/" . $appname . "</a>"
		);
	} else {
		$infotable->data [] = array (
				"",
				""
		);
	}
	$imagetable->data [] = array (
			"<img src='https://graph.facebook.com/" .$facebook_id . "/picture?type=large'>",
			html_writer::table ($infotable)
	);
	echo html_writer::table ($imagetable);
}
function get_posts_from_discussion($discussionid) {
	global $DB;
	
	$sql = "SELECT fp.id AS id, fp.subject AS subject, fp.message AS message, fp.created AS date, fp.parent AS parent, 
			CONCAT(u.firstname, ' ', u.lastname) AS user 
			FROM {forum_posts} AS fp 
			INNER JOIN {user} AS u ON (fp.userid = u.id)
			WHERE fp.discussion = ? 
			GROUP BY fp.id";
	
	$discussiondata = $DB->get_records_sql($sql, array($discussionid));
	
	$data = array();
	foreach($discussiondata as $post) {
		$data[] = array(
				'id' => $post->id,
				'subject' => $post->subject,
				'message' => $post->message,
				'date' => $post->date,
				'parent' => $post->parent,
				'user' => $post->user
		);
	}
	
	return $data;
}
function cmp($a, $b){
	return strcmp ($b->totalnotifications, $a->totalnotifications);
}