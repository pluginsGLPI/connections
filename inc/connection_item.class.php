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
   
   // From CommonDBRelation
   public $itemtype_1 = "PluginConnectionsConnection";
   public $items_id_1 = 'plugin_connections_connections_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';

   static function canCreate() {
      return true; //TODO : A porter ?
      return plugin_connections_haveRight('connections', 'w');
   }

   static function canView() {
      return plugin_connections_haveRight('connections', 'r');
   }
   
   /**
    * Clean object veryfing criteria (when a relation is deleted)
    *
    * @param $crit array of criteria (should be an index)
    */
   public function clean($crit) {
      global $DB;
      
      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
   }
   
	static function getClasses($all = false) {
	
      static $types = array('NetworkEquipment');

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
   

	function getFromDBbyConnectionsAndItem($plugin_connections_connections_id, $items_id, $itemtype) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."` " .
			"WHERE `plugin_connections_connections_id` = '" . $plugin_connections_connections_id . "' 
			AND `itemtype` = '" . $itemtype . "'
			AND `items_id` = '" . $items_id . "'";

		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			}
		}
		return false;
	}
	
	function addItem($plugin_connections_connections_id,$items_id,$itemtype) {

      $this->add(array('plugin_connections_connections_id' => $plugin_connections_connections_id,
                     'items_id' => $items_id,
                     'itemtype' => $itemtype));    
   }
  
   function deleteItemByConnectionsAndItem($plugin_connections_connections_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyConnectionsAndItem($plugin_connections_connections_id,$items_id,$itemtype)) {
         return $this->delete(array('id'=>$this->fields["id"]));
      }
      return false;
   }
  
   function showItemFromPlugin($instID, $search = '') {
      global $DB, $CFG_GLPI;

      if (!$this->canView()) {
         return false;
      }
      
      $PluginConnectionsConnection = new PluginConnectionsConnection();
      if (! $PluginConnectionsConnection->getFromDB($instID)) {
         return;
      }

      $rand = mt_rand();

      $canedit = true: //$PluginConnectionsConnection->can($instID,'w'); //TODO : à porter

      $query = "SELECT DISTINCT `itemtype`
          FROM `".$this->getTable()."`
          WHERE `plugin_connections_connections_id` = '$instID'
          ORDER BY `itemtype`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      $colsup = Session::isMultiEntitiesMode() ? 1 : 0;

      echo "<form method='post' name='connections_form$rand' id='connections_form$rand' 
               action=\"".$CFG_GLPI["root_doc"]."/plugins/connections/front/connection.form.php\">";

      echo "<table class='tab_cadrehov'>";
      echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".__("Linked elements", 'connections').":</th></tr>";

      echo "<tr>";
      if ($canedit) {
         echo "<th>&nbsp;</th>";
      }
      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Name')."</th>";

      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
      echo "</tr>";

      for ($i = 0; $i < $number; $i++) {
         $type = $DB->result($result, $i, "itemtype");
         if (!class_exists($type)) {
            continue;
         }           
         $item = new $type();
         if ($item->canView()) {
            $table = getTableForItemType($type);

            $query = "SELECT `".$table."`.*, `".$this->getTable()."`.`id` AS items_id, `glpi_entities`.`ID` AS entity "
             ." FROM `".$this->getTable()."`, `".$table
             ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
             ." WHERE `".$table."`.`id` = `".$this->getTable()."`.`items_id`
             AND `".$this->getTable()."`.`itemtype` = '$type'
             AND `".$this->getTable()."`.`plugin_connections_connections_id` = '$instID' "
             . getEntitiesRestrictRequest(" AND ",$table,'','',$item->maybeRecursive());

            if ($item->maybeTemplate()) {
               $query .=" AND `".$table."`.`is_template` = '0'";
            }
            $column = "name";
            $query .= " ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column` ";

            $result_linked = $DB->query($query); //Please, keep this *here*

            if ($result_linked && $DB->numrows($result_linked)) {
               Session::initNavigateListItems($type,__("Connections", 'connections')." = ".$PluginConnectionsConnection->fields['name']);

               while ($data = $DB->fetch_assoc($result_linked)) {
                  $item->getFromDB($data["id"]);
                  
                  Session::addToNavigateListItems($type,$data["id"]);
                  $ID = "";
                  if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                     $ID = " (".$data["id"].")";
                  }
                  $name = "<a href=\"".Toolbox::getItemTypeFormURL($type)."?id=".$data["id"]."\">".$data["name"]."$ID</a>";

                  echo "<tr class='tab_bg_1 center'>";

                  if ($canedit) {
                     echo "<td width='10'>";
                     $sel = "";
                     if (isset($_GET["select"]) && $_GET["select"] == "all") {
                        $sel = "checked";
                     }
                     echo "<input type='checkbox' name='item[".$data["items_id"]."]' value='1' $sel>";
                     echo "</td>";
                  }
                  echo "<td>".$item->getTypeName()."</td>";

                  echo "<td ".(isset($data['is_deleted'])&&$data['is_deleted']?"class='tab_bg_2_2'":"").">";
                  echo $name;
                  echo "</td>";

                  if (Session::isMultiEntitiesMode()) {
                     echo "<td>".Dropdown::getDropdownName("glpi_entities",$data['entity'])."</td>";
                  }

                  echo "<td>".(isset($data["serial"]) && !empty($data['serial']) ? $data["serial"] :"-")."</td>";
                  echo "<td>".(isset($data["otherserial"]) && !empty($data['otherserial']) ? $data["otherserial"] :"-")."</td>";

                  echo "</tr>";
               }
            }
         }
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='".(3+$colsup)."' class='center'>";
         echo "<input type='hidden' name='plugin_connections_connections_id' value='$instID'>";

         $entity_restrict = $PluginConnectionsConnection->fields['is_recursive'] ? -1 : $PluginConnectionsConnection->fields['entities_id'];

         Dropdown::showAllItems("items_id",0,0,$entity_restrict,$this->getClasses());
         echo "</td>";

         echo "<td colspan='2' class='tab_bg_2 center'>";
         echo "<input type='submit' name='additem' value=\"".__('Add')."\" class='submit'>";
         echo "</td></tr>";
      }
      echo "</table>";
      
      if ($canedit) {
         Html::openArrowMassives("connections_form$rand");
         Html::closeArrowMassives(array('deleteitem' => __('Delete')));
      }
      Html::closeForm();
   }

   //from items

   //For NetworkEquipement (for example)
   function showPluginFromItems($itemtype,$ID,$withtemplate='') {
      global $DB, $CFG_GLPI;

      $item = new $itemtype();

      $canread = $item->can($ID,'r');
      $canedit = true; //$item->can($ID,'w'); //TODO : À porter
      
      $PluginConnectionsConnection = new PluginConnectionsConnection();
      
      $query = "SELECT `".$this->getTable()."`.`id` AS items_id,`glpi_plugin_connections_connections`.* "
        ."FROM `".$this->getTable()."`,`glpi_plugin_connections_connections` "
        ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_connections_connections`.`entities_id`) "
        ." WHERE `".$this->getTable()."`.`items_id` = '".$ID."'
        AND `".$this->getTable()."`.`itemtype` = '".$itemtype."'
        AND `".$this->getTable()."`.`plugin_connections_connections_id` = `glpi_plugin_connections_connections`.`id` "
        . getEntitiesRestrictRequest(" AND ","glpi_plugin_connections_connections",'','',$PluginConnectionsConnection->maybeRecursive());
      $query.= " ORDER BY `glpi_plugin_connections_connections`.`name` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $target = $CFG_GLPI["root_doc"]."/plugins/connections/front/connection.form.php";
      echo "<form method='post' action='$target'>";

      echo "<table class='tab_cadre_fixe'>";

      $colsup = Session::isMultiEntitiesMode() ? 1 : 0;

      echo "<tr><th colspan='".(8+$colsup)."'>".__("Connections linked", 'connections').":</th></tr>";
      echo "<tr><th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".__('Group')."</th>";
      echo "<th>".Supplier::getTypeName(1)."</th>";
      echo "<th>".__("Technical reference", 'connections')."</th>";
      echo "<th>"._n('Type', 'Types', 1)."</th>";
      echo "<th>".__("Rates", 'connections')."</th>";
      echo "<th>".__("Guaranteed Rates", 'connections')."</th>";
      if ($this->canCreate()) {
         echo "<th>&nbsp;</th>";
      }
      echo "</tr>";
      $used = array();
      while ($data = $DB->fetch_array($result)) {
         $connectionsID = $data["id"];
         $used[] = $connectionsID;

         echo "<tr class='center tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

         echo "<td>";
         if ($withtemplate!=3 && $canread && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) || $data["is_recursive"])) {
            echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/connections/front/connection.form.php?id=".$data["id"]."'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) {
               echo " (".$data["id"].")";
            }
            echo "</a>";
         } else {
            echo $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
         }
         echo "</td>";

         if (Session::isMultiEntitiesMode()) {
            echo "<td>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
         }
         echo "<td>".Dropdown::getDropdownName("glpi_groups",$data["groups_id"])."</td>";
         echo "<td>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/enterprise.form.php?ID=".$data["suppliers_id"]."\">";
         echo Dropdown::getDropdownName("glpi_suppliers",$data["suppliers_id"]);
         if ($_SESSION["glpiis_ids_visible"] == 1) {
            echo " (".$data["suppliers_id"].")";
         }
         echo "</a></td>";
         echo "<td>".getUsername($data["users_id"])."</td>";
         echo "<td>";
         echo Dropdown::getDropdownName("glpi_plugin_connections_connectiontypes",$data["plugin_connections_connectiontypes_id"]);
         echo "</td>";
         echo "<td>";
         echo Dropdown::getDropdownName("glpi_plugin_connections_connectionrates",$data["plugin_connections_connectionrates_id"]);
         echo "</td>";
         echo "<td>";
         echo Dropdown::getDropdownName("glpi_plugin_connections_guaranteedconnectionrates",$data["plugin_connections_guaranteedconnectionrates_id"]);
         echo "</td>";

         if ($this->canCreate()) {
            if ($withtemplate < 2) {
               echo "<td class='tab_bg_2'>";
               Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/connections/front/connection.form.php', 
                  'deleteconnections', __('Delete'), array('id'=>$data["items_id"]), $CFG_GLPI['root_doc'].'/pics/delete.png');
               echo "</td>";
            }
         }
         echo "</tr>";
      }

      if ($canedit) {
         if ($this->canCreate()) {
            $entities = $item->isRecursive() ? getSonsOf('glpi_entities',$item->getEntityID()) : $item->getEntityID();

            $limit = getEntitiesRestrictRequest(" AND ", "glpi_plugin_connections_connections",'',$entities,true);

            $result = $DB->query("SELECT COUNT(*)
                                   FROM `glpi_plugin_connections_connections`
                                   WHERE `is_deleted` = '0' $limit");
            $nb = $DB->result($result,0,0);

            if ($nb > count($used)) {
               echo "<tr class='tab_bg_1'>";
               
               echo "<td colspan='".(7+$colsup)."' class='right'>";
               echo "<input type='hidden' name='items_id' value='$ID'>";
               echo "<input type='hidden' name='itemtype' value='$itemtype'>";

               $PluginConnectionsConnection = new PluginConnectionsConnection();
               $PluginConnectionsConnection->dropdownConnections('plugin_connections_connections_id', $entities, $used); //TODO : à porter !
               echo "</td>";

               echo "<td class='center'>";
               echo "<input type='submit' name='additem' value=\"".__('Add')."\" class='submit'>";
               echo "</td>";
               echo "</tr>";
            }
         }

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='".(8+$colsup)."' class='right'>";
         echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/connections/front/connection.form.php'>";
         echo __("Create a new connection", 'connections')."</a>";
         echo "</td>";
         echo "</tr>";
      }
      
      echo "</table>";
      Html::closeForm();
   }
  
   //TODO : NOT USED (?)
   static function showPluginFromSupplier($itemtype, $ID, $withtemplate = '') {
      global $DB, $CFG_GLPI;

      $item = new $itemtype();

      $canread = $item->can($ID, 'r');
      $canedit = $item->can($ID, 'w');
      
      $PluginConnectionsConnection = new PluginConnectionsConnection();
      
      $query = "SELECT `glpi_plugin_connections_connections`.* "
        ."FROM `glpi_plugin_connections_connections` "
        ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_connections_connections`.`entities_id`) "
        ." WHERE `suppliers_id` = '$ID' "
        . getEntitiesRestrictRequest(" AND ","glpi_plugin_connections_connections",'','',$PluginConnectionsConnection->maybeRecursive());
      $query.= " ORDER BY glpi_plugin_connections_connections.name ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $colsup = Session::isMultiEntitiesMode() ? 1 : 0;

      $target = $CFG_GLPI["root_doc"]."/plugins/connections/front/connection.form.php";
      echo "<form method='post' action='$target'>";

      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      echo "<th colspan='".(5+$colsup)."'>".__("Connections linked", 'connections').":</th>";
      echo "</tr>";

      echo "<tr><th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".__('Entity')."</th>";
      echo "<th>" . __("Technical reference", 'connections') . "</th>";
      echo "<th>" . _n('Type', 'Types', 1) . "</th>";
      echo "<th>" . __("Creation date", 'connections') . "</th>";
      echo "<th>" . __("Expiration date") . "</th>";
      echo "</tr>";

      while ($data = $DB->fetch_array($result)) {

         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
         echo "<td class='center'>";
         if ($withtemplate!=3 && $canread 
               && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) || $data["is_recursive"])) {
            echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/connections/front/connection.form.php?id=".$data["id"]."'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
            echo "</a>";
         } else {
            echo $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
         }
         echo "</td>";
         if (Session::isMultiEntitiesMode()) {
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
         }
         echo "<td class='center'>".getUsername($data["users_id"])."</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_plugin_connections_connectiontypes",$data["plugin_connections_connectiontypes_id"]);
         echo "</td>";
         echo "<td class='center'>".Html::convDate($data["date_creation"])."</td>";
         echo "<td class='center'>";
         if ($data["date_expiration"] <= date('Y-m-d') && !empty($data["date_expiration"]))
            echo "<span class='plugin_connections_date_color'>".Html::convDate($data["date_expiration"])."</span>";
         else if (empty($data["date_expiration"]))
            echo __('Never expires', 'connections');
         else
            echo Html::convDate($data["date_expiration"]);
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";
      echo "</div>";

      Html::closeForm();
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($withtemplate) {
         return '';
      }

      if ($item->getType() == 'PluginConnectionsConnection' && count(self::getClasses(false))) {
         if (! $_SESSION['glpishow_count_on_tabs']) {
            return __('Associated item');
         }
         $nb = countElementsInTable($this->getTable(),
                                    "`plugin_connections_connections_id` = '".$item->getID()."'");
         $title = _n("Associated item", "Associated items", $nb);
         return self::createTabEntry($title, $nb);
         
      } else if (in_array($item->getType(), self::getClasses(true))) {
         //Note : can add (<sup>nb</sup>) like on PluginConnectionsConnection
         return PluginConnectionsConnection::getTypeName(2);
      }
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $PluginConnectionsConnection_Item = new self();
      if ($item->getType() == 'PluginConnectionsConnection') {
         $PluginConnectionsConnection_Item->showItemFromPlugin($item->getID());
      } else if (in_array($item->getType(), self::getClasses(true))) {
         $PluginConnectionsConnection_Item->showPluginFromItems($item->getType(), $item->getID());
      }
      return true;
   }
}
