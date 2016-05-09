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
 * @copyright  2015 Hans Jeria (hansjeria@gmail.com)
 * @copyright  2016 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot."/local/facebook/app/Facebook/autoload.php");
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->libdir.'/datalib.php');
require_once($CFG->libdir.'/accesslib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/enrol/guest/lib.php');
require_once($CFG->dirroot."/local/facebook/app/Facebook/FacebookRequest.php");
include $CFG->dirroot."/local/facebook/app/Facebook/Facebook.php";
use Facebook\FacebookResponse;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequire;
use Facebook\Facebook;
use Facebook\Request;

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Send facebook notifications when a course have some news.

Options:
-h, --help            Print out this help

Example:
\$sudo /usr/bin/php /local/facebook/cli/notifications.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

cli_heading('Facebook notifications'); // TODO: localize

echo "\nSearching for new notifications\n";
echo "\nStarting at ".date("F j, Y, G:i:s")."\n";

// Define used lower in the querys
define('FACEBOOK_NOTIFICATION_LOGGEDOFF','message_provider_local_facebook_notification_loggedoff');
define('FACEBOOK_NOTIFICATION_LOGGEDIN','message_provider_local_facebook_notification_loggedin');
// Define used lower in the querys
define('FACEBOOK_COURSE_MODULE_VISIBLE', 1);
define('FACEBOOK_COURSE_MODULE_NOT_VISIBLE', 0);
// Visible Module
define('FACEBOOK_MODULE_VISIBLE', 1);
define('FACEBOOK_MODULE_NOT_VISIBLE', 0);
// Facebook Notifications
define('FACEBOOK_NOTIFICATIONS_WANTED', 1);
define('FACEBOOK_NOTIFICATIONS_UNWANTED', 0);

define('MODULE_ASSIGN', 1);

$initialtime = time();

// Sql that brings the facebook user id
$sqlusers = "SELECT  u.id as id, f.facebookid AS facebookid, u.lastaccess, CONCAT(u.firstname,' ',u.lastname) as name
	FROM {user} AS u JOIN {facebook_user} AS f ON (u.id = f.moodleid AND f.status = ?)
	WHERE f.facebookid IS NOT NULL
	GROUP BY f.facebookid";

// Table made for debugging purposes
echo "<table border=1>";
echo "<tr><th>User id</th> <th>User name</th> <th>total Resources</th> <th>Total Urls</th> <th>Total posts</th> <th>total emarking</th> <th>Total Assings</th> <th>Notification sent</th> </tr> ";

$appid = $CFG->fbkAppID;
$secretid = $CFG->fbkScrID;

// Counts every notification sent
$sent = 0;

// Facebook app information
$fb = new Facebook([
		"app_id" => $appid,
		"app_secret" => $secretid,
		"default_graph_version" => "v2.5"
]);

