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

function plugin_connections_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/connections/inc/profile.class.php");
   $update = false;
   
   //TODO: Use "Migration" class instead (available since GLPI v0.80)
   
   if (!TableExists('glpi_plugin_connection') && !TableExists('glpi_plugin_connections_connections')) { // Fresh install
      // Go for 1.6.4
      $DB->runFile(GLPI_ROOT .'/plugins/connections/sql/empty-1.6.0.sql');
      $DB->runFile(GLPI_ROOT .'/plugins/connections/sql/update-1.6.0-to-1.6.4.sql');

   } else if (TableExists('glpi_plugin_connections_connections') && !TableExists('glpi_plugin_connectiond_device')) {
      // We're 1.6.0 update to 1.6.4
      $DB->runFile(GLPI_ROOT .'/plugins/connections/sql/update-1.6.0-to-1.6.4.sql');

   } else if (TableExists("glpi_plugin_connection") && !FieldExists("glpi_plugin_connection","recursive")) {
      
      $update = true;
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.3.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.4.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.5.0.sql");

   } else if (TableExists("glpi_plugin_connection_profiles") && FieldExists("glpi_plugin_connection_profiles","interface")) {
      
      $update = true;
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.4.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.3.0.sql");

   } else if (TableExists("glpi_plugin_connection") && !FieldExists("glpi_plugin_connection","helpdesk_visible")) {
      
      $update = true;
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.3.0.sql");
      
   }

   // Migrate data (of notepad)
   $notepad_table = 'glpi_plugin_connections_connections';

   if (FieldExists($notepad_table, 'notepad')) {
      $query = "SELECT id, notepad
                FROM `$notepad_table`
                WHERE notepad IS NOT NULL
                      AND notepad <>'';";
      foreach ($DB->request($query) as $data) {
      	//Note : Could use GLPI Log data for have real date and date_mod (and others fields)
         $iq = "INSERT INTO `glpi_notepads`
                       (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                VALUES ('".getItemTypeForTable($notepad_table)."', '".$data['id']."',
                        '".addslashes($data['notepad'])."', NOW(), NOW())";
         $DB->queryOrDie($iq, "0.85 migrate notepad data");
      }
      $DB->query("ALTER TABLE `$notepad_table` DROP COLUMN `notepad`;");
   }

   // Delete a (very) old field
   $field = 'bytes';
   $table = 'glpi_plugin_connections_connections';
   if (FieldExists($table, $field)) {
      $DB->query("ALTER TABLE `$table` DROP COLUMN `$field`;");
   }
   
   if ($update) {
      $query_ = "SELECT *
            FROM `glpi_plugin_connections_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_connections_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $result=$DB->query("ALTER TABLE `glpi_plugin_connections_profiles` DROP `name` ;");
   
      Plugin::migrateItemType(
         array(4400=>'PluginConnectionsConnection'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_connections_connections_items"));
      
      Plugin::migrateItemType(
         array(1200 => "PluginAppliancesAppliance",
         		1300 => "PluginWebapplicationsWebapplication"),
         array("glpi_plugin_connections_connections_items"));
	}
	
   PluginConnectionsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

   return true;
}

function plugin_connections_uninstall() {
	global $DB;

	$tables = array('glpi_plugin_connections_configs',
					"glpi_plugin_connections_connections",
					"glpi_plugin_connections_connections_items",
					"glpi_plugin_connections_connectiontypes",
					"glpi_plugin_connections_connectionrates",
					"glpi_plugin_connections_guaranteedconnectionrates",
					"glpi_plugin_connections_profiles",
					"glpi_plugin_connections_notificationstates");

	foreach($tables as $table) {
		$DB->query("DROP TABLE IF EXISTS `$table`;");
	}
   
   //old versions	
   $tables = array("glpi_plugin_connection",
					"glpi_plugin_connection_device",
					'glpi_plugin_connections_connectionratesguaranteed',
					"glpi_dropdown_plugin_connections_type",
					"glpi_plugin_connection_profiles",
					"glpi_plugin_connection_mailing");

	foreach($tables as $table) {
		$DB->query("DROP TABLE IF EXISTS `$table`;");
	}
   	
   $tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginConnectionsConnection';");

	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginConnectionsConnection'));
   }

   //Note : For future use of glpi_notepads by the plugin
   $DB->query("DELETE FROM glpi_notepads WHERE itemtype LIKE 'PluginConnections%'"); //PluginConnectionsConnection

   //Note : beaucoup de liens entre Connection et les objets GLPI ne sont pas clearés.

	return true;
}

function plugin_connections_AssignToTicket($types) { //Old ?
	if (true) { //if (plugin_connections_haveRight("open_ticket","1")) { //TODO : DEBUG
		$types['PluginConnectionsConnection'] = __("Connections", 'connections');
	}
	return $types;
}


// Define dropdown relations
function plugin_connections_getDatabaseRelations() {

	$plugin = new Plugin();
	if (! $plugin->isActivated("connections")) {
      return array();
   }

	return array("glpi_plugin_connections_connectiontypes"=>array("glpi_plugin_connections_connections"=>"plugin_connections_connectiontypes_id"),
				"glpi_plugin_connections_connectionrates"=>array("glpi_plugin_connections_connections"=>"plugin_connections_connectionrates_id"),
				"glpi_plugin_connections_guaranteedconnectionrates"=>array("glpi_plugin_connections_connections"=>"plugin_connections_guaranteedconnectionrates_id"),
            "glpi_users"=>array("glpi_plugin_connections_connections"=>"users_id"),
            "glpi_groups"=>array("glpi_plugin_connections_connections"=>"groups_id"),
            "glpi_suppliers"=>array("glpi_plugin_connections_connections"=>"glpi_suppliers"),
            "glpi_plugin_connections_connections"=>array("glpi_plugin_connections_connections_items"=>"plugin_connections_connections_id"),
            "glpi_profiles" => array("glpi_plugin_badges_profiles" => "profiles_id"),
            "glpi_entities"=>array("glpi_plugin_connections_connections"=>"entities_id",
				"glpi_plugin_connections_connectiontypes"=>"entities_id"));
}

// Define Dropdown tables to be manage in GLPI :
function plugin_connections_getDropdown() {	
	$plugin = new Plugin(); 
	if ($plugin->isActivated("connections"))
		return array('PluginConnectionsConnectionType'=> PluginConnectionsConnectionType::getTypeName(2),
					'PluginConnectionsConnectionRate'=> PluginConnectionsConnectionRate::getTypeName(2),
					'PluginConnectionsGuaranteedConnectionRate' => PluginConnectionsGuaranteedConnectionRate::getTypeName(2));
	return array();
}

////// SEARCH FUNCTIONS ///////() {

function plugin_connections_getAddSearchOptions($itemtype) { //Note : Es ce possible de déplacer une partie ?
	
   $sopt = array();

   if (! plugin_connections_haveRight("connections", "r")) {
   	return $sopt;
   }

   // For example, for NetworkEquipment
   if (in_array($itemtype, PluginConnectionsConnection_Item::getClasses(true))) {
      $sopt[4410]['table']          = 'glpi_plugin_connections_connections';
      $sopt[4410]['field']          = 'name';
      $sopt[4410]['linkfield']      = '';
      $sopt[4410]['name']           = __("Connections", 'connections')." - ".__('Name');
      $sopt[4410]['forcegroupby']   = true;
      $sopt[4410]['datatype']       = 'itemlink';
      $sopt[4410]['itemlink_type']  = 'PluginConnectionsConnection';

      $sopt[4411]['table']          = 'glpi_plugin_connections_connectiontypes';
      $sopt[4411]['field']          = 'name';
      $sopt[4411]['linkfield']      = '';
      $sopt[4411]['name']           = __("Connections", 'connections')." - ".PluginConnectionsConnectionType::getTypeName(2);
      $sopt[4411]['forcegroupby']   = true;
      $sopt[4411]['datatype']       = 'itemlink';
      $sopt[4411]['itemlink_type']  = 'PluginConnectionsConnectionType';
 
      $sopt[4412]['table']          = 'glpi_plugin_connections_connectionrates';
      $sopt[4412]['field']          = 'name';
      $sopt[4412]['linkfield']      = '';
      $sopt[4412]['name']           = __("Connections", 'connections')." - ".PluginConnectionsConnectionRate::getTypeName(2);
      $sopt[4412]['forcegroupby']   = true;
      $sopt[4412]['datatype']       = 'itemlink';
      $sopt[4412]['itemlink_type']  = 'PluginConnectionsConnectionRate';

      $sopt[4413]['table']          = 'glpi_plugin_connections_guaranteedconnectionrates';
      $sopt[4413]['field']          = 'name';
      $sopt[4413]['linkfield']      = '';
      $sopt[4413]['name']           = __("Connections", 'connections')." - ".PluginConnectionsGuaranteedConnectionRate::getTypeName(2);
      $sopt[4413]['forcegroupby']   = true;
      $sopt[4413]['datatype']       = 'itemlink';
      $sopt[4413]['itemlink_type']  = 'PluginConnectionsGuaranteedConnectionRate';
	}
	return $sopt;
}

function plugin_connections_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables) {
	switch ($new_table) {
		case "glpi_plugin_connections_connections_items" :
			return " LEFT JOIN `$new_table` ON (`$ref_table`.`id` = `$new_table`.`plugin_connections_connections_id`) ";
			break;

		case "glpi_plugin_connections_connections" : // From items
			$out  = " LEFT JOIN `glpi_plugin_connections_connections_items` ON (`$ref_table`.`id` = `glpi_plugin_connections_connections_items`.`items_id` 
             AND `glpi_plugin_connections_connections_items`.`itemtype` = '$type') ";
			$out .= " LEFT JOIN `$new_table` ON (`$new_table`.`id` = `glpi_plugin_connections_connections_items`.`plugin_connections_connections_id`) ";
			return $out;
			break;

		case "glpi_plugin_connections_connectiontypes" : // From items
			$out  = Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_connections_connections",$linkfield);
			$out .= " LEFT JOIN `$new_table` ON (`$new_table`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_connectiontypes_id`) ";
			return $out;

		case "glpi_plugin_connections_connectionrates" : // From items
			$out = Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_connections_connections",$linkfield);
			$out.= " LEFT JOIN `$new_table` ON (`$new_table`.`id` = `$new_table`.`plugin_connections_connectionrates_id`) ";
			return $out;
		case "glpi_plugin_connections_guaranteedconnectionrates" : // From items
			$out=Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_connections_connections",$linkfield);
			$out.= " LEFT JOIN `$new_table` ON (`$new_table`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_guaranteedconnectionrates_id`) ";
			return $out;
			break;
	}

	return "";
}

