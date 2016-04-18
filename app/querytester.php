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
 * @copyright  2016 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * PAGE USED FOR TESTING PURPOSES ONLY
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/facebook/locallib.php');
global $DB, $USER, $CFG;

require_login();
if (isguestuser()){
	die();
}

$totalstart = microtime(TRUE);

$moodleid = $USER->id;
$course = $DB->get_record('course', array('fullname' => 'Curso de gente'));

echo "Id: ".$course->id."<br> Course: ".$course->fullname."<br>";


echo "<br> Posts Query <br>";

$querystart = microtime(TRUE);

	// Parameters for post query
	$paramspost = array(
			$course->id,
			FACEBOOK_COURSE_MODULE_VISIBLE
	);
	
	// Query for the posts information
	$datapostsql = "SELECT fp.id AS postid, us.firstname AS firstname, us.lastname AS lastname, fp.subject AS subject,
				fp.modified AS modified, discussions.course AS course, discussions.id AS dis_id
				FROM {forum_posts} AS fp
				INNER JOIN {forum_discussions} AS discussions ON (fp.discussion=discussions.id AND discussions.course = ?)
				INNER JOIN {forum} AS forum ON (forum.id=discussions.forum)
				INNER JOIN {user} AS us ON (us.id=discussions.userid)
				INNER JOIN {course_modules} AS cm ON (cm.instance=forum.id)
				WHERE cm.visible = ?
				GROUP BY fp.id";
	
	// Get the data from the above query
	$datapost = $DB->get_records_sql($datapostsql, $paramspost);

$queryend = microtime(TRUE);
$querytime = $queryend - $querystart;

echo "Modules found: ".count($datapost)."<br>";

echo "Query time: ".$querytime." s <br>";

echo '<table border="1" width="100%" style="font-size: 13px; margin-left: 9px;">
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

foreach ($datapost as $post) {
	$date = date ( "d/m/Y H:i", $post ['date'] );
	echo "<tr><td>";
	if ($post ['image'] == FACEBOOK_IMAGE_POST) {
		echo '<img src="images/post.png">';
		$discussionId = $post ['discussion'];
	}
	
	else if ($post ['image'] == FACEBOOK_IMAGE_RESOURCE) {
		echo '<img src="images/resource.png">';
	}
	
	else if ($post ['image'] == FACEBOOK_IMAGE_LINK) {
		echo '<img src="images/link.png">';
	}
	
	else if ($post ['image'] == FACEBOOK_IMAGE_EMARKING) {
		echo '<img src="images/emarking.png">';
		$markid = $post ['id'];
	}
	
	else if ($post ['image'] == FACEBOOK_IMAGE_ASSIGN) {
		echo '<img src="images/assign.png">';
		$assignid = $post ['id'];
	}
	$link = $post['link'];
	echo "</td><td><a href='".$link."'>". $post['title'] ."</a></td>
			<td>". $post['from'] ."</td><td>". $date ."</td></tr>";
}

echo "</tbody></table> <br>";


echo "<br> Resources Query <br>";

