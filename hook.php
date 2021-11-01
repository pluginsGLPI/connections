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

/**
 * @return bool
 * @throws \GlpitestSQLError
 */
function plugin_connections_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/connections/inc/profile.class.php");

   $update = false;

   //TODO: Use "Migration" class instead (available since GLPI v0.80)

   // Go for 1.7.0
   if (!$DB->tableExists('glpi_plugin_connection')
       && !$DB->tableExists('glpi_plugin_connections_connections')) { // Fresh install
      $DB->runFile(GLPI_ROOT . '/plugins/connections/sql/empty-10.0.sql');

      // We're 1.6.0 update to 1.6.4
   } else if ($DB->tableExists('glpi_plugin_connections_connectionratesguaranteed')
              && !$DB->tableExists('glpi_plugin_connectiond_device')) {
      $DB->runFile(GLPI_ROOT . '/plugins/connections/sql/update-1.6.0-to-1.6.4.sql');

   } else if ($DB->tableExists("glpi_plugin_connection")
              && !$DB->FieldExists("glpi_plugin_connection", "recursive")) {
      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-1.3.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-1.4.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-1.5.0.sql");

   } else if ($DB->tableExists("glpi_plugin_connection_profiles")
              && $DB->FieldExists("glpi_plugin_connection_profiles", "interface")) {
      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-1.4.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-1.3.0.sql");

   } else if ($DB->tableExists("glpi_plugin_connection")
              && !$DB->FieldExists("glpi_plugin_connection", "helpdesk_visible")) {
      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-1.3.0.sql");

   } else if ($DB->tableExists("glpi_plugin_connections_connectionrates") &&
              !$DB->FieldExists("glpi_plugin_connections_connectionrates", "is_recursive")) {
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-9.4.0.sql");

   } else if (!$DB->FieldExists("glpi_plugin_connections_connections", "locations_id")) {
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-9.5.0.sql");
   } else if (!$DB->FieldExists("glpi_plugin_connections_connections", "users_id_tech")) {
      $DB->runFile(GLPI_ROOT . "/plugins/connections/sql/update-10.0.sql");
   }


   if ($update) {
      $query_  = "SELECT * FROM `glpi_plugin_connections_profiles` ";
      $result_ = $DB->query($query_);

      if ($DB->numrows($result_) > 0) {
         while ($data = $DB->fetchArray($result_)) {
            $query = "UPDATE `glpi_plugin_connections_profiles`
                      SET `profiles_id` = '" . $data["id"] . "'
                      WHERE `id` = '" . $data["id"] . "';";
            $DB->query($query);
         }
      }

      $DB->query("ALTER TABLE `glpi_plugin_connections_profiles` DROP `name`;");

      Plugin::migrateItemType(
         [4400 => 'PluginConnectionsConnection'],
         [
            "glpi_bookmarks",
            "glpi_bookmarks_users",
            "glpi_displaypreferences",
            "glpi_documents_items",
            "glpi_infocoms",
            "glpi_logs",
            "glpi_tickets",
         ],
         ["glpi_plugin_connections_connections_items"]
      );

      Plugin::migrateItemType(
         [
            1200 => "PluginAppliancesAppliance",
            1300 => "PluginWebapplicationsWebapplication"
         ],
         ["glpi_plugin_connections_connections_items"]
      );
   }

   PluginConnectionsProfile::initProfile();
   PluginConnectionsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("9.2");
   $migration->dropTable('glpi_plugin_connections_profiles');
   return true;
}

/**
 * @return bool
 * @throws \GlpitestSQLError
 */
function plugin_connections_uninstall() {
   global $DB;

   $tables = [
      'glpi_plugin_connections_configs',
      "glpi_plugin_connections_connections",
      "glpi_plugin_connections_connections_items",
      "glpi_plugin_connections_connectiontypes",
      "glpi_plugin_connections_connectionrates",
      "glpi_plugin_connections_guaranteedconnectionrates",
      "glpi_plugin_connections_profiles",
      "glpi_plugin_connections_notificationstates",
   ];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   //old versions
   $tables = [
      "glpi_plugin_connection",
      "glpi_plugin_connection_device",
      'glpi_plugin_connections_connectionratesguaranteed',
      "glpi_dropdown_plugin_connections_type",
      "glpi_plugin_connection_profiles",
      "glpi_plugin_connection_mailing",
   ];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = [
      "glpi_displaypreferences",
      "glpi_documents_items",
      "glpi_savedsearches",
      "glpi_logs",
      "glpi_items_tickets",
      "glpi_impactitems"
   ];

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginConnectionsConnection';");
   }

   $DB->query("DELETE
                  FROM `glpi_impactrelations`
                  WHERE `itemtype_source` IN ('PluginConnectionsConnection')
                    OR `itemtype_impacted` IN ('PluginConnectionsConnection')");

   if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(['itemtype' => 'PluginConnectionsConnection']);
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginConnectionsProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginConnectionsMenu::removeRightsFromSession();
   PluginConnectionsProfile::removeRightsFromSession();

   return true;
}