if( $facebookusers = $DB->get_records_sql($sqlusers, array(1)) ){
	foreach($facebookusers as $user){
		var_dump($user);
		
		$courses = enrol_get_users_courses($user->id);
		$courseidarray = array();
		
		// Save all courses ids in an array
		foreach ($courses as $course){
			$courseidarray[] = $course->id;
		}	
		
		if(!empty($courseidarray)){
			
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
			
			
			echo "<tr>";
			echo "<td>".$user->id."</td>";
			echo "<td>".$user->name."</td>";
			
			// Count total notifications for the current user
			$notifications = 0;
			
			// Print the obtained information in the table (debugging)
			if($resources = $DB->get_record_sql($dataresourcesql, $paramsresource)){
				echo "<td>".$resources->count."</td>";
				$notifications += $resources->count;
			} else {
				echo "<td>0</td>";
			}
			
			if($urls = $DB->get_record_sql($datalinksql, $paramslink)){
				echo "<td>".$urls->count."</td>";
				$notifications += $urls->count;
			} else {
				echo "<td>0</td>";
			}
			
			if($posts = $DB->get_record_sql($datapostsql, $paramspost)){
				echo "<td>".$posts->count."</td>";
				$notifications += $posts->count;
			} else {
				echo "<td>0</td>";
			}
			
			if($emarkings = $DB->get_record_sql($dataemarkingsql, $paramsemarking)){
				echo "<td>".$emarkings->count."</td>";
				$notifications += $emarkings->count;
			} else {
				echo "<td>0</td>";
			}
			
			if($assigns = $DB->get_record_sql($dataassignmentsql, $paramsassignment)){
				echo "<td>".$assigns->count."</td>";
				$notifications += $assigns->count;
			} else {
				echo "<td>0</td>";
			}
			
			// Check if there are notifications to send
			if ($notifications == 0) {
				echo "<td>No notifications found</td>";
			} elseif ($user->facebookid != null) {
				if ($notifications == 1) {
					$template = "Tienes $notifications notificación de WebCursos.";
				} else {
					$template = "Tienes $notifications notificaciones de WebCursos.";
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
					
					if($return['success'] == TRUE){
						echo "<td>Sent: $notifications</td>";
						$sent++;
					} else {
						echo "<td>Not sent (success = FALSE)</td>";
					}
				} catch (Exception $e) {
					$exception = $e->getMessage();
					echo "<td>Exception found: <br>$exception<br>";
					
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
							echo "Record updated, set status to 0.";
						} else {
							echo "Could not update the record.";
						}
						
						echo "</td>";
					}
				}
			}
			
			/*
			if( ($notification->countallresources+$notification->countallurl+$notification->countallpost+$notification->emarkingid) > 0 ){
				$data = array(
						"link" => "",
						"message" => "",
						"template" => "Tienes nuevas notificaciones de WebCursos."
				);
				
				$fb->setDefaultAccessToken($appid.'|'.$secretid);
				$response = $fb->post('/'.$user->facebookid.'/notifications', $data);
				$return = $response->getDecodedBody();
				
				if($return['success'] == TRUE){
					// Echo that tells to who notifications were sent, ordered by id
					echo $counttosend." ".$user->facebookid." ok\n";
					$counttosend++;
				}else{
					echo $userfacebookid->facebookid." fail\n";
				}
			}*/
			
			echo "</tr>";
		}else{
			// When the current user isn't enroled in any course (debugging)
			echo "<br><b>Chupalo no tienes cursos</b><br>";
		}
	}
	echo "</table>";
	
	// Check how many notifications were sent
	echo $sent." notifications sent.<br>";
	
	// Displays the time required to complete the process
	$finaltime = time();
	$executiontime = $finaltime - $initialtime;
	
	echo "Execution time: ".$executiontime." seconds.";
}

die();

// ----------------- previous version ----------------- //

// Sql that brings the latest time modified from facebook_notifications
$maxtimenotificationssql = "SELECT max(timemodified) AS maxtime	
		FROM {facebook_notifications}
		WHERE status = ?";

$maxtimenotifications = $DB->get_record_sql($maxtimenotificationssql, array(FACEBOOK_NOTIFICATIONS_WANTED));

// If clause that makes the timemodified=0 if there are no records in the data base
if($maxtimenotifications->maxtime == null){
	$timemodified = 0;
}else{
	$timemodified = $maxtimenotifications->maxtime;
}

// Parameters for resources query
$params = array(
		'resource', 
		'emarking', 
		'url', 
		'assign',
		FACEBOOK_COURSE_MODULE_VISIBLE,
		FACEBOOK_MODULE_VISIBLE,
);

// Sql for resource information
//TODO: agregar foros, revisar fecha que incluir mas notificaciones.
$sql = "SELECT cm.id, 
		cm.course AS course, 
		cm.module AS module, 
		m.name AS name 
		FROM {course_modules} AS cm 
		INNER JOIN {modules} AS m ON 
		(cm.module = m.id AND m.name IN (?, ?, ?, ?) AND m.visible = ?) 
        WHERE cm.visible = ?";

