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
 * This script send notifications on facebook
 *
 * @package    local/facebook/
 * @subpackage cli
 * @copyright  2010 Jorge Villalon (http://villalon.cl)
 * @copyright  2015 Mihail Pozarski (mipozarski@alumnos.uai.cl)
 * @copyright  2015 - 2016 Hans Jeria (hansjeria@gmail.com)
 * @copyright  2016 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once ($CFG->libdir . '/clilib.php'); 
require_once($CFG->dirroot."/local/facebook/app/Facebook/autoload.php");
require_once($CFG->dirroot."/local/facebook/app/Facebook/FacebookRequest.php");
include $CFG->dirroot."/local/facebook/app/Facebook/Facebook.php";
use Facebook\FacebookResponse;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequire;
use Facebook\Facebook;
use Facebook\Request;

// Now get cli options
list($options, $unrecognized) = cli_get_params(
		array('help'=>false),
        array('h'=>'help')
		);
if($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
// Text to the facebook console
if($options['help']) {
    $help =
// Todo: localize - to be translated later when everything is finished
"Send facebook notifications when a course have some news.
Options:
-h, --help            Print out this help
Example:
\$sudo /usr/bin/php /local/facebook/cli/notifications.php";
echo $help;
die();
}


cli_heading('Facebook notifications'); // TODO: localize

echo "\nSearching for new notifications\n";
echo "\nStarting at ".date("F j, Y, G:i:s")."\n";

// Define used lower in the querys
define('FACEBOOK_COURSE_MODULE_VISIBLE', 1);
// Facebook 
define('FACEBOOK_LINKED', 1);
define('MODULE_ASSIGN', 1);

$initialtime = time();

// Sql that brings the facebook user id
$sqlusers = "SELECT  u.id AS id, 
		f.facebookid, 
		u.lastaccess, 
		CONCAT(u.firstname,' ',u.lastname) AS name, 
		f.lasttimechecked, 
		u.email
	FROM {facebook_user} AS f  RIGHT JOIN {user} AS u ON (u.id = f.moodleid AND f.status = ?)
	WHERE f.facebookid IS NOT NULL
	GROUP BY f.facebookid, u.id";

$appid = $CFG->fbk_appid;
$secretid = $CFG->fbk_scrid;

// Table made for debugging purposes
//echo "<table border=1>";
//echo "<tr><th>User id</th> <th>User name</th> <th> last access</th> <th>total Resources</th> <th>Total Urls</th> <th>Total posts</th> <th>total emarking</th> <th>Total Assings</th> <th>Notification sent</th> </tr> ";

// Counts every notification sent
$sent = 0;

// Facebook app information
$fb = new Facebook([
		"app_id" => $appid,
		"app_secret" => $secretid,
		"default_graph_version" => "v2.5"
]);

if( $facebookusers = $DB->get_records_sql($sqlusers, array(FACEBOOK_LINKED)) && $CFG->fbk_notifications ){
	foreach($facebookusers as $user){
		
		$courses = enrol_get_users_courses($user->id);
		$courseidarray = array();
		
		// Save all courses ids in an array
		foreach ($courses as $course){
			$courseidarray[] = $course->id;
		}	
		
		if(!empty($courseidarray)){
			
			// Use the last time in web or app
			if($user->lastaccess < $user->lasttimechecked){
				$user->lastaccess = $user->lasttimechecked; 
			}			
			
			// get_in_or_equal used in the IN ('') clause of multiple querys
			list($sqlincourses, $paramcourses) = $DB->get_in_or_equal($courseidarray);
			
			// Parameters for post query
			$paramspost = array_merge($paramcourses, array(
					FACEBOOK_COURSE_MODULE_VISIBLE,
					$user->lastaccess
			));
			
			// Query for the posts information
			$datapostsql = "SELECT COUNT(data.id) AS count
					FROM (
					    SELECT fp.id AS id
					    FROM {forum_posts} AS fp
					    INNER JOIN {forum_discussions} AS discussions ON (fp.discussion = discussions.id AND discussions.course $sqlincourses)
					    INNER JOIN {forum} AS forum ON (forum.id = discussions.forum)
					    INNER JOIN {user} AS us ON (us.id = fp.userid)
					    INNER JOIN {course_modules} AS cm ON (cm.instance = forum.id AND cm.visible = ?)
					    WHERE fp.modified > ?
					    GROUP BY fp.id)
			        AS data";
			
			// Parameters for resource query
			$paramsresource = array_merge($paramcourses, array(
					FACEBOOK_COURSE_MODULE_VISIBLE,
					'resource',
					$user->lastaccess
			));
			
			// Query for the resource information
			$dataresourcesql = "SELECT COUNT(data.id) AS count
					  FROM (
					      SELECT cm.id AS id
					      FROM {resource} AS r
		                  INNER JOIN {course_modules} AS cm ON (cm.instance = r.id AND cm.course $sqlincourses AND cm.visible = ?)
		                  INNER JOIN {modules} AS m ON (cm.module = m.id AND m.name = ?)
		                  WHERE r.timemodified > ?
		                  GROUP BY cm.id)
			          AS data";
			
			// Parameters for the link query
			$paramslink = array_merge($paramcourses, array(
					FACEBOOK_COURSE_MODULE_VISIBLE,
					'url',
					$user->lastaccess
			));
			
			//query for the link information
			$datalinksql="SELECT COUNT(data.id) AS count
				      FROM (
				          SELECT url.id AS id
				          FROM {url} AS url
		                  INNER JOIN {course_modules} AS cm ON (cm.instance = url.id AND cm.course $sqlincourses AND cm.visible = ?)
		                  INNER JOIN {modules} AS m ON (cm.module = m.id AND m.name = ?)
		                  WHERE url.timemodified > ?
		                  GROUP BY url.id)
			          AS data";
			
			//$emarkingparams = $param;
			$paramsemarking = array_merge(
					array(
						$user->lastaccess,
						$user->id
					),
					$paramcourses
			);
			
			// Query for getting eMarkings by course
			$dataemarkingsql= "SELECT COUNT(data.id) AS count
					FROM (
					    SELECT d.id AS id
					    FROM {emarking_draft} AS d JOIN {emarking} AS e ON (e.id = d.emarkingid AND e.type in (1,5,0) AND d.timemodified > ?)
					    INNER JOIN {emarking_submission} AS s ON (d.submissionid = s.id AND d.status IN (20,30,35,40) AND s.student = ?)
					    INNER JOIN {user} AS u ON (u.id = s.student)
					    INNER JOIN {course_modules} AS cm ON (cm.instance = e.id AND cm.course $sqlincourses)
					    INNER JOIN {modules} AS m ON (cm.module = m.id AND m.name = 'emarking'))
					AS data";
			
			$paramsassignment = array_merge($paramcourses, array(
					$user->id,
					MODULE_ASSIGN,
					FACEBOOK_COURSE_MODULE_VISIBLE,
					$user->lastaccess
			));
			
			$dataassignmentsql = "SELECT COUNT(data.id) AS count
					FROM (
					    SELECT a.id AS id
					    FROM {assign} AS a
					    INNER JOIN {course} AS c ON (a.course = c.id AND c.id $sqlincourses)
					    INNER JOIN {enrol} AS e ON (c.id = e.courseid)
					    INNER JOIN {user_enrolments} AS ue ON (e.id = ue.enrolid AND ue.userid = ?)
					    INNER JOIN {course_modules} AS cm ON (c.id = cm.course AND cm.module = ? AND cm.visible = ?)
					    INNER JOIN {assign_submission} AS s ON (a.id = s.assignment)
					    WHERE a.timemodified > ?
					    GROUP BY a.id)
			        AS data";
			
			/*
			echo "<tr>";
			echo "<td>".$user->id."</td>";
			echo "<td>".$user->name."</td>";
			echo "<td>".$user->lastaccess." - ".date("H:i / d-m-Y",$user->lastaccess)."</td>";
			*/
			
			// Count total notifications for the current user
			$notifications = 0;
			
			// Print the obtained information in the table (debugging)
			if($resources = $DB->get_record_sql($dataresourcesql, $paramsresource)){
				//echo "<td>".$resources->count."</td>";
				$notifications += $resources->count;
			}else{
				//echo "<td>0</td>";
			}
			
			if($urls = $DB->get_record_sql($datalinksql, $paramslink)){
				//echo "<td>".$urls->count."</td>";
				$notifications += $urls->count;
			} else {
 				//echo "<td>0</td>";
  			}
			
			if($posts = $DB->get_record_sql($datapostsql, $paramspost)){
				//echo "<td>".$posts->count."</td>";
				$notifications += $posts->count;
			}else{
				//echo "<td>0</td>";
			}
			
			if($emarkings = $DB->get_record_sql($dataemarkingsql, $paramsemarking) && $CFG->fbk_emarking ){
				//echo "<td>".$emarkings->count."</td>";
				$notifications += $emarkings->count;
			}else{
				//echo "<td>0</td>";
			}
			
			if($assigns = $DB->get_record_sql($dataassignmentsql, $paramsassignment)){
				//echo "<td>".$assigns->count."</td>";
				$notifications += $assigns->count;
			}else{
				//echo "<td>0</td>";
			}
			
			if ($notifications == 0) {
				//echo "<td>No notifications found</td>";
			} else
			
			// Check if there are notifications to send
			if ($user->facebookid != null && $notifications != 0) {
				if ($notifications == 1) {
					$template = "Tienes $notifications notificación de Webcursos.";
				} else {
					$template = "Tienes $notifications notificaciones de Webcursos.";
				}
				
				$data = array(
						"link" => "",
						"message" => "",
						"template" => $template
				);
			
				$fb->setDefaultAccessToken($appid.'|'.$secretid);
				
				// Handles when the notifier throws an exception (couldn't send the notification)
				try {
					$response = $fb->post('/'.$user->facebookid.'/notifications', $data);
					$return = $response->getDecodedBody();
					echo "Send ".$notifications." notification to ".$user->name." - ".$user->email." |  \n";
				} catch (Exception $e) {
					$exception = $e->getMessage();
					echo "Exception found: $exception \n";
					
					// If the user hasn't installed the app, update it's record to status = 0
					if (strpos($exception, "not installed") !== FALSE) {
						$updatequery = "UPDATE {facebook_user} 
								SET status = ? 
								WHERE moodleid = ?";
						
						$updateparams = array(
								0,
								$user->id
						);
						
						if ($DB->execute($updatequery, $updateparams)) {
							echo "Record updated, set status to 0. \n";
						} else {
							echo "Could not update the record. \n";
						}
						
						//echo "</td>";
					}
				}
				$sent += $notifications;
			}
		}
	}
	//echo "</table>";
	// Check how many notifications were sent
	echo $sent." notifications sent. \n";
	
	// Displays the time required to complete the process
	$finaltime = time();
	$executiontime = $finaltime - $initialtime;
	
	echo "Execution time: ".$executiontime." seconds. \n";
}

exit(0);