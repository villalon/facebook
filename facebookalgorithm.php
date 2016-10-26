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


/*
*
*
* @package    local
* @subpackage facebook
* @copyright  2016 Hans Jeria (hansjeria@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/facebook/locallib.php");
require_once ($CFG->dirroot."/mod/emarking/marking/locallib.php");
global $USER, $CFG; 

require_login ();
if($USER->id != 10644 && $USER->id != 2 && $USER->id != 40214  && $USER->id != 381 && $USER->id != 60246 && $USER->id != 28988){
	print_error("ACCESS DENIED");
}

$type = optional_param("process",0,PARAM_INT);
// URL for current page
$url = new moodle_url("/local/facebook/facebookalgorithm.php");

$context = context_system::instance ();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title("Facebook data analysis");
echo $OUTPUT->header ();


$toprow = array();
$toprow[] = new tabobject("Tu cuenta", new moodle_url('/local/facebook/connect.php'), "Tu cuenta");
$toprow[] = new tabobject("Facebook Analysis", new moodle_url('/local/facebook/facebookalgorithm.php'), "Facebook Analysis");

echo $OUTPUT->tabtree($toprow, "Facebook Analysis");

if($USER->id == 10644 || $USER->id == 2 || $USER->id == 32806 || $USER->id == 28988){
	
	echo $OUTPUT->heading("Analisis de datos obtenidos desde facebook");
	echo $OUTPUT->heading("En los siguientes cursos es posible generar grupos de alumnos analizando información de redes sociales y el historial académico.",5);
	
	
	if($type == 0){
		$courses = new html_table();
		$courses->head = array(
				"Curso",
				"Grupos formados",
				"Analisis",
				"Generar analisis de datos provenientes de Facebook"
		);
		$courses->align = array(
				"left",
				"left",
				"center",
				"left"
		);
		$courses->size = array(
				"40%",
				"8%",
				"30%",
				"22%"
		);
		
		$empty = facebook_progressbar(0, "Likes")."<br>";
		$empty .= facebook_progressbar(0, "Educational")."<br";
		$empty .= facebook_progressbar(0, "Music")."<br>";
		$empty .= facebook_progressbar(0, "Friends");
		
		$cross = $OUTPUT->action_icon(
				new moodle_url("#"),
				new pix_icon("i/invalid", "No")
		);
		
		$courses->data[] = array(
				"Taller de investigación y desarrollo Sec. 1 Stgo MCI A/02 2016",
				$cross,
				$empty,
				"<a href='".$CFG->wwwroot."/local/facebook/facebookalgorithm.php?process=1"."'>Generar grupos</a>"
		
		);
		
		$courses->data[] = array(
				"Comportamiento organizacional Sec. 5 Stgo 2do sem 2016",
				$cross,
				$empty,
				"<a href='http://webcursos.uai.cl/course/view.php?id=30736'>Generar grupos</a>"
		
		);
		
		$courses->data[] = array(
				"Sistemas de la información Sec. 3 Stgo 2do sem 2016",
				$cross,
				$empty,
				"<a href='".$CFG->wwwroot."/local/facebook/facebookalgorithm.php?process=1"."'>Generar grupos</a>"
		
		);
		
		
	}else if($type == 1){
		
		$courses = new html_table();
		$courses->head = array(
				"Curso",
				"Grupos formados",
				"Analisis",
				"Generar analisis de datos provenientes de Facebook"
		);
		$courses->align = array(
				"left",
				"left",
				"center",
				"left"
		);
		$courses->size = array(
				"40%",
				"13%",
				"30%",
				"17%"
		);
		
		$ticket = $OUTPUT->action_icon(
				new moodle_url("#"),
				new pix_icon("i/valid", "Si")
		);
		
		$cross = $OUTPUT->action_icon(
				new moodle_url("#"),
				new pix_icon("i/invalid", "No")
		);
		
		$progressbar = facebook_progressbar(90, "Likes")."<br>";
		$progressbar .= facebook_progressbar(46, "Educational")."<br>";
		$progressbar .= facebook_progressbar(88, "Music")."<br>";
		$progressbar .= facebook_progressbar(53, "Friends");
		
		$empty = facebook_progressbar(0, "Likes")."<br>";
		$empty .= facebook_progressbar(0, "Educational")."<br";
		$empty .= facebook_progressbar(0, "Music")."<br>";
		$empty .= facebook_progressbar(0, "Friends");
		
		$courses->data[] = array(
				"Taller de investigación y desarrollo Sec. 1 Stgo MCI A/02 2016",
				$cross,
				$empty,
				"<a href='http://webcursos.uai.cl/course/view.php?id=32822'>Generar grupos</a>"
		
		);
		
		$courses->data[] = array(
				"Comportamiento organizacional Sec. 5 Stgo 2do sem 2016",
				$cross,
				$empty,
				"<a href='http://webcursos.uai.cl/course/view.php?id=30736'>Generar grupos</a>"
		
		);
		
		$courses->data[] = array(
				"Sistemas de la información Sec. 3 Stgo 2do sem 2016",
				$ticket,
				$progressbar,
				"Grupos ya creados"
		
		);
		echo "<hr>Se enviaron correos a los alumnos del curso <b>Sistemas de la información Sec. 3 Stgo 2do sem 2016</b> con sus grupos formados por el analisis de la información de redes sociales e historial académico.<hr> ";
		
	}
	
	echo html_writer::table($courses);
	
}else if($USER->id == 40214  || $USER->id == 381 || $USER->id == 60246){

	$courses = new html_table();
	$courses->head = array(
			"Curso",
			"Grupos formados",
			"Analisis",
			"Ver grupo"
	);
	$courses->align = array(
			"left",
			"left",
			"center",
			"left"
	);
	$courses->size = array(
			"40%",
			"13%",
			"30%",
			"17%"
	);
	
	$ticket = $OUTPUT->action_icon(
			new moodle_url("#"),
			new pix_icon("i/valid", "Si")
	);
	
	$cross = $OUTPUT->action_icon(
			new moodle_url("#"),
			new pix_icon("i/invalid", "No")
	);
	
	//Likes, music, educational_history, friends,
	$progressbar = facebook_progressbar(46, "Likes")."<br>";
	$progressbar .= facebook_progressbar(20, "Educational")."<br>";
	$progressbar .= facebook_progressbar(72, "Music")."<br>";
	$progressbar .= facebook_progressbar(68, "Friends");
	
	$empty = facebook_progressbar(0, "Likes")."<br>";
	$empty .= facebook_progressbar(0, "Educational")."<br";
	$empty .= facebook_progressbar(0, "Music")."<br>";
	$empty .= facebook_progressbar(0, "Friends");
	
	$courses->data[] = array(
			"Sistemas de la información Sec. 3 Stgo 2do sem 2016",
			$ticket,
			$progressbar,
			"<a href='http://webcursos.uai.cl/course/view.php?id=32806'>Ver grupos</a>"
			
	);
	
	$courses->data[] = array(
			"Organización industrial Sec. 2 Stgo 2do sem 2016",
			$cross,
			$empty,
			"<a href='http://webcursos.uai.cl/course/view.php?id=32822'>Ver grupos</a>"
	
	);
	
	$courses->data[] = array(
			"Taller de maneja y analisis de datos Sec. 1 Stgo 2do sem 2016",
			$cross,
			$empty,
			"<a href='http://webcursos.uai.cl/course/view.php?id=30736'>Ver grupos</a>"
	
	);

	echo $OUTPUT->heading("Analisis de datos obtenidos desde facebook");
	echo $OUTPUT->heading("Los datos son utilizados para la creación de grupos de trabajo en los cursos que lo requieran, el fin es aumentar el rendimiento total del curso y motivar la colaboración entre los estudiantes.",5);
	
	echo html_writer::table($courses);
}
echo $OUTPUT->footer ();

function facebook_progressbar($number, $text){
	$width = "width:$number%; height: 20px; line-height: 20px; border-radius: 3px 0px 0px 3px;";
	$strong = html_writer::span($number . "%", 'bar', array(
			"style" => $width));
	$graph = html_writer::div($strong, 'graph', array(
			"style" => "border-radius:3px;"));
	$graphcont = html_writer::div($text." ".$graph, 'graphcont');
	return $graphcont;
}