function plugin_connections_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['connections'] = [];

   foreach (PluginConnectionsConnection_Item::getClasses(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['connections'][$type]
         = ['PluginConnectionsConnection_Item', 'cleanForItem'];

      CommonGLPI::registerStandardTab($type, 'PluginConnectionsConnection_Item');
   }
}

/**
 * @param $types
 *
 * @return mixed
 */
function plugin_connections_AssignToTicket($types) {

   if (isset($_SESSION['glpiactiveprofile']['helpdesk_item_type'])
       && in_array('PluginConnectionsConnection', $_SESSION['glpiactiveprofile']['helpdesk_item_type'])) {
      $types['PluginConnectionsConnection'] = __('Connections', 'connections');
   }
   return $types;
}


// Define dropdown relations
/**
 * @return array
 */
function plugin_connections_getDatabaseRelations() {
   $plugin = new Plugin();

   if ($plugin->isActivated("connections")) {
      return [
         "glpi_plugin_connections_connectiontypes"           => ["glpi_plugin_connections_connections" => "plugin_connections_connectiontypes_id"],
         "glpi_plugin_connections_connectionrates"           => ["glpi_plugin_connections_connections" => "plugin_connections_connectionrates_id"],
         "glpi_plugin_connections_guaranteedconnectionrates" => ["glpi_plugin_connections_connections" => "plugin_connections_guaranteedconnectionrates_id"],
         "glpi_users"                                        => ["glpi_plugin_connections_connections" => "users_id_tech"],
         "glpi_groups"                                       => ["glpi_plugin_connections_connections" => "groups_id_tech"],
         "glpi_suppliers"                                    => ["glpi_plugin_connections_connections" => "suppliers_id"],
         "glpi_plugin_connections_connections"               => ["glpi_plugin_connections_connections_items" => "plugin_connections_connections_id"],
         "glpi_entities"                                     => [
            "glpi_plugin_connections_connections"     => "entities_id",
            "glpi_plugin_connections_connectiontypes" => "entities_id"
         ]
      ];
   }

   return [];
}

// Define Dropdown tables to be manage in GLPI :
/**
 * @return array
 */
function plugin_connections_getDropdown() {
   $plugin = new Plugin();

   if ($plugin->isActivated("connections")) {
      return [
         'PluginConnectionsConnectionType'           => __('Type of Connections', 'connections'),
         'PluginConnectionsConnectionRate'           => __('Rates', 'connections'),
         'PluginConnectionsGuaranteedConnectionRate' => __('Guaranteed Rates', 'connections'),
      ];
   }

   return [];
}

/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_connections_getAddSearchOptions($itemtype) {
   $sopt  = [];
   $title = __('Connections', 'connections');

   if (in_array($itemtype, PluginConnectionsConnection_Item::getClasses(true))) {
      if (Session::haveRight("plugin_connections_connection", READ)) {
         $sopt[4410]['table']         = 'glpi_plugin_connections_connections';
         $sopt[4410]['field']         = 'name';
         $sopt[4410]['linkfield']     = '';
         $sopt[4410]['name']          = $title . " - " . __('Associated element');
         $sopt[4410]['forcegroupby']  = '1';
         $sopt[4410]['datatype']      = 'itemlink';
         $sopt[4410]['itemlink_type'] = 'PluginConnectionsConnection';

         $sopt[4411]['table']        = 'glpi_plugin_connections_connectiontypes';
         $sopt[4411]['field']        = 'name';
         $sopt[4411]['linkfield']    = '';
         $sopt[4411]['name']         = $title . " - " . __('Type of Connections', 'connections');
         $sopt[4411]['forcegroupby'] = '1';

         $sopt[4412]['table']        = 'glpi_plugin_connections_connectionrates';
         $sopt[4412]['field']        = 'name';
         $sopt[4412]['linkfield']    = '';
         $sopt[4412]['name']         = $title . " - " . __('Rates', 'connections');
         $sopt[4412]['forcegroupby'] = '1';

         $sopt[4413]['table']        = 'glpi_plugin_connections_guaranteedconnectionrates';
         $sopt[4413]['field']        = 'name';
         $sopt[4413]['linkfield']    = '';
         $sopt[4413]['name']         = $title . " - " . __('Guaranteed Rates', 'connections');
         $sopt[4413]['forcegroupby'] = '1';

      }
   }
   return $sopt;
}

