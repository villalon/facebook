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

*
* @package    local
* @subpackage facebook
* @copyright  2015 Hans Jeria (hansjeria@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/moodlelib.php');      // moodle lib functions
require_once($CFG->libdir.'/datalib.php');      // data lib functions
require_once($CFG->libdir.'/accesslib.php');      // access lib functions
require_once($CFG->dirroot.'/course/lib.php');      // course lib functions
require_once($CFG->dirroot.'/enrol/guest/lib.php');      // guest enrol lib functions
include "../app/facebook-php-sdk-master/src/facebook.php";

require_login();
if (isguestuser()) {
	die();
}

if(!is_siteadmin($USER)){
	die();
}

$time = time();

$sqlgetusers = "SELECT *
		FROM {facebook_user} AS fu
		WHERE fu.status = ? ";

$users = $DB->get_records_sql($sqlgetusers, array(1));

$countusers = count($users);

echo $countusers." Updates found\n";
echo "ok\n";
echo "Updating ".date("F j, Y, G:i:s")."\n";


$AppID= $CFG->fbkAppID;
$SecretID= $CFG->fbkScrID;
$config = array(
		'appId' => $AppID,
		'secret' => $SecretID,
		'grant_type' => 'client_credentials' );
$facebook = new Facebook($config, true);

$countusersupdate = 0;

foreach($users as $user){
	$userprofile = $facebook->api ( '' . $user->facebookid . '', 'GET' );

	$newinfo = new stdClass();
	$newinfo->id = $user->id;

	$newinfo->link = $userprofile['link'];
	$newinfo->firstname = $userprofile['first_name'];
	if (isset ( $userprofile ['middle_name'] )) {
		$newinfo->middlename = $userprofile['middle_name'];
	}else{
		$newinfo->middlename = "";
	}
	$newinfo->lastname = $userprofile['last_name'];
	if(isset($userprofile['email'])){
		$newinfo->email = $userprofile['email'];
	}else{
		$newinfo->email = "NO PERMISSIONS";
	}
	
	if($DB->update_record("facebook_user", $newinfo )){
		$countusersupdate++;
		echo $countusersupdate."; ".$newinfo->firstname." ".$newinfo->middlename." ".$newinfo->lastname.
		"; Facebook id ".$user->facebookid.";\n";
	}
}


echo "ok\n";
echo $countusersupdate." Update hechos sent.\n";
echo "Ending at ".date("F j, Y, G:i:s");
$timenow=time();
$execute=$time - $timenow;
echo "\nExecute time ".$execute." sec";
echo "\n";

exit(0); // 0 means success

