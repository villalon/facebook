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
 * Prints a particular instance of evapares
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    local
 * @subpackage facebook
 * @copyright  2016 Benjamin Espinosa (beespinosa94@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/facebook/locallib.php");
require_once ($CFG->dirroot."/local/facebook/forms.php");

global $DB, $USER, $CFG;

require_login();

$url = new moodle_url("/local/facebook/invite.php");

$context = context_system::instance ();

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title(get_string("invitetitle", "local_facebook"));
$PAGE->navbar->add(get_string("facebook", "local_facebook"));

$cid = required_param('cid', PARAM_INT);

$facebookstatussql = 'SELECT u.lastname,
		u.firstname,
		u.email,
		f.status
		FROM mdl_course AS c
		INNER JOIN mdl_context AS ct ON c.id = ct.instanceid
		INNER JOIN mdl_role_assignments AS ra ON ra.contextid = ct.id
		INNER JOIN mdl_user AS u ON u.id = ra.userid
		INNER JOIN mdl_role AS r ON r.id = ra.roleid
		LEFT JOIN mdl_facebook_user AS f ON u.id = f.moodleid
		WHERE c.id = ? AND r.id = 5';

$facebookstatus = $DB->get_records_sql($facebookstatussql, array($cid));

$check = $OUTPUT->pix_icon("i/grade_correct", get_string('linked','local_facebook'));
$cross = $OUTPUT->pix_icon("i/grade_incorrect", get_string('unlinked','local_facebook'));

$tabledata = array();
$tablerow = array();
$tableheadings = array(get_string('lastname','local_facebook'), get_string('firstname','local_facebook'),
		get_string('email','local_facebook'), get_string('linked','local_facebook')
);

echo $OUTPUT->header ();

foreach($facebookstatus AS $statusdata){
	$tablerow = array();
	
	$tablerow[] = $statusdata->lastname;
	$tablerow[] = $statusdata->firstname;
	$tablerow[] = $statusdata->email;
	if($statusdata->status != 1){
		$tablerow[] = $cross;
	}else{
		$tablerow[] = $check;
	}
	$tabledata[] = $tablerow;
}

$backtocourse =  new moodle_url("/course/view.php",array('id' => $cid));
echo $OUTPUT->single_button($backtocourse, get_string('invitebutton','local_facebook'));

$table = new html_table();
$table->head = $tableheadings;
$table->data = $tabledata;
echo html_writer::table($table);

echo $OUTPUT->footer ();
