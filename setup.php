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
   global $PLUGIN_HOOKS, $CFG_GLPI, $LANG;

   $PLUGIN_HOOKS['csrf_compliant']['connections']   = true;
   $PLUGIN_HOOKS['change_profile']['connections']   = array('PluginConnectionsProfile', 'changeProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['connections'] = true;

   if (class_exists('PluginConnectionsConnection_Item')) { // only if plugin activated
      $PLUGIN_HOOKS['pre_item_purge']['connections']                = array('Profile' => array('PluginConnectionsProfile', 'purgeProfiles'));
      $PLUGIN_HOOKS['plugin_datainjection_populate']['connections'] = 'plugin_datainjection_populate_connections';
      $PLUGIN_HOOKS['item_purge']['connections']                    = array();
      foreach (PluginConnectionsConnection_Item::getClasses(true) as $type) {
         $PLUGIN_HOOKS['item_purge']['connections'][$type] = 'plugin_item_purge_connections';
      }
   }

   Plugin::registerClass('PluginConnectionsConnection', array(
      'linkuser_types'              => true,
      'linkgroup_types'             => true,
      'document_types'              => true,
      'contract_types'              => true,
      'ticket_types'                => true,
      'helpdesk_visible_types'      => true,
      'notificationtemplates_types' => true,
   ));
   Plugin::registerClass('PluginConnectionsProfile', array('addtabon' => 'Profile'));
   Plugin::registerClass('PluginConnectionsConnection_Item', array('addtabon' => 'NetworkEquipment'));

   if (Session::getLoginUserID()) {

      if (Session::haveRight("connections", READ)) {
         $PLUGIN_HOOKS["menu_toadd"]['connections'] = array('assets'  => 'PluginConnectionsMenu');
      }

      // Add specific files to add to the header : javascript or css
      //$PLUGIN_HOOKS['add_javascript']['example']="example.js";
      $PLUGIN_HOOKS['add_css']['connections'] = "connections.css";

      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['migratetypes']['connections'] = 'plugin_datainjection_migratetypes_connections';

   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_connections() {
   global $LANG;

   return array (
      'name'           => $LANG['plugin_connections']['title'][1],
      'version'        => '0.90-1.7.0',
      'license'        => 'GPLv2+',
      'oldname'        => 'connection',
      'author'         =>'Xavier Caillaud, Jean Marc GRISARD',
      'homepage'       =>'https://forge.indepnet.net/projects/connections',
      'minGlpiVersion' => '0.85',
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_connections_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.85', 'lt')) {
      echo 'This plugin requires GLPI >= 0.85';
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_connections_check_config() {
   return true;
}

function plugin_connections_haveRight($module, $right) {
	$matches = array(
      ""  => array("", "r", "w"), // ne doit pas arriver normalement
      "r" => array("r", "w"),
      "w" => array("w"),
      "1" => array("1"),
      "0" => array("0", "1"), // ne doit pas arriver non plus
   );
	if (isset($_SESSION["glpi_plugin_connections_profile"][$module]) && in_array($_SESSION["glpi_plugin_connections_profile"][$module],$matches[$right])){
		return true;
   }
	return false;
}

function plugin_datainjection_migratetypes_connections($types) {
   $types[4400] = 'PluginConnectionsConnection';
   return $types;
}
