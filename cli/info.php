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
 * 			   2015 Mihail Pozarski (mipozarski@alumnos.uai.cl)
 * 			   2015 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/moodlelib.php');      // moodle lib functions
require_once($CFG->libdir.'/datalib.php');      // data lib functions
require_once($CFG->libdir.'/accesslib.php');      // access lib functions
require_once($CFG->dirroot.'/course/lib.php');      // course lib functions
require_once($CFG->dirroot.'/enrol/guest/lib.php');      // guest enrol lib functions
include "../app/facebook-php-sdk-master/src/facebook.php";
// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Backup of all information of user's on facebook.

Options:
-h, --help            Print out this help

Example:
\$sudo /usr/bin/php /local/facebook/cli/info.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

cli_heading('User\'s Facebook information'); // TODO: localize
echo "\nStarting at ".date("F j, Y, G:i:s")."\n";
$AppID= $CFG->fbkAppID;
$SecretID= $CFG->fbkScrID;
$config = array(
		'appId' => $AppID,
		'secret' => $SecretID,
		'grant_type' => 'client_credentials' );
$facebook = new Facebook($config, true);

$facebook = new Facebook($config);
$facebook_id= $facebook->getUser();

$users_info = $DB->get_records('facebook_user');

foreach($users_info as $data){
	$facebook_id=$data->facebookid;
	$user = $facebook->api($facebook_id.'/friends','GET');
	$user_friends = $facebook->api('/'.$facebook_id.'/friends','GET');
	$user_likes = $facebook->api('/'.$facebook_id.'/likes?limit=500','GET');
	$array=array(
			'basic information' => $user,
			'likes'=>$user_likes,
			'friends'=>$user_friends
	);

	$json=json_encode($array);
	$data->information=$json;

	$DB->update_record('facebook_user', $data);

}

echo "ok\n";
echo "Ending at ".date("F j, Y, G:i:s");
echo "\n";



exit(0); // 0 means success