$querydata = $DB->get_records_sql($sql, $params);

$allnotifications = array();
$courseidarray = array();
$notificationsof = array();

// foreach that get all the data from the resource query to an array
foreach ($querydata as $log){
	$record = new stdClass();
	$record->courseid = $log->course;
	$record->time = time();
	$record->status = 0;
	$record->timemodified = 0;
	$allnotifications[] = $record;
	
	$courseidarray[] = $log->course;
}

// if clause that makes sure if there is something in the array , if there is it saves the array in the data base
if(count($allnotifications)>0){
		$DB->insert_records('facebook_notifications', $allnotifications);
}

$countnotifications = count($allnotifications);
$time = time();

// Parameters for update query
$paramsupdate = array(
			FACEBOOK_NOTIFICATIONS_WANTED,
			$time,
			FACEBOOK_NOTIFICATIONS_UNWANTED,
			$timemodified
	);

$updatequery = "UPDATE {facebook_notifications}
		SET status = ?, timemodified = ?
		WHERE status = ? AND time >= ?";

$DB->execute($updatequery, $paramsupdate);
	
echo $countnotifications." Notifications found\n";
echo "ok\n";
echo "Sending notifications ".date("F j, Y, G:i:s")."\n";

$appid = $CFG->fbkAppID;
$secretid = $CFG->fbkScrID;

// Facebook app information
$fb = new Facebook([
		"app_id" => $appid,
		"app_secret" => $secretid,
		"default_graph_version" => "v2.5"
]);

$counttosend = 0;

// User parameters for query
$userparams = array(
		FACEBOOK_NOTIFICATION_LOGGEDOFF,
		FACEBOOK_NOTIFICATION_LOGGEDIN,
		FACEBOOK_NOTIFICATIONS_WANTED
);

// List the result of get_in_or_equal
list($sqlin, $courseparam) = $DB->get_in_or_equal($courseidarray);

$paramsmerge = array_merge($courseparam,$userparams);

// Sql that brings the facebook user id
$sqlusers = "SELECT  facebookuser.facebookid AS facebookid 
	     FROM {user_enrolments} AS enrolments
	     INNER JOIN  {enrol} AS enrol ON (enrolments.enrolid=enrol.id)
	     INNER JOIN {user_preferences} AS preferences ON (preferences.userid=enrolments.userid)
	     INNER JOIN {facebook_user} AS facebookuser ON (facebookuser.moodleid=enrolments.userid)
	     WHERE enrol.courseid $sqlin
	     AND preferences.name IN (?,?)
	     AND preferences.value like '%facebook%' AND facebookuser.status = ?
	     GROUP BY facebookuser.facebookid";


// Gets the information of the above query
$arrayfacebookid = $DB->get_records_sql($sqlusers,$paramsmerge);

//Foreach that notify all the facebook users with new staff to see
foreach($arrayfacebookid as $userfacebookid){
	
	if($userfacebookid->facebookid != null){
		$data = array(
				"link" => "",
				"message" => "",
				"template" => "Tienes nuevas notificaciones de WebCursos."
		);
		
		$fb->setDefaultAccessToken($appid.'|'.$secretid);
		$response = $fb->post('/'.$userfacebookid->facebookid.'/notifications', $data);
		$return = $response->getDecodedBody();
		if($return['success'] == TRUE){		
			// Echo that tells to who notifications were sent, ordered by id
			echo $counttosend." ".$userfacebookid->facebookid." ok\n";
			$counttosend++;
		}else{
			echo $userfacebookid->facebookid." fail\n";
		}
	}
}


echo "ok\n";
echo $counttosend." notificantions sent.\n";
echo "Ending at ".date("F j, Y, G:i:s");
$timenow = time();
$execute = $timenow - $time;
echo "\nExecute time ".$execute." sec";
echo "\n";

exit(0); // 0 means success
