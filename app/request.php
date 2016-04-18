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
global $CFG, $DB;
$action = required_param ( 'action', PARAM_ALPHA );
$moodleid = optional_param ( 'moodleid', null , PARAM_RAW_TRIMMED );
$courseid = optional_param ( 'courseid', null , PARAM_RAW_TRIMMED );
//$lastvisit = optional_param ( 'lastvisit', null , PARAM_RAW_TRIMMED );
switch ($action) {
	case 'get_course_data':
		
		global $DB;
		$totaldata = get_course_data($moodleid, $courseid);
		$htmltable = "";
		
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
		
		break;
		
		}
		//end of actions