//Example :force groupby for multible links to items
function plugin_connections_forceGroupBy($type) { //Note : Utilisé où ?!

	return true;
   /*
	switch ($type) {
		case 'PluginConnectionsConnection':
			return true;
			break;

	}
	return false;
   */
}

function plugin_connections_giveItem($type,$ID,$data,$num) {
	global $DB;

	$searchopt=&Search::getOptions($type);

	$table = $searchopt[$ID]["table"];
	$field = $searchopt[$ID]["field"];

	switch ($table.'.'.$field) {
		case "glpi_plugin_connections_connections_items.items_id" :
			$query_device = "SELECT DISTINCT `itemtype`
							FROM `glpi_plugin_connections_connections_items`
							WHERE `plugin_connections_connections_id` = '".$data['id']."'
							ORDER BY `itemtype`";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);

			$out='';
			
			$connections=$data['id'];
			if ($number_device>0) {
				for ($i=0 ; $i < $number_device ; $i++) {
					$column = "name";
					$itemtype = $DB->result($result_device, $i, "itemtype");
					
					if (!class_exists($itemtype)) {
                  continue;
               }
					$item = new $itemtype();
					if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);
						$query = "SELECT `".$table_item."`.*, `glpi_entities`.`ID` AS entity "
						." FROM `glpi_plugin_connections_connections_items`, `".$table_item
						."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
						." WHERE `".$table_item."`.`id` = `glpi_plugin_connections_connections_items`.`items_id`
						AND `glpi_plugin_connections_connections_items`.`itemtype` = '$itemtype'
						AND `glpi_plugin_connections_connections_items`.`plugin_connections_connections_id` = '".$connections."' "
						. getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

						if ($item->maybeTemplate()) {
							$query.=" AND `".$table_item."`.`is_template` = '0'";
						}
						$query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column`";

						if ($result_linked=$DB->query($query))
							if ($DB->numrows($result_linked)) {
								//$item = new $itemtype();
								while ($data = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item->getTypeName()." - ".$item->getLink()."<br>";
                           }
								}
							} else
								$out.= ' ';
						} else
							$out.=' ';
				}
			}
         return $out;
         break;
	}
	return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_connections_MassiveActions($type) { //TODO : DELETE

	//TODO : A porter -> cf. getSpecificMassiveActions()
	if (in_array($type, PluginConnectionsConnection_Item::getClasses(true))) {
			return array("plugin_connections_add_item" => __("Link to connections", 'connections'));
		}
	return array();
}

//TODO : 
function plugin_connections_MassiveActionsDisplay($options=array()) { //TODO : DELETE

	//TODO : A porter -> cf. showMassiveActionsSubForm()
	if (in_array($options['itemtype'], PluginConnectionsConnection_Item::getClasses(true))) {
      $PluginConnectionsConnection = new PluginConnectionsConnection();
      $PluginConnectionsConnection->dropdownConnections("plugin_connections_connections_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".__('Post')."\" >";
   }
	return "";
}

function plugin_connections_MassiveActionsProcess($data) { //TODO : DELETE
   
   $PluginConnectionsConnection      = new PluginConnectionsConnection();
   $PluginConnectionsConnection_Item = new PluginConnectionsConnection_Item();
	
	switch ($data['action']) {
		case "plugin_connections_add_item":
			foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_connections_connections_id' => $data['plugin_connections_connections_id'],
                              'items_id'      => $key,
                              'itemtype'      => $data['itemtype']);
               if ($PluginConnectionsConnection_Item->can(-1,'w',$input)) {
                  $PluginConnectionsConnection_Item->add($input);
               }
            }
         }
         break;
	}
}

//////////

// Hook done on purge item case
function plugin_item_purge_connections($item) {

   $temp = new PluginConnectionsConnection_Item();
   $temp->clean(array('itemtype' => get_class($item),
                		'items_id' => $item->getField('id')));
   return true;
}

//TODO : Install plugin datainjection
function plugin_datainjection_populate_connections() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginConnectionsConnectionInjection'] = 'connections';
}
