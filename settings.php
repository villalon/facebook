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
//a

/**
 * 
 *
 * @package    local
 * @subpackage facebook
 * @copyright  2016 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	
	$settings = new admin_settingpage('local_facebook', 'Facebook');
	
	$ADMIN->add('localplugins', $settings);

	/*
	$settings->add(new admin_setting_configtext(
    		name for $CFG - example $CFG->appname,
    		Text for field,
    		Description text,
    		Default value,
    		Type value - example PARAM_TEXT
    ));
	 */
	
	// Basic Settings
	$settings->add(
			new admin_setting_heading(
					'fkb_basicsettings',
					"Configuración Básica",
					''
			)
	);
	$settings->add(
			new admin_setting_configtext(
				'fbk_email',
				'E-mail de contacto',
				'Correo electronico para ayuda de usuario',
				'webcursos@uai.cl',
				PARAM_TEXT
			)
	);
	$settings->add(
			new admin_setting_configtext(
				'fbk_tutorialsname',
				'Nombre de tutoriales',
				'Nombre de tutoriales de moodle, si no tiene página con tutoriales dejar en blanco',
				'Tutoriales WebC',
				PARAM_TEXT
			)
	);
	$settings->add(
			new admin_setting_configtext(
				'fbk_tutorialurl',
				'URL pagina de tutoriales',
				'Link de tutoriales de moodle, si no tiene página con tutoriales dejar en blanco',
				'http://webcursos.uai.cl/local/tutoriales/',
				PARAM_TEXT
			)
	);
	$settings->add(
			new admin_setting_configtext(
					'fbk_frontimage',
					'URL de imagen',
					'Link de tutoriales de moodle, si no tiene página con tutoriales dejar en blanco',
					'',
					PARAM_TEXT
			)
	);
	
	// Advanced Settings
	$settings->add(
			new admin_setting_heading(
					'fkb_advancedsettings',
					"Configuración Avanzada",
					'Información proporcionada por intefaz Developers Facebook'
			)
	);
    $settings->add(
    		new admin_setting_configtext(
	    		'fbk_appname',
	    		'Nombre de la aplicación',
	    		'Se recomienda utilizar el mismo nombre que posee la aplicación en Facebook',
	    		'Webcursos UAI',
	    		PARAM_TEXT
    		)
    );
	$settings->add(
			new admin_setting_configtext(
				'fbk_appid',
				'Identificador de la aplicación',
				'Identificador único que entrega Facebook al crear la aplicación',
				'',
				PARAM_INT
			)
	);
	$settings->add(
			new admin_setting_configtext(
				'fbk_scrid',
				'Clave secreta de la aplicación',
				'Entregado por Facebook al crear la aplicación, desde la interfaz de facebook es posible obtener el dato',
				'',
				PARAM_RAW
			)
	);
	$settings->add(
			new admin_setting_configtext(
				'fbk_url',
				'URL de la aplicación',
				'Normalmente url del tipo https://apps.facebook.com/APPNAME',
				'https://apps.facebook.com/webcursosuai',
				PARAM_TEXT
			)
	);
	
	// Developers Settings
	$settings->add(
			new admin_setting_heading(
				'fkb_developerssettings', 
				"Configuración de Desarrolladores",
				''
			)
	);
	$settings->add(
			new admin_setting_configtext(
				'fbk_ajax',
				'URL de consultas Ajax',
				'URL de página que recibe consultas ajax por parte de la aplicación',
				'',
				PARAM_TEXT
			)
	);
	$settings->add(
			new admin_setting_configcheckbox(
				'fbk_notifications',
				'Habilitar/Deshabilitar Notificaciones',
				'Controla el envío de notificaciones desde Webcursos a Facebook para usuarios enlazados',
				TRUE,
				PARAM_BOOL
			)
	);
	$settings->add(
			new admin_setting_configcheckbox(
				'fbk_emarking', 
				'Habilitar/Deshabilitar eMarking',
				'Esta acción afecta tanto a notificaciones como el despliegue del módulo en la interfaz de la aplicación',
				TRUE,
				PARAM_BOOL
			)
	);
		
}
