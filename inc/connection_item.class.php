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
// Purpose of file: plugin connections v1.6.4 - GLPI 0.84
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginConnectionsConnection_Item extends CommonDBTM {

   public $dohistory = TRUE;

   static $rightname = 'plugin_connections_connection';

   // From CommonDBRelation
   public $itemtype_1 = "PluginConnectionsConnection";
   public $items_id_1 = 'plugin_connections_connections_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';

   /**
    * Clean object veryfing criteria (when a relation is deleted)
    *
    * @param $crit array of criteria (should be an index)
    */
   public function clean ($crit)
   {
      global $DB;

      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
   }
   
   public static function getClasses($all = false)
   {

      static $types = array(
         'NetworkEquipment',
      );

      $plugin = new Plugin();
      if ($plugin->isActivated("appliances")) {
         $types[] = 'PluginAppliancesAppliance';
      }

      if ($all) {
         return $types;
      }

      foreach ($types as $key=>$type) {
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


   public function getFromDBbyConnectionsAndItem($connections_id, $items_id, $itemtype)
   {
      global $DB;

      $query = "SELECT * FROM `" . $this->getTable() . "`
                WHERE `plugin_connections_connections_id` = '" . $connections_id . "'
                AND `itemtype` = '" . $itemtype . "'
                AND `items_id` = '" . $items_id . "'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   public function addItem($connections_id, $items_id, $itemtype)
   {
      $input = array(
         'plugin_connections_connections_id' => $connections_id,
         'items_id' => $items_id,
         'itemtype' => $itemtype,
      );

      if ($this->add($input)) {
         $item = new NetworkEquipment();
         $item->getFromDB($items_id);

         $changes[0] = 0;
         $changes[1] = '';
         $changes[2] = $item->getNameID(array('forceid' => true));
         Log::history($items_id, 'PluginConnectionsConnection', $changes, 'NetworkEquipment', 15);
      }
   }

   public function deleteItemByConnectionsAndItem($connections_id, $items_id, $itemtype)
   {
      if ($this->getFromDBbyConnectionsAndItem($connections_id, $items_id, $itemtype)) {
         $this->delete(array(
            'id' => $this->fields["id"]
         ));
      }
   }

   public function showItemFromPlugin($instID, $search='') {
      global $DB, $CFG_GLPI;

      if (!$this->canView()) return false;

      $rand = mt_rand();

      $PluginConnectionsConnection = new PluginConnectionsConnection();
      if ($PluginConnectionsConnection->getFromDB($instID)) {
         $canedit = $PluginConnectionsConnection->can($instID, UPDATE);

         $query = "SELECT DISTINCT `itemtype`
                   FROM `" . $this->getTable() . "`
                   WHERE `plugin_connections_connections_id` = '" . (int) $instID . "'
                   ORDER BY `itemtype`";
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         if (Session::isMultiEntitiesMode()) {
            $colsup=1;
         } else {
            $colsup=0;
         }

         echo "<form method='post' name='connections_form$rand' id='connections_form$rand'
                  action=\"" . $CFG_GLPI["root_doc"] . "/plugins/connections/front/connection.form.php\">";

         echo "<div class='center'><table class='tab_cadrehov'>";
         echo "<tr><th colspan='" . ($canedit ? (5 + $colsup) : (4 + $colsup)) . "'>" 
                        . __('Associated element') . ":</th></tr><tr>";
         if ($canedit) {
            echo "<th>&nbsp;</th>";
         }
         echo "<th>" . __('Type') . "</th>";
         echo "<th>" . __('Name') . "</th>";
         if (Session::isMultiEntitiesMode()) {
            echo "<th>" . __('Entity') . "</th>";
         }
         echo "<th>" . __('Serial Number') . "</th>";
         echo "<th>" . __('Inventory number') . "</th>";
         echo "</tr>";

         for ($i = 0 ; $i < $number ; $i++) {
            $type = $DB->result($result, $i, "itemtype");

            if (!class_exists($type)) continue;

            $item = new $type();

            if ($item->canView()) {
               $column           = "name";
               $table            = getTableForItemType($type);
               $itemTable        = $this->getTable();
               $entitiesRestrict = getEntitiesRestrictRequest(" AND ", 't', '', '', $item->maybeRecursive());
               $mayBeTemplate    = ($item->maybeTemplate()) ? " AND t.`is_template` = '0'" : '';

               $query = "SELECT t.*, it.`id` AS items_id, `glpi_entities`.`ID` AS entity
                         FROM `$itemTable` it, `$table` t
                         LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = t.`entities_id`)
                         WHERE t.`id` = it.`items_id`
                         AND it.`itemtype` = '$type'
                         AND it.`plugin_connections_connections_id` = '$instID'
                         $entitiesRestrict
                         $mayBeTemplate
                         ORDER BY `glpi_entities`.`completename`, t.`$column`;";
               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {
                     Session::initNavigateListItems(
                        $type,
                        __('Connections', 'connections') . " = " . $PluginConnectionsConnection->fields['name']
                     );

                     while ($data = $DB->fetch_assoc($result_linked)) {
                        $item->getFromDB($data["id"]);

                        Session::addToNavigateListItems($type,$data["id"]);

                        $ID =  ($_SESSION["glpiis_ids_visible"] || empty($data["name"]))
                              ? " (" . $data["id"] . ")"
                              : "";

                        $link = Toolbox::getItemTypeFormURL($type);
                        $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\">" . $data["name"] . $ID . "</a>";

                        echo "<tr class='tab_bg_1'>";

                        if ($canedit) {
                           echo "<td width='10'>";

                           $sel = (isset($_GET["select"]) && ("all" == $_GET["select"]))
                                 ? "checked"
                                 : "";

                           echo "<input type='checkbox' name='item[" . $data["items_id"] . "]' value='1' " . $sel . ">";
                           echo "</td>";
                        }
                        echo "<td class='center'>" . $item->getTypeName() . "</td>";

                        $is_deleted = (isset($data['is_deleted']) && ($data['is_deleted']))
                                    ? "class='tab_bg_2_2'"
                                    : "";
                        echo "<td class='center' " . $is_deleted . ">" . $name . "</td>";
                        if (Session::isMultiEntitiesMode()) {
                           echo "<td class='center'>";
                           echo Dropdown::getDropdownName("glpi_entities", $data['entity']);
                           echo "</td>";
                        }
                        echo "<td class='center'>";
                        echo isset($data["serial"]) ? $data["serial"] : "-";
                        echo "</td>";
                        echo "<td class='center'>";
                        echo isset($data["otherserial"]) ? $data["otherserial"] : "-";
                        echo "</td>";

                        echo "</tr>";
                     }
                  }
            }
         }

         if ($canedit)   {
            echo "<tr class='tab_bg_1'><td colspan='" . (3 + $colsup) . "' class='center'>";
            echo "<input type='hidden' name='plugin_connections_connections_id' value='$instID'>";
            Dropdown::showAllItems(
               "items_id",
               0,
               0,
               $PluginConnectionsConnection->fields['is_recursive']
                  ? -1
                  : $PluginConnectionsConnection->fields['entities_id'],
               $this->getClasses()
            );
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\"" . __('Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;

            Html::openArrowMassives("connections_form$rand");
            Html::closeArrowMassives(array('deleteitem' => __('Delete')));

         } else {

            echo "</table></div>";
         }
         echo Html::closeForm(false);
      }
   }

   //from items

   public function showPluginFromItems($itemtype, $ID, $withtemplate = '') {
      global $DB, $CFG_GLPI;
      
      $item    = new $itemtype();
      $canread = $item->can($ID, READ);
      $canedit = $item->can($ID, UPDATE);
      $table   = $this->getTable();
      $rand    = mt_rand();

      $PluginConnectionsConnection = new PluginConnectionsConnection();
      $entitiesRestrict            = getEntitiesRestrictRequest(
         " AND ",
         "glpi_plugin_connections_connections",
         '',
         '',
         $PluginConnectionsConnection->maybeRecursive()
      );

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='connection_form$rand' id='connection_form$rand' method='post'
                action='" . $CFG_GLPI["root_doc"] . "/plugins/connections/front/connection.form.php'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add an item')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";
         Dropdown::showSelectItemFromItemtypes(array('itemtypes'
                                                       => array('PluginConnectionsConnection'),
                                                     'entity_restrict'
                                                       => ($item->fields['is_recursive']
                                                           ?getSonsOf('glpi_entities',
                                                                      $item->fields['entities_id'])
                                                           :$item->fields['entities_id']),
                                                     'checkright'
                                                       => true,
                                                     'items_id_name'
                                                       => 'plugin_connections_connections_id'));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "<input type='hidden' name='items_id' value='$ID'>";
         echo "<input type='hidden' name='additem' value='true'>";
         echo "<input type='hidden' name='itemtype' value='NetworkEquipment'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      $query = "SELECT  t.`id` AS IDD, `glpi_plugin_connections_connections`.*
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
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('container' => 'mass'.__CLASS__.$rand);
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canedit && $number) {
         $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_top    .= "</th>";
         $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom .= "</th>";
      }
      $header_end .= "<th>".__('Entity')."</th>";
      $header_end .= "<th>".__('Name')."</th>";
      $header_end .= "<th>".__('Type of Connections', 'connections')."</th>";
      $header_end .= "<th>".__('Rates', 'connections')."</th>";
      $header_end .= "<th>".__('Guaranteed Rates', 'connections')."</th>";
      
      if ($number) {
         echo $header_begin.$header_top.$header_end;
      }

      while ($data=$DB->fetch_array($result)) {
         $name = $data["name"];
         if ($_SESSION["glpiis_ids_visible"]
             || empty($data["name"])) {
            $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
         }
         if($_SESSION['glpiactiveprofile']['interface'] != 'helpdesk') {
            $link     = PluginConnectionsConnection::getFormURLWithID($data['id']);
            $namelink = "<a href=\"".$link."\">".$name."</a>";
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
         echo Dropdown::getDropdownName("glpi_entities", $data['entities_id'])."</td>";
         echo "<td class='center".
                  (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
         echo ">".$namelink."</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName(
               getTableForItemType('PluginConnectionsConnectionType'), 
               $data['plugin_connections_connectiontypes_id'])."</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName(
               getTableForItemType('PluginConnectionsConnectionRate'), 
               $data['plugin_connections_connectionrates_id'])."</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName(
               getTableForItemType('PluginConnectionsGuaranteedConnectionRate'), 
               $data['plugin_connections_guaranteedconnectionrates_id'])."</td>";
         echo "</tr>";
      }

      if ($number) {
         echo $header_begin.$header_bottom.$header_end;
      }

      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }

   public function showPluginFromSupplier($itemtype, $ID, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      $item                        = new $itemtype();
      $canread                     = $item->can($ID, READ);
      $canedit                     = $item->can($ID, UPDATE);

      $PluginConnectionsConnection = new PluginConnectionsConnection();
      $entitiesRestrict            = getEntitiesRestrictRequest(
         " AND ",
         "glpi_plugin_connections_connections",
         '',
         '',
         $PluginConnectionsConnection->maybeRecursive()
      );

      $query = "SELECT c.*
                FROM `glpi_plugin_connections_connections` c
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = c.`entities_id`)
                WHERE `suppliers_id` = '$ID'
                $entitiesRestrict
                ORDER BY c.`name`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      echo "<form method='post' action=\"" . $CFG_GLPI["root_doc"] . "/plugins/connections/front/connection.form.php\">";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='" . (5 + $colsup) . "'>" . __('Connections linked','connections') . ":</th></tr>";
      echo "<tr><th>" . __('Name') . "</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>" . __('Entity') . "</th>";
      }
      echo "<th>" . __('Technician in charge of the hardware') . "</th>";
      echo "<th>" . __('Type of Connection', 'connections') . "</th>";
      echo "<th>" . __('Last update'). "</th>";
      echo "</tr>";

      while ($data=$DB->fetch_array($result)) {
         echo "<tr class='tab_bg_1" . ($data["is_deleted"] == '1' ? "_2" : "") . "'>";

         if ($withtemplate != 3 && $canread 
               && (in_array($data['entities_id'], $_SESSION['glpiactiveentities']) 
                     || $data["is_recursive"])) {
            echo "<td class='center'>";
            echo "<a href='" . $CFG_GLPI["root_doc"] . "/plugins/connections/front/connection.form.php?id=" . $data["id"] . "'>";
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
      echo Html::closeForm(false);
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      if (!$withtemplate) {
         if ($item->getType() == 'PluginConnectionsConnection' && count(self::getClasses(false))) {
            return __('Associated item');
         } else if (in_array($item->getType(), self::getClasses(true))) {
            return PluginConnectionsConnection::getTypeName(2);
         }
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      $PluginConnectionsConnection_Item = new self();
      if ($item->getType() == 'PluginConnectionsConnection') {
         $PluginConnectionsConnection_Item->showItemFromPlugin($item->getID());
      } else if (in_array($item->getType(), self::getClasses(true))) {
         $PluginConnectionsConnection_Item->showPluginFromItems($item->getType(), $item->getID());
      }

      return true;
   }
}
