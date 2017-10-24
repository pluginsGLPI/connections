<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
 connections plugin for GLPI
 Copyright (C) 2015-2016 by the connections Development Team.

 https://github.com/pluginsGLPI/connections
-------------------------------------------------------------------------

LICENSE

This file is part of connections.

 connections is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

 connections is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with connections. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_connections() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['connections']   = true;
   $PLUGIN_HOOKS['change_profile']['connections']   = array('PluginConnectionsProfile', 'initProfile');
   $PLUGIN_HOOKS['assign_to_ticket']['connections'] = true;

   $plugin = new Plugin();
   if ($plugin->isActivated("connections")) {

      Plugin::registerClass('PluginConnectionsConnection', array(
         'linkuser_types'              => true,
         'linkgroup_types'             => true,
         'document_types'              => true,
         'contract_types'              => true,
         'ticket_types'                => true,
         'helpdesk_visible_types'      => true,
         'notificationtemplates_types' => true,
      ));

      Plugin::registerClass('PluginConnectionsProfile', array(
         'addtabon' => 'Profile'
      ));

      Plugin::registerClass('PluginConnectionsConnection_Item', array(
         'addtabon' => array('NetworkEquipment','Supplier')
      ));

      if (Session::haveRight("plugin_connections_connection", READ)) {
         $PLUGIN_HOOKS["menu_toadd"]['connections'] = array('assets' => 'PluginConnectionsMenu');
      }

      $PLUGIN_HOOKS['add_css']['connections']                       = "connections.css";
      $PLUGIN_HOOKS['migratetypes']['connections']                  = 'plugin_datainjection_migratetypes_connections';
      $PLUGIN_HOOKS['plugin_datainjection_populate']['connections'] = 'plugin_datainjection_populate_connections';
   }

}

// Get the name and the version of the plugin - Needed
function plugin_version_connections() {

   return array(
      'name'           => __('Connections', 'connections'),
      'version'        => '9.2',
      'license'        => 'GPLv2+',
      'oldname'        => 'connection',
      'author'         => 'Xavier Caillaud, Jean Marc GRISARD, TECLIB\'',
      'homepage'       => 'https://github.com/pluginsGLPI/connections',
      'minGlpiVersion' => '9.2',
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_connections_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.2', 'lt')) {
      echo 'This plugin requires GLPI >= 9.2';
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_connections_check_config() {
   return true;
}

function plugin_datainjection_migratetypes_connections($types) {
   $types[4400] = 'PluginConnectionsConnection';
   return $types;
}
