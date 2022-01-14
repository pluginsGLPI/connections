<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
 connections plugin for GLPI
 Copyright (C) 2015-2022 by the connections Development Team.

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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginConnectionsConnection_Item
 */
class PluginConnectionsConnection_Item extends CommonDBRelation {

   static public $itemtype_1    = "PluginConnectionsConnection";
   static public $items_id_1    = 'plugin_connections_connections_id';
   static public $take_entity_1 = false;

   static public $itemtype_2    = 'itemtype';
   static public $items_id_2    = 'items_id';
   static public $take_entity_2 = true;

   static $rightname = 'plugin_connections_connection';


   /**
    * @param \CommonDBTM $item
    */
   public static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         ['itemtype' => $item->getType(),
          'items_id' => $item->getField('id')]
      );
   }

   /**
    * @param bool $all
    *
    * @return array
    */
   public static function getClasses($all = false) {

      static $types = [
         'NetworkEquipment',
         'Appliance',
         'Computer',
         'Certificate'
      ];

      if ($all) {
         return $types;
      }

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }
         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }


   /**
    * @param $connections_id
    * @param $items_id
    * @param $itemtype
    *
    * @return bool
    * @throws \GlpitestSQLError
    */
   public function getFromDBbyConnectionsAndItem($connections_id, $items_id, $itemtype) {
      global $DB;

      $query = "SELECT * FROM `" . $this->getTable() . "`
                WHERE `plugin_connections_connections_id` = '" . $connections_id . "'
                AND `itemtype` = '" . $itemtype . "'
                AND `items_id` = '" . $items_id . "'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   /**
    * @param $connections_id
    * @param $items_id
    * @param $itemtype
    */
   public function addItem($connections_id, $items_id, $itemtype) {
      $input = [
         'plugin_connections_connections_id' => $connections_id,
         'items_id'                          => $items_id,
         'itemtype'                          => $itemtype,
      ];

      if ($this->add($input)) {

         // History Log into PluginConnectionsConnection
         $item = new $itemtype();
         $item->getFromDB($items_id);

         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = $item->getNameID(['forceid' => true]);
         Log::history($connections_id, 'PluginConnectionsConnection', $changes, $item->getType(), 15);

         // History Log into Item
         $item = new PluginConnectionsConnection();
         $item->getFromDB($connections_id);

         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = $item->getNameID(['forceid' => true]);
         Log::history($items_id, $item->getType(), $changes, 'PluginConnectionsConnection', 15);
      }
   }

   /**
    * @param $input
    */
   public function deleteItem($input) {

      $this->check($input['id'], UPDATE);

      $connections_id = $this->getField('plugin_connections_connections_id');
      $itemtype       = $this->getField('itemtype');
      $items_id       = $this->getField('items_id');
      if ($this->delete($input)) {

         // History Log into PluginConnectionsConnection
         $item = new $itemtype();
         $item->getFromDB($items_id);

         $changes[0] = 0;
         $changes[1] = $item->getNameID(['forceid' => true]);
         $changes[2] = '';
         Log::history($connections_id, 'PluginConnectionsConnection', $changes, $item->getType(), 16);

         // History Log into item
         $item = new PluginConnectionsConnection();
         $item->getFromDB($connections_id);

         $changes[0] = 0;
         $changes[1] = $item->getNameID(['forceid' => true]);
         $changes[2] = '';
         Log::history($items_id, $item->getType(), $changes, 'PluginConnectionsConnection', 16);
      }
   }

   /**
    * @param $connections_id
    * @param $items_id
    * @param $itemtype
    */
   public function deleteItemByConnectionsAndItem($connections_id, $items_id, $itemtype) {
      if ($this->getFromDBbyConnectionsAndItem($connections_id, $items_id, $itemtype)) {
         $this->delete([
                          'id' => $this->fields["id"]
                       ]);
      }
   }

   /**
    * @param \PluginConnectionsConnection $connection
    *
    * @return bool
    * @throws \GlpitestSQLError
    */
   public function showItemFromPlugin(PluginConnectionsConnection $connection) {
      global $DB;

      $instID = $connection->fields['id'];
      if (!$connection->can($instID, READ)) return false;

      $rand    = mt_rand();
      $dbu     = new DbUtils();
      $canedit = $connection->can($instID, UPDATE);

      $query = "SELECT DISTINCT `itemtype`
             FROM `glpi_plugin_connections_connections_items`
             WHERE `plugin_connections_connections_id` = '$instID'
             ORDER BY `itemtype`
             LIMIT " . count(self::getClasses(true));

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' name='connections_form$rand' id='connections_form$rand'
         action='" . Toolbox::getItemTypeFormURL("PluginConnectionsConnection") . "'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='" . ($canedit ? (5 + $colsup) : (4 + $colsup)) . "'>" .
              __('Add an item') . "</th></tr>";

         echo "<tr class='tab_bg_1'><td colspan='" . (3 + $colsup) . "' class='center'>";
         echo Html::hidden('plugin_connections_connections_id', ['value' => $instID]);
         Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'items_id',
                                                'itemtypes'     => self::getClasses(true),
                                                'entity_restrict'
                                                                => ($connection->fields['is_recursive']
                                                   ? getSonsOf('glpi_entities',
                                                               $connection->fields['entities_id'])
                                                   : $connection->fields['entities_id']),
                                                'checkright'
                                                                => true,
                                               ]);
         echo "</td>";
         echo "<td colspan='2' class='tab_bg_2'>";
         echo Html::submit(_sx('button', 'Add'), ['name' => 'additem', 'class' => 'btn btn-primary']);
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = [];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
      }

      echo "<th>" . __('Type') . "</th>";
      echo "<th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>" . __('Entity') . "</th>";
      echo "<th>" . __('Serial number') . "</th>";
      echo "<th>" . __('Inventory number') . "</th>";
      echo "</tr>";

      for ($i = 0; $i < $number; $i++) {
         $itemType = $DB->result($result, $i, "itemtype");

         if (!($item = $dbu->getItemForItemtype($itemType))) {
            continue;
         }

         if ($item->canView()) {
            $column    = "name";
            $itemTable = $dbu->getTableForItemType($itemType);

            $query = "SELECT `" . $itemTable . "`.*,
                             `glpi_plugin_connections_connections_items`.`id` AS items_id,
                             `glpi_entities`.`id` AS entity "
                     . " FROM `glpi_plugin_connections_connections_items`, `" . $itemTable
                     . "` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `" . $itemTable . "`.`entities_id`) "
                     . " WHERE `" . $itemTable . "`.`id` = `glpi_plugin_connections_connections_items`.`items_id`
                AND `glpi_plugin_connections_connections_items`.`itemtype` = '$itemType'
                AND `glpi_plugin_connections_connections_items`.`plugin_connections_connections_id` = '$instID' "
                     . $dbu->getEntitiesRestrictRequest(" AND ", $itemTable, '', '', $item->maybeRecursive());

            if ($item->maybeTemplate()) {
               $query .= " AND `" . $itemTable . "`.`is_template` = '0'";
            }
            $query .= " ORDER BY `glpi_entities`.`completename`, `" . $itemTable . "`.`$column`";

            if ($result_linked = $DB->query($query)) {
               if ($DB->numrows($result_linked)) {

                  Session::initNavigateListItems($itemType, PluginConnectionsConnection::getTypeName(2) . " = " . $connection->fields['name']);

                  while ($data = $DB->fetchAssoc($result_linked)) {

                     $item->getFromDB($data["id"]);

                     Session::addToNavigateListItems($itemType, $data["id"]);

                     $ID = "";

                     if ($_SESSION["glpiis_ids_visible"] || empty($data["name"]))
                        $ID = " (" . $data["id"] . ")";

                     $link = Toolbox::getItemTypeFormURL($itemType);
                     $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">"
                             . $data["name"] . "$ID</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["items_id"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>" . $item::getTypeName(1) . "</td>";

                     echo "<td class='center' " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
                          ">" . $name . "</td>";

                     if (Session::isMultiEntitiesMode())
                        echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entity']) . "</td>";

                     echo "<td class='center'>" . (isset($data["serial"]) ? "" . $data["serial"] . "" : "-") . "</td>";
                     echo "<td class='center'>" . (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";

                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table>";

      if ($canedit && $number) {
         $paramsma['ontop'] = false;
         Html::showMassiveActions($paramsma);
         Html::closeForm();
      }
      echo "</div>";
   }

   //from items

   /**
    * @param        $itemtype
    * @param        $ID
    * @param string $withtemplate
    *
    * @throws \GlpitestSQLError
    */
   public function showPluginFromItems($itemtype, $ID, $withtemplate = '') {
      global $DB;

      $item                        = new $itemtype();
      $canedit                     = $item->can($ID, UPDATE);
      $table                       = $this->getTable();
      $rand                        = mt_rand();
      $dbu                         = new DbUtils();
      $PluginConnectionsConnection = new PluginConnectionsConnection();
      $entitiesRestrict            = $dbu->getEntitiesRestrictRequest(
         " AND ",
         "glpi_plugin_connections_connections",
         '',
         '',
         $PluginConnectionsConnection->maybeRecursive()
      );

      $query  = "SELECT  t.`plugin_connections_connections_id`
                FROM `$table` t
                WHERE t.`items_id` = '$ID'
                AND t.`itemtype` = '$itemtype'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $used   = [];
      if ($number) {
         while ($data = $DB->fetchArray($result)) {
            $used['PluginConnectionsConnection'][] = $data['plugin_connections_connections_id'];
         }
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='connection_form$rand' id='connection_form$rand' method='post'
                action='" . Toolbox::getItemTypeFormURL("PluginConnectionsConnection") . "'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>" . __('Add an item') . "</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";
         Dropdown::showSelectItemFromItemtypes(['itemtypes'
                                                       => ['PluginConnectionsConnection'],
                                                'entity_restrict'
                                                       => ($item->fields['is_recursive']
                                                   ? getSonsOf('glpi_entities',
                                                               $item->fields['entities_id'])
                                                   : $item->fields['entities_id']),
                                                'checkright'
                                                       => true,
                                                'items_id_name'
                                                       => 'plugin_connections_connections_id',
                                                'used' => $used]);
         echo "</td><td class='center'>";
         echo Html::submit(_sx('button', 'Add'), ['name' => 'add', 'class' => 'btn btn-primary']);
         echo Html::hidden('items_id', ['value' => $ID]);
         echo Html::hidden('additem', ['value' => true]);
         echo Html::hidden('itemtype', ['value' => $item->getType()]);
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      $query  = "SELECT  t.`id` AS IDD, `glpi_plugin_connections_connections`.*
                FROM `$table` t, `glpi_plugin_connections_connections`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_connections_connections`.`entities_id`)
                WHERE t.`items_id` = '$ID'
                AND t.`itemtype` = '$itemtype'
                AND t.`plugin_connections_connections_id` = `glpi_plugin_connections_connections`.`id`
                $entitiesRestrict
                ORDER BY `glpi_plugin_connections_connections`.`name` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = ['container' => 'mass' . __CLASS__ . $rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canedit && $number) {
         $header_top    .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         $header_top    .= "</th>";
         $header_bottom .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         $header_bottom .= "</th>";
      }
      $header_end .= "<th>" . __('Entity') . "</th>";
      $header_end .= "<th>" . __('Name') . "</th>";
      $header_end .= "<th>" . __('Type of Connections', 'connections') . "</th>";
      $header_end .= "<th>" . __('Rates', 'connections') . "</th>";
      $header_end .= "<th>" . __('Guaranteed Rates', 'connections') . "</th>";

      if ($number) {
         echo $header_begin . $header_top . $header_end;
      }

      while ($data = $DB->fetchArray($result)) {
         $name = $data["name"];
         if ($_SESSION["glpiis_ids_visible"]
             || empty($data["name"])) {
            $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
         }
         if ($_SESSION['glpiactiveprofile']['interface'] != 'helpdesk') {
            $link     = PluginConnectionsConnection::getFormURLWithID($data['id']);
            $namelink = "<a href=\"" . $link . "\">" . $name . "</a>";
         } else {
            $namelink = $name;
         }

         echo "<tr class='tab_bg_1'>";
         if ($canedit) {
            echo "<td width='10'>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["IDD"]);
            echo "</td>";
         }

         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_entities", $data['entities_id']) . "</td>";
         echo "<td class='center" .
              (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
         echo ">" . $namelink . "</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName(
               $dbu->getTableForItemType('PluginConnectionsConnectionType'),
               $data['plugin_connections_connectiontypes_id']) . "</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName(
               $dbu->getTableForItemType('PluginConnectionsConnectionRate'),
               $data['plugin_connections_connectionrates_id']) . "</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName(
               $dbu->getTableForItemType('PluginConnectionsGuaranteedConnectionRate'),
               $data['plugin_connections_guaranteedconnectionrates_id']) . "</td>";
         echo "</tr>";
      }

      if ($number) {
         echo $header_begin . $header_bottom . $header_end;
      }

      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }


   /**
    * @param        $itemtype
    * @param        $ID
    * @param string $withtemplate
    *
    * @throws \GlpitestSQLError
    */
   public function showPluginFromSupplier($itemtype, $ID, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      $item                        = new $itemtype();
      $canread                     = $item->can($ID, READ);
      $dbu                         = new DbUtils();
      $PluginConnectionsConnection = new PluginConnectionsConnection();
      $entitiesRestrict            = $dbu->getEntitiesRestrictRequest(
         " AND ",
         "glpi_plugin_connections_connections",
         '',
         '',
         $PluginConnectionsConnection->maybeRecursive()
      );

      $query  = "SELECT `glpi_plugin_connections_connections`.*
                FROM `glpi_plugin_connections_connections`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_connections_connections`.`entities_id`)
                WHERE `suppliers_id` = '" . $ID . "'
                $entitiesRestrict
                ORDER BY `glpi_plugin_connections_connections`.`name`";
      $result = $DB->query($query);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='" . (5 + $colsup) . "'>" . __('Connections linked', 'connections') . ":</th></tr>";
      echo "<tr><th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>" . __('Entity') . "</th>";
      }
      echo "<th>" . __('Technician in charge of the hardware') . "</th>";
      echo "<th>" . __('Type of Connections', 'connections') . "</th>";
      echo "<th>" . __('Last update') . "</th>";
      echo "</tr>";

      while ($data = $DB->fetchArray($result)) {
         echo "<tr class='tab_bg_1" . ($data["is_deleted"] == '1' ? "_2" : "") . "'>";

         if ($withtemplate != 3 && $canread
             && (in_array($data['entities_id'], $_SESSION['glpiactiveentities'])
                 || $data["is_recursive"])) {
            echo "<td class='center'>";
            echo "<a href='" . PLUGINCONNECTIONS_WEBDIR . "/front/connection.form.php?id=" . $data["id"] . "'>";
            echo $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) {
               echo " (" . $data["id"] . ")";
            }
            echo "</a></td>";
         } else {
            echo "<td class='center'>" . $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) {
               echo " (" . $data["id"] . ")";
            }
            echo "</td>";
         }

         if (Session::isMultiEntitiesMode()) {
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_entities", $data['entities_id']);
            echo "</td>";
         }

         echo "<td class='center'>";
         echo getUsername($data["users_id"]);
         echo "</td>";

         echo "<td class='center'>";
         echo Dropdown::getDropdownName(
            "glpi_plugin_connections_connectiontypes",
            $data["plugin_connections_connectiontypes_id"]
         );
         echo "</td>";

         echo "<td class='center'>";
         echo Html::convDate($data["date_mod"]);
         echo "</td>";

         echo "</tr>";
      }

      echo "</table></div>";
   }

   /**
    * @param PluginConnectionsConnection $item
    *
    * @return int
    */
   static function countForConnection(PluginConnectionsConnection $item) {

      $types = self::getClasses();
      if (count($types) == 0) {
         return 0;
      }
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_connections_connections_items',
                                        ["plugin_connections_connections_id" => $item->getID(),
                                         "itemtype"                      => $types
                                        ]);
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $withtemplate
    *
    * @return array|string
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $dbu = new DbUtils();
      if ($item->getType() == 'PluginConnectionsConnection' && count(self::getClasses(false))) {

         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForConnection($item));
         }
         return _n('Associated item', 'Associated items', 2);

      } else if (in_array($item->getType(), self::getClasses(true))
                 && Session::haveRight('plugin_connections_connection', READ)) {

         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(PluginConnectionsConnection::getTypeName(2), self::countForItem($item));
         }

         return self::getTypeName(2);

      } else if ($item->getType() == 'Supplier'
                 && Session::haveRight('plugin_connections_connection', READ)) {

         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(PluginConnectionsConnection::getTypeName(2), self::countSupplierForItem($item));
         }

         return self::getTypeName(2);
      }

      return '';
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $tabnum
    * @param int         $withtemplate
    *
    * @return bool
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      $PluginConnectionsConnection_Item = new self();
      if ($item->getType() == 'PluginConnectionsConnection') {

         $PluginConnectionsConnection_Item->showItemFromPlugin($item);

      } else if (in_array($item->getType(), self::getClasses(true))) {

         $PluginConnectionsConnection_Item->showPluginFromItems($item->getType(), $item->getID());

      } else if ($item->getType() == 'Supplier') {

         $PluginConnectionsConnection_Item->showPluginFromSupplier($item->getType(), $item->getID());

      }

      return true;
   }


   /**
    * @param CommonDBTM $item
    *
    * @return int
    */
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_connections_connections_items',
                                        ["itemtype" => $item->getType(),
                                         "items_id" => $item->getID()]);
   }

   /**
    * @param CommonDBTM $item
    *
    * @return int
    */
   static function countSupplierForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_connections_connections',
                                        ["suppliers_id" => $item->getID()]);
   }
}
