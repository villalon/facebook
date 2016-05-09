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
    * This file keeps track of upgrades to the evaluaciones block
   *
   * Sometimes, changes between versions involve alterations to database structures
   * and other major things that may break installations.
   *
   * The upgrade function in this file will attempt to perform all the necessary
   * actions to upgrade your older installation to the current version.
   *
   * If there's something it cannot do itself, it will tell you what you need to do.
   *
   * The commands in here will all be database-neutral, using the methods of
   * database_manager class
   *
   * Please do not forget to use upgrade_set_timeout()
   * before any action that may take longer time to finish.
   *
 * @package    local
 * @subpackage facebook
 * @copyright  2013 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
   
   /**
    *
   * @param int $oldversion
   * @param object $block
   */
   
   
   function xmldb_local_facebook_upgrade($oldversion) {
   	global $CFG, $DB;
   
   	$dbman = $DB->get_manager();
   	
   	if ($oldversion < 2016042801) {
   	
   		// Define key userid (foreign-unique) to be added to facebook_user.
   		$table = new xmldb_table('facebook_user');
   		$key = new xmldb_key('userid', XMLDB_KEY_FOREIGN_UNIQUE, array('moodleid'), 'mdl_user', array('id'));
   	
   		// Launch add key userid.
   		$dbman->add_key($table, $key);
   	
   		// Facebook savepoint reached.
   		upgrade_plugin_savepoint(true, 2016042801, 'local', 'facebook');
   	}
   
    if ($oldversion < 2013072900) {

        // Define field lasttimechecked to be added to facebook_user.
        $table = new xmldb_table('facebook_user');
        $field = new xmldb_field('lasttimechecked', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0', 'status');
        // Conditionally launch add field lasttimechecked.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2013072900, 'local', 'facebook');
    }
    if ($oldversion < 2013080600) {
    
    	// Define table facebook_notifications to be created.
    	$table = new xmldb_table('facebook_notifications');
    
    	// Adding fields to table facebook_notifications.
    	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    	$table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    
    	// Adding keys to table facebook_notifications.
    	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    	// Conditionally launch create table for facebook_notifications.
    	if (!$dbman->table_exists($table)) {
    		$dbman->create_table($table);
    	}
    
    
    	// Facebook savepoint reached.
    	upgrade_plugin_savepoint(true, 2013080600, 'local', 'facebook');
    
    
   }
   if ($oldversion < 2013091400) {
   
   	// Define table facebook_testing to be created.
   	$table = new xmldb_table('facebook_testing');
   
   	// Adding fields to table facebook_testing.
   	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
   	$table->add_field('username', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
   	$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
   
   	// Adding keys to table facebook_testing.
   	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
   
   	// Conditionally launch create table for facebook_testing.
   	if (!$dbman->table_exists($table)) {
   		$dbman->create_table($table);
   	}
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2013091400, 'local', 'facebook');
   }

   if ($oldversion < 2013100401) {
   
   	// Define field information to be added to facebook_user.
   	$table = new xmldb_table('facebook_user');
   	$field = new xmldb_field('information', XMLDB_TYPE_TEXT, null, null, null, null, null, 'status');
   
   	// Conditionally launch add field information.
   	if (!$dbman->field_exists($table, $field)) {
   		$dbman->add_field($table, $field);
   	}
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2013100401, 'local', 'facebook');
   }
   if ($oldversion < 2013301001) {
   
   	// Define field lasttimechecked to be added to facebook_user.
   	$table = new xmldb_table('facebook_user');
   	$field = new xmldb_field('lasttimechecked', XMLDB_TYPE_INTEGER, '20', null, null, null, '0', 'information');
   
   	// Conditionally launch add field lasttimechecked.
   	if (!$dbman->field_exists($table, $field)) {
   		$dbman->add_field($table, $field);
   	}
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2013301001, 'local', 'facebook');
   }
   
   if ($oldversion < 2015102501) {
   
   	// Define field link to be added to facebook_user.
   	$table = new xmldb_table('facebook_user');
   	$field = new xmldb_field('link', XMLDB_TYPE_CHAR, '200', null, null, null, 'NULL', 'lasttimechecked');
   
   	// Conditionally launch add field link.
   	if (!$dbman->field_exists($table, $field)) {
   		$dbman->add_field($table, $field);
   	}
   	
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2015102501, 'local', 'facebook');
   }
   
   if ($oldversion < 2015102502) {
   
   	// Define field firstname to be added to facebook_user.
   	$table = new xmldb_table('facebook_user');
   	$field = new xmldb_field('firstname', XMLDB_TYPE_CHAR, '200', null, null, null, 'NULL', 'link');
   
   	// Conditionally launch add field firstname.
   	if (!$dbman->field_exists($table, $field)) {
   		$dbman->add_field($table, $field);
   	}
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2015102502, 'local', 'facebook');
   }
    
   if ($oldversion < 2015102503) {
   
   	// Define field middlename to be added to facebook_user.
   	$table = new xmldb_table('facebook_user');
   	$field = new xmldb_field('middlename', XMLDB_TYPE_CHAR, '200', null, null, null, 'NULL', 'firstname');
   
   	// Conditionally launch add field middlename.
   	if (!$dbman->field_exists($table, $field)) {
   		$dbman->add_field($table, $field);
   	}
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2015102503, 'local', 'facebook');
   }
    
   if ($oldversion < 2015102504) {
   
   	// Define field lastname to be added to facebook_user.
   	$table = new xmldb_table('facebook_user');
   	$field = new xmldb_field('lastname', XMLDB_TYPE_CHAR, '200', null, null, null, 'NULL', 'middlename');
   
   	// Conditionally launch add field lastname.
   	if (!$dbman->field_exists($table, $field)) {
   		$dbman->add_field($table, $field);
   	}
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2015102504, 'local', 'facebook');
   }
    
   if ($oldversion < 2015102701) {
   
   	// Define field email to be added to facebook_user.
   	$table = new xmldb_table('facebook_user');
   	$field = new xmldb_field('email', XMLDB_TYPE_CHAR, '100', null, null, null, 'NULL', 'lastname');
   
   	// Conditionally launch add field email.
   	if (!$dbman->field_exists($table, $field)) {
   		$dbman->add_field($table, $field);
   	}
   
   	// Facebook savepoint reached.
   	upgrade_plugin_savepoint(true, 2015102701, 'local', 'facebook');
   }
    
   
   
   return true;
   }