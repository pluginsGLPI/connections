<?php
/*
 * @version $Id: setup.php,v 1.2 2006/04/02 14:45:27 moyo Exp $
 ---------------------------------------------------------------------- 
 GLPI - Gestionnaire Libre de Parc Informatique 
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------

include_once ("inc/plugin_connections.functions_auth.php");
include_once ("inc/plugin_connections.profiles.classes.php");

// Init the hooks of the plugins -Needed
function plugin_init_connections() {

	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;

	$PLUGIN_HOOKS['change_profile']['connections'] = 'plugin_connections_changeprofile';
	//$PLUGIN_HOOKS['assign_to_ticket']['connections'] = true;

	// Params : plugin name - string type - number - class - table - form page
	registerPluginType('connections', 'PLUGIN_CONNECTIONS_TYPE', 2700, array(
		'classname'  => 'plugin_connections',
		'tablename'  => 'glpi_plugin_connections',
		'formpage'   => 'front/plugin_connections.form.php',
		'searchpage' => 'index.php',
		'typename'   => $LANG['plugin_connections'][1],
		'deleted_tables' => true,
		'specif_entities_tables' => true,
		'recursive_type' => true
		));

	if (isset($_SESSION["glpiID"]))
	{

		//add connections to entities_tables (for 'by entities' using)
		array_push($CFG_GLPI["deleted_tables"],"glpi_plugin_connections");
		array_push($CFG_GLPI["specif_entities_tables"],"glpi_plugin_connections");
		array_push($CFG_GLPI["specif_entities_tables"],"glpi_dropdown_plugin_connections_type");
		
		if (isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]==1){		
			$_SESSION["glpi_plugin_environment_connections"]=1;
			
			if(plugin_connections_haveRight("connections","r"))
			{
				$PLUGIN_HOOKS['menu_entry']['connections'] = false;
				$PLUGIN_HOOKS['submenu_entry']['environment']['search']['connections'] = 'front/plugin_environment.form.php?plugin=connections&search=1';
				$PLUGIN_HOOKS['headings']['connections'] = 'plugin_get_headings_connections';
				$PLUGIN_HOOKS['headings_action']['connections'] = 'plugin_headings_actions_connections';				
			}
			if (plugin_connections_haveRight("connections","w"))
			{
				$PLUGIN_HOOKS['submenu_entry']['environment']['add']['connections'] = 'front/plugin_environment.form.php?plugin=connections&add=1';
				$PLUGIN_HOOKS['pre_item_delete']['connections'] = 'plugin_pre_item_delete_connections';
				$PLUGIN_HOOKS['item_purge']['connections'] = 'plugin_item_purge_connections';
				$PLUGIN_HOOKS['use_massive_action']['connections']=1;				
			}		
		}else{
			// Display a menu entry ?		
			if(plugin_connections_haveRight("connections","r"))
			{
				$PLUGIN_HOOKS['menu_entry']['connections'] = true;
				$PLUGIN_HOOKS['submenu_entry']['connections']['search'] = 'index.php';
				$PLUGIN_HOOKS['headings']['connections'] = 'plugin_get_headings_connections';
				$PLUGIN_HOOKS['headings_action']['connections'] = 'plugin_headings_actions_connections';
			}
			
			if (plugin_connections_haveRight("connections","w"))
			{
				$PLUGIN_HOOKS['submenu_entry']['connections']['add'] = 'front/plugin_connections.form.php?new=1';
				$PLUGIN_HOOKS['pre_item_delete']['connections'] = 'plugin_pre_item_delete_connections';
				$PLUGIN_HOOKS['item_purge']['connections'] = 'plugin_item_purge_connections';
				$PLUGIN_HOOKS['use_massive_action']['connections']=1;
			}
		}

		// Add specific files to add to the header : javascript or css
		//$PLUGIN_HOOKS['add_javascript']['example']="example.js";
		$PLUGIN_HOOKS['add_css']['connections']="connections.css";
									
		// Import from Data_Injection plugin
		$PLUGIN_HOOKS['data_injection']['connections'] = "plugin_connections_data_injection_variables";
		
	}
		
}

// Get the name and the version of the plugin - Needed
function plugin_version_connections(){
	global $LANG;
	
	return array (
		'name' => $LANG['plugin_connections'][1],
		'version' => '1.5.0',
		'author'=>'Jean Marc GRISARD, Damien BARON, Xavier CAILLAUD',
		'homepage'=>'http://glpi-project.org/wiki/doku.php?id='.substr($_SESSION["glpilanguage"],0,2).':plugins:pluginslist',
		'minGlpiVersion' => '0.72',// For compatibility / no install in version < 0.72
	);
	
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_connections_check_prerequisites(){
	if (GLPI_VERSION>=0.72){
		return true;
	} else {
		echo "GLPI version not compatible need 0.72";
	}
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_connections_check_config()
{
	return true;
}

// Define rights for the plugin types
function plugin_connections_haveTypeRight($type,$right)
{
	switch ($type){
		case PLUGIN_CONNECTIONS_TYPE :
			return plugin_connections_haveRight("connections",$right);
			break;
	}
}
?>