$querystart = microtime(TRUE);

	// Parameters for resource query
	$paramsresource = array(
			$course->id,
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
	              INNER JOIN {user} AS u ON (u.id = log.userid)
				  WHERE m.name = ?
				  AND cm.visible = ?
	              GROUP BY cm.id";
	
	// Get the data from the above query
	$dataresource = $DB->get_records_sql($dataresourcesql, $paramsresource);

$queryend = microtime(TRUE);
$querytime = $queryend - $querystart;

echo "Resources found: ".count($dataresource)."<br>";

echo "Query time: ".$querytime." s <br>";

echo '<table border="1" width="100%" style="font-size: 13px; margin-left: 9px;">
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

foreach ($dataresource as $resource) {
	$date = date ( "d/m/Y H:i", $resource ['date'] );
	echo "<tr><td>";
	if ($resource ['image'] == FACEBOOK_IMAGE_POST) {
		echo '<img src="images/post.png">';
		$discussionId = $resource ['discussion'];
	}

	else if ($resource ['image'] == FACEBOOK_IMAGE_RESOURCE) {
		echo '<img src="images/resource.png">';
	}

	else if ($resource ['image'] == FACEBOOK_IMAGE_LINK) {
		echo '<img src="images/link.png">';
	}

	else if ($resource ['image'] == FACEBOOK_IMAGE_EMARKING) {
		echo '<img src="images/emarking.png">';
		$markid = $resource ['id'];
	}

	else if ($resource ['image'] == FACEBOOK_IMAGE_ASSIGN) {
		echo '<img src="images/assign.png">';
		$assignid = $resource ['id'];
	}
	$link = $resource['link'];
	echo "</td><td><a href='".$link."'>". $resource['title'] ."</a></td>
			<td>". $resource['from'] ."</td><td>". $date ."</td></tr>";
}

echo "</tbody></table> <br>";


echo "<br> URLs Query <br>";

$querystart = microtime(TRUE);

	// Parameters for the link query
	$paramslink = array(
			$course->id,
			'url',
			FACEBOOK_COURSE_MODULE_VISIBLE
	);
	
	//query for the link information
	$datalinksql="SELECT url.id AS id, url.name AS urlname, url.externalurl AS externalurl, url.timemodified AS timemodified,
	          url.course AS urlcourse, cm.visible AS visible, cm.visibleold AS visibleold, CONCAT(u.firstname,' ',u.lastname) as user
		      FROM {url} AS url
              INNER JOIN {course_modules} AS cm ON (cm.instance = url.id AND cm.course = ?)
              INNER JOIN {modules} AS m ON (cm.module = m.id)
              LEFT JOIN {logstore_standard_log} AS log ON (log.objectid = cm.id AND log.action = 'created' AND log.target = 'course_module')
              INNER JOIN {user} AS u ON (u.id = log.userid)
		      WHERE m.name = ? 
		      AND cm.visible = ? 
              GROUP BY url.id";
	
	// Get the data from the above query
	$datalink = $DB->get_records_sql($datalinksql, $paramslink);

$queryend = microtime(TRUE);
$querytime = $queryend - $querystart;

echo "URLs found: ".count($datalink)."<br>";

echo "Query time: ".$querytime." s <br>";

echo '<table border="1" width="100%" style="font-size: 13px; margin-left: 9px;">
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

foreach ($datalink as $link) {
	$date = date ( "d/m/Y H:i", $link ['date'] );
	echo "<tr><td>";
	if ($link ['image'] == FACEBOOK_IMAGE_POST) {
		echo '<img src="images/post.png">';
		$discussionId = $link ['discussion'];
	}

	else if ($link ['image'] == FACEBOOK_IMAGE_RESOURCE) {
		echo '<img src="images/resource.png">';
	}

	else if ($link ['image'] == FACEBOOK_IMAGE_LINK) {
		echo '<img src="images/link.png">';
	}

	else if ($link ['image'] == FACEBOOK_IMAGE_EMARKING) {
		echo '<img src="images/emarking.png">';
		$markid = $link ['id'];
	}

	else if ($link ['image'] == FACEBOOK_IMAGE_ASSIGN) {
		echo '<img src="images/assign.png">';
		$assignid = $link ['id'];
	}
	$link = $link['link'];
	echo "</td><td><a href='".$link."'>". $link['title'] ."</a></td>
			<td>". $link['from'] ."</td><td>". $date ."</td></tr>";
}

echo "</tbody></table> <br>";


echo "<br> Emarkings Query <br>";

$querystart = microtime(TRUE);

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
			$course->id
	);
	
	// Get the data from the query
	$dataemarking = $DB->get_records_sql($dataemarkingsql, $paramsemarking);

$queryend = microtime(TRUE);
$querytime = $queryend - $querystart;

echo "Emarkings found: ".count($datalink)."<br>";

echo "Query time: ".$querytime." s <br>";

echo '<table border="1" width="100%" style="font-size: 13px; margin-left: 9px;">
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

foreach ($dataemarking as $emarking) {
	$date = date ( "d/m/Y H:i", $emarking ['date'] );
	echo "<tr><td>";
	if ($emarking ['image'] == FACEBOOK_IMAGE_POST) {
		echo '<img src="images/post.png">';
		$discussionId = $emarking ['discussion'];
	}

	else if ($emarking ['image'] == FACEBOOK_IMAGE_RESOURCE) {
		echo '<img src="images/resource.png">';
	}

	else if ($emarking ['image'] == FACEBOOK_IMAGE_LINK) {
		echo '<img src="images/link.png">';
	}

	else if ($emarking ['image'] == FACEBOOK_IMAGE_EMARKING) {
		echo '<img src="images/emarking.png">';
		$markid = $emarking ['id'];
	}

	else if ($emarking ['image'] == FACEBOOK_IMAGE_ASSIGN) {
		echo '<img src="images/assign.png">';
		$assignid = $emarking ['id'];
	}
	$link = $emarking['link'];
	echo "</td><td><a href='".$emarking."'>". $emarking['title'] ."</a></td>
			<td>". $emarking['from'] ."</td><td>". $date ."</td></tr>";
}

echo "</tbody></table> <br>";

$totalend = microtime(TRUE);
$totaltime = $totalend - $totalstart;

echo "Total time: ".$totaltime." s";