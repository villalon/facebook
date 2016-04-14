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

$moodleid = $USER->id;
$course = $DB->get_record('course', array('fullname' => 'Curso de gente'));

echo "Id: ".$course->id."<br> Course: ".$course->fullname."<br>";


$start = microtime(TRUE);
$coursedata = get_course_data($moodleid, $course->id);
$end = microtime(TRUE);
$timeelapsed = $end - $start;

echo "Query time: ".$timeelapsed." s <br>";

echo '<table border="1" width="100%" style="font-size: 13px; margin-left: 9px;">
				<thead>
					<tr>
						<th width="1%" style="border-top-left-radius: 8px;"></th>
						<th width="5%"></th>
						<th width="33%">Título</th>
						<th width="30%">De</th>
						<th width="30%">Fecha</th>
						<th width="1%" style="background-color: transparent"></th>
					</tr>
				</thead>
				<tbody>';



foreach ($coursedata as $module) {
	$date = date ( "d/m/Y H:i", $data ['date'] );
	echo "<tr><td>";
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
	
	echo "</td><td><a href='#' discussionid='" . $discussionId . "' component='forum'>". $data['title'] ."</a></td>
			<td>". $data['from'] ."</td><td>". $date ."</td></tr>";
}

echo "</tbody></table>";