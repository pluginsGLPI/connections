<?php
/*
 * @version $Id: HEADER 1 2010-02-24 00:12 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Author of file: CAILLAUD Xavier, GRISARD Jean Marc
// Purpose of file: plugin connections v1.6.5 - GLPI 0.85 / 0.90
// ----------------------------------------------------------------------
 */
 
// Init the hooks of the plugins -Needed
function plugin_init_connections() {
	global $PLUGIN_HOOKS;

	$PLUGIN_HOOKS['csrf_compliant']['connections'] = true;
	
	$PLUGIN_HOOKS['change_profile']['connections'] = array('PluginConnectionsProfile', 'changeProfile');
	
	//Une fois correctement défini $PLUGIN_HOOKS['assign_to_ticket'] et les fonctions associées, 
	// l'association des différents types possibles ce fait directement dans les profils du coeur tout comme les autres objets.
	$PLUGIN_HOOKS['assign_to_ticket']['connections'] = true; //Note : Aussi dans le plugin account en 0.85
	
   if (class_exists('PluginConnectionsConnection_Item')) { // only if plugin activated
      $PLUGIN_HOOKS['pre_item_purge']['connections'] = array('Profile'=>array('PluginConnectionsProfile', 'purgeProfiles'));
      $PLUGIN_HOOKS['plugin_datainjection_populate']['connections'] = 'plugin_datainjection_populate_connections';
      $PLUGIN_HOOKS['item_purge']['connections'] = array();

      //Note : Ce bloc était dans aussi postinit (mais non appelé)
      foreach (PluginConnectionsConnection_Item::getClasses(true) as $type) {
         $PLUGIN_HOOKS['item_purge']['connections'][$type] = 'plugin_item_purge_connections';
      }
   }
   
	Plugin::registerClass('PluginConnectionsConnection', array('linkuser_types' => true,
																				'linkgroup_types' => true,
																				'document_types' => true,
																				'contract_types' => true,
																				'ticket_types'   => true,
																				'helpdesk_visible_types' => true,
																				'notificationtemplates_types' => true));
	
	if (Session::getLoginUserID()) {
		// Assistance -> Association -> Matériels associables à un ticket
		// Nom du champs : helpdesk_item_type[]
		if (! isset($_SESSION["glpiactiveprofile"]["helpdesk_item_type"]['PluginConnectionsConnection'])) {
			$_SESSION["glpiactiveprofile"]["helpdesk_item_type"][] = 'PluginConnectionsConnection';
		}

		Plugin::registerClass('PluginConnectionsProfile', array('addtabon' => 'Profile'));
		Plugin::registerClass('PluginConnectionsConnection_Item', array('addtabon' => 'NetworkEquipment'));
		
		// Menus
		$PLUGIN_HOOKS['menu_toadd']['connections'] = array('assets' => 'PluginConnectionsMenu');

		// Note : environment
		if (isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]) {
			
			$_SESSION["glpi_plugin_environment_connections"] = 1;
			
			// Display a menu entry ?
			if (plugin_connections_haveRight("connections", "r")) {
				$PLUGIN_HOOKS['menu_entry']['connections'] = false;

				//TODO : submenu_entry -> https://forge.glpi-project.org/projects/plugins/wiki/Fr_Plugin084to085#hook-add-et-search
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['connections']['title'] = __('Connections', 'connections');
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['connections']['page'] = '/plugins/connections/front/connection.php';
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['connections']['links']['search'] = '/plugins/connections/front/connection.php';
			}
			
			if (plugin_connections_haveRight("connections", "w")) {
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['connections']['links']['add'] = '/plugins/connections/front/connection.form.php';
			}		
		} else {
		
			// Display a menu entry ?
			if (plugin_connections_haveRight("connections", "r")) {
				$PLUGIN_HOOKS['menu_entry']['connections'] = 'front/connection.php';
				$PLUGIN_HOOKS['submenu_entry']['connections']['search'] = 'front/connection.php';
			}
			
			if (plugin_connections_haveRight("connections", "w")) {
				$PLUGIN_HOOKS['submenu_entry']['connections']['add'] = 'front/connection.form.php?new=1';
			}
		}

		//if (plugin_connections_haveRight("connections", "w")) { //TODO : 
			$PLUGIN_HOOKS['use_massive_action']['connections'] = 1; //Note : existe bien dans le coeur GLPI
		//}
		
		// Import from Data_Injection plugin
		$PLUGIN_HOOKS['migratetypes']['connections'] = 'plugin_datainjection_migratetypes_connections';

	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_connections() {
	return array('name' 	=> __('Connections', 'connections'),
		'version'			=> '0.90-1.6.5',
		'license'			=> 'GPLv2+',
		'author'				=> 'Xavier Caillaud, Jean Marc GRISARD',
		'homepage'			=> 'https://github.com/pluginsGLPI/connections',
		'minGlpiVersion' 	=> '0.85');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_connections_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85', 'lt')) {
      echo 'This plugin requires GLPI >= 0.85';
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_connections_check_config() {
	return true;
}

function plugin_connections_haveRight($module,$right) {
	return true; //DEBUG !!!!!!!
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	return (isset($_SESSION["glpi_plugin_connections_profile"][$module])
		&& in_array($_SESSION["glpi_plugin_connections_profile"][$module],$matches[$right]));
}

function plugin_datainjection_migratetypes_connections($types) {
	var_dump("plugin_datainjection_migratetypes_connections()"); //DEBUG
   $types[4400] = 'PluginConnectionsConnection';

   return $types;
}