/**
 * @param $type
 * @param $ref_table
 * @param $new_table
 * @param $linkfield
 * @param $already_link_tables
 *
 * @return \Left|string
 */
function plugin_connections_addLeftJoin($type, $ref_table, $new_table, $linkfield, &$already_link_tables) {
   switch ($new_table) {
      case "glpi_plugin_connections_connections_items" :
         return " LEFT JOIN `$new_table` ON (`$ref_table`.`id` = `$new_table`.`plugin_connections_connections_id`) ";
         break;

      case "glpi_plugin_connections_connections" :
         $out = " LEFT JOIN `glpi_plugin_connections_connections_items` ON (`$ref_table`.`id` = `glpi_plugin_connections_connections_items`.`items_id` AND `glpi_plugin_connections_connections_items`.`itemtype` = '$type') ";
         $out .= " LEFT JOIN `glpi_plugin_connections_connections` ON (`glpi_plugin_connections_connections`.`id` = `glpi_plugin_connections_connections_items`.`plugin_connections_connections_id`) ";
         return $out;
         break;

      case "glpi_plugin_connections_connectiontypes" :
         $out = Search::addLeftJoin(
            $type,
            $ref_table,
            $already_link_tables,
            "glpi_plugin_connections_connections",
            $linkfield
         );
         $out .= " LEFT JOIN `glpi_plugin_connections_connectiontypes` ON (`glpi_plugin_connections_connectiontypes`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_connectiontypes_id`) ";
         return $out;

      case "glpi_plugin_connections_connectionrates" :
         $out = Search::addLeftJoin(
            $type,
            $ref_table,
            $already_link_tables,
            "glpi_plugin_connections_connections",
            $linkfield
         );
         $out .= " LEFT JOIN `glpi_plugin_connections_connectionrates` ON (`glpi_plugin_connections_connectionrates`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_connectionrates_id`) ";
         return $out;

      case "glpi_plugin_connections_guaranteedconnectionrates" :
         $out = Search::addLeftJoin(
            $type,
            $ref_table,
            $already_link_tables,
            "glpi_plugin_connections_connections",
            $linkfield
         );
         $out .= " LEFT JOIN `glpi_plugin_connections_guaranteedconnectionrates` ON (`glpi_plugin_connections_guaranteedconnectionrates`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_guaranteedconnectionrates_id`) ";
         return $out;
   }

   return "";
}

/**
 * @param $type
 *
 * @return bool
 */
function plugin_connections_forceGroupBy($type) {
   return true;
}

/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return string
 * @throws \GlpitestSQLError
 */
function plugin_connections_giveItem($type, $ID, $data, $num) {
   global $DB;

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table . '.' . $field) {
      case "glpi_plugin_connections_connections_items.items_id" :
         $query_device  = "SELECT DISTINCT `itemtype`
                          FROM `glpi_plugin_connections_connections_items`
                          WHERE `plugin_connections_connections_id` = '" . $data['id'] . "'
                          ORDER BY `itemtype`";
         $result_device = $DB->query($query_device);
         $number_device = $DB->numrows($result_device);

         $out         = '';
         $connections = $data['id'];

         if ($number_device > 0) {
            for ($i = 0; $i < $number_device; $i++) {
               $column   = "name";
               $itemtype = $DB->result($result_device, $i, "itemtype");

               if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
               if ($item->canView()) {
                  $table_item       = getTableForItemType($itemtype);
                  $entitiesRestrict = getEntitiesRestrictRequest(
                     " AND ",
                     $table_item,
                     '',
                     '',
                     $item->maybeRecursive()
                  );
                  $mayBeTemplated   = ($item->maybeTemplate())
                     ? " AND `$table_item`.`is_template` = '0'"
                     : '';

                  $query = "SELECT `$table_item`.*, `glpi_entities`.`ID` AS entity
                            FROM `glpi_plugin_connections_connections_items` ci, `$table_item`
                            LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `$table_item`.`entities_id`)
                            WHERE `$table_item`.`id` = ci.`items_id`
                            AND ci.`itemtype` = '$itemtype'
                            AND ci.`plugin_connections_connections_id` = '$connections'
                            $mayBeTemplated
                            ORDER BY `glpi_entities`.`completename`, `$table_item`.`$column`";
                  if ($result_linked = $DB->query($query))
                     if ($DB->numrows($result_linked)) {
                        $item = new $itemtype();

                        while ($data = $DB->fetchAssoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item->getTypeName() . " - " . $item->getLink() . "<br>";
                           }
                        }
                     } else {
                        $out .= ' ';
                     }
               } else {
                  $out .= ' ';
               }
            }
         }
         return $out;
         break;
   }
   return "";
}


function plugin_datainjection_populate_connections() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginConnectionsConnectionInjection'] = 'connections';
}
