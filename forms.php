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
 * @package local_facebook
 * @copyright 2015 Xiu-Fong Lin (xlin@alumnos.uai.cl)
 * @copyright 2015 Mihail Pozarski (mipozarski@alumnos.uai.cl)
 * 			  2015 Hans Jeria (hansjeria@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');

class buttons extends moodleform{
	function definition() {
		
		$mform =& $this->_form;
		
		$buttonarray=array();
		$buttonarray[]=$mform->createElement('static', 'description2', '',get_string('cancelnotifications', 'local_facebook'));
		$buttonarray[]=$mform->createElement('submit','disconnect',get_string('disconnectaccount', 'local_facebook'));
		$mform->addGroup($buttonarray);
		
	}
}

class connect extends moodleform{
	function definition() {

		$mform =& $this->_form;
		$instance = $this->_customdata;
		$duplicate=$instance['duplicate'];
	
		if($duplicate==false){
		
		$mform->addElement('static', 'description1', '',get_string('connectquestion', 'local_facebook'));
		$mform->addElement('submit','connect',get_string('connectbutton', 'local_facebook'));
		
		}
		//si la cuenta ya se encuentra enlazada y activa no lo deja enlazar
		else{
			$mform->addElement('static', 'description1', '',get_string('accused', 'local_facebook',array("style="=>"color:red")));
			$mform->addElement('button','link',get_string('back', 'local_facebook'),array("onClick"=>"window.location.href='../../'"));
			}	
	
	}
}







