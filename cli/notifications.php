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
/*
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
*/
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

// Sql that brings the facebook user id
$sqlusers = "SELECT  u.id as id, f.facebookid, u.lastaccess, CONCAT(u.firstname,' ',u.lastname) as name
	FROM {user} AS u JOIN {facebook_user} AS f ON (u.id = f.moodleid AND f.status = ?)
	WHERE f.facebookid iS NOT NULL
	GROUP BY f.facebookid";

echo "<table border=1>";
echo "<tr><th>User id</th> <th>User name</th> <th>total Resources</th> <th>Total Urls</th> <th>Total posts</th> <th>total emarking</th></tr> ";

$appid = $CFG->fbkAppID;
$secretid = $CFG->fbkScrID;

// Facebook app information
$fb = new Facebook([
		"app_id" => $appid,
		"app_secret" => $secretid,
		"default_graph_version" => "v2.5"
]);

if( $facebookusers = $DB->get_records_sql($sqlusers, array(1)) ){
	var_dump($facebookusers);
	$counttosend = 0;
	foreach($facebookusers as $user){
		
		$courses = enrol_get_users_courses($user->id);
		
		$courseidarray = array();
		foreach ($courses as $course){
			$courseidarray[] = $course->id;
		}	
		
		if(!empty($courseidarray)){
			
			
			// get_in_or_equal used after in the IN ('') clause of multiple querys
			list($sqlincourses, $paramcourses) = $DB->get_in_or_equal($courseidarray);
			
			$params = array_merge(
					$paramcourses,
					array(
						"resource",
						1,
						1,
						$user->lastaccess,
						$user->id
					),
					$paramcourses,
					array(
						"url",
						1,
						1,
						$user->lastaccess,
						$user->id
					),
					$paramcourses,
					array(
						$user->lastaccess,
						$user->id
					),
					$paramcourses,
					array(
						$user->id	
					),
					$paramcourses,
					array(
						$user->lastaccess
					)
					
			);
			
			$sqlnotifications = "SELECT Resources.countallresources, Urls.countallurl, Posts.countallpost, Emarkings.emarkingid
			FROM
			(SELECT COUNT(cm.module) AS countallresources
			FROM {course_modules} AS cm
			INNER JOIN {modules} AS m ON (cm.module = m.id)
			INNER JOIN {resource} AS r ON (cm.instance=r.id)
			INNER JOIN {course} AS course ON (course.id = cm.course)
			INNER JOIN {context} AS ct ON (course.id = ct.instanceid)
			INNER JOIN {role_assignments} AS ra ON (ra.contextid = ct.id)
			INNER JOIN {user} AS user ON (user.id = ra.userid)
			WHERE cm.course $sqlincourses
			AND m.name IN (?)
			AND cm.visible = ?
			AND m.visible = ?
			AND r.timemodified >= ?
			AND user.id = ?
			GROUP BY cm.course, user.id)
			AS Resources,
				
			(SELECT COUNT(cm.module) AS countallurl
			FROM {course_modules} AS cm
			INNER JOIN {modules} AS m ON (cm.module = m.id)
			INNER JOIN {url} AS u ON (cm.instance=u.id)
			INNER JOIN {course} AS course ON (course.id = cm.course)
			INNER JOIN {context} AS ct ON (course.id = ct.instanceid)
			INNER JOIN {role_assignments} AS ra ON (ra.contextid = ct.id)
			INNER JOIN {user} AS user ON (user.id = ra.userid)
			WHERE cm.course $sqlincourses
			AND m.name IN (?)
			AND cm.visible = ?
			AND m.visible = ?
			AND  u.timemodified >= ?
			AND user.id = ?
			GROUP BY cm.course,user.id )
			as Urls,
			
			(SELECT fd.course AS idcoursefd, COUNT(fp.id) AS countallpost
			FROM {forum_posts} AS fp
			INNER JOIN {forum_discussions} AS fd ON (fp.discussion=fd.id)
			INNER JOIN {course} AS course ON (course.id = fd.course)
			INNER JOIN {context} AS ct ON (course.id = ct.instanceid)
			INNER JOIN {role_assignments} AS ra ON (ra.contextid = ct.id)
			INNER JOIN {user} AS user ON (user.id = ra.userid)
			WHERE fd.course $sqlincourses
			AND fp.modified > ?
			AND user.id = ?
			GROUP BY fd.course,user.id)
			as Posts,
			
			(SELECT COUNT(e.id) AS emarkingid
			FROM {emarking_draft} AS d JOIN {emarking} AS e ON (e.id = d.emarkingid AND e.course $sqlincourses AND e.type in (1,5,0))
			JOIN {emarking_submission} AS s ON (d.submissionid = s.id AND d.status IN (20,30,35,40) AND s.student = ?)
			JOIN {user} AS u ON (u.id = s.student )
			JOIN {course_modules} AS cm ON (cm.instance = e.id AND cm.course  $sqlincourses)
			JOIN {modules} AS m ON (cm.module = m.id AND m.name = 'emarking')
			WHERE d.timemodified >= ?
			GROUP BY u.id)
			as Emarkings";
		
			$notifications = $DB->get_records_sql($sqlnotifications, $params);
			
			
			foreach ($notifications as $notification){
				echo "<tr>";
				//var_dump($notification);
				echo "<td>".$user->id."</td>";
				echo "<td>".$user->name."</td>";
				echo "<td>".$notification->countallresources."</td>";
				echo "<td>".$notification->countallurl."</td>";
				echo "<td>".$notification->countallpost."</td>";
				echo "<td>".$notification->emarkingid."</td>";
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
			}
			
		}else{
		echo "chupalo no tienes cursos";
	}
	}
echo "</table>";
}



die();



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
