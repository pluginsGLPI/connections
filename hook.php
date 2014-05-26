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
// Purpose of file: plugin connections v1.6.2 - GLPI 0.83
// ----------------------------------------------------------------------
 */

function plugin_connections_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/connections/inc/profile.class.php");
   $update=false;
   
   //TODO: Use "Migration" class instead (available since GLPI v0.80)
   
   if (!TableExists("glpi_plugin_connection") && !TableExists("glpi_plugin_connections_connections")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/empty-1.6.0.sql");

   } else if (TableExists("glpi_plugin_connection") && !FieldExists("glpi_plugin_connection","recursive")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.3.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.4.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.5.0.sql");

   } else if (TableExists("glpi_plugin_connection_profiles") && FieldExists("glpi_plugin_connection_profiles","interface")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.4.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.3.0.sql");

   } else if (TableExists("glpi_plugin_connection") && !FieldExists("glpi_plugin_connection","helpdesk_visible")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/connections/sql/update-1.3.0.sql");
      
   }
   

   if ($update) {
      $query_="SELECT *
            FROM `glpi_plugin_connections_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_connections_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_connections_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
   
      Plugin::migrateItemType(
         array(4400=>'PluginConnectionsConnection'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_connections_connections_items"));
      
      Plugin::migrateItemType(
         array(1200 => "PluginAppliancesAppliance",1300 => "PluginWebapplicationsWebapplication"),
         array("glpi_plugin_connections_connections_items"));
	}
	
   PluginConnectionsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_connections_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_connections_connections",
					"glpi_plugin_connections_connections_items",
					"glpi_plugin_connections_connectiontypes",
					"glpi_plugin_connections_connectionrates",
					"glpi_plugin_connections_connectionratesguaranteed",
					"glpi_plugin_connections_profiles",
					"glpi_plugin_connections_notificationstates");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_connection",
					"glpi_plugin_connection_device",
					"glpi_dropdown_plugin_connections_type",
					"glpi_plugin_connection_profiles",
					"glpi_plugin_connection_mailing");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");

   	
   $tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginConnectionsConnection';");

	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginConnectionsConnection'));
   }

	return true;
}

function plugin_connections_AssignToTicket($types) {
	global $LANG;

	if (plugin_connections_haveRight("open_ticket","1"))
		$types['PluginConnectionsConnection']=$LANG['plugin_connections']['title'][1];
	return $types;
}


// Define dropdown relations
function plugin_connections_getDatabaseRelations() {

	$plugin = new Plugin();

	if ($plugin->isActivated("connections"))
		return array("glpi_plugin_connections_connectiontypes"=>array("glpi_plugin_connections_connections"=>"plugin_connections_connectiontypes_id"),
					 "glpi_plugin_connections_connectionrates"=>array("glpi_plugin_connections_connections"=>"plugin_connections_connectionrates_id"),
					 "glpi_plugin_connections_connectionratesguaranteed"=>array("glpi_plugin_connections_connections"=>"plugin_connections_connectionratesguaranteed_id"),
                     "glpi_users"=>array("glpi_plugin_connections_connections"=>"users_id"),
                     "glpi_groups"=>array("glpi_plugin_connections_connections"=>"groups_id"),
                     "glpi_suppliers"=>array("glpi_plugin_connections_connections"=>"glpi_suppliers"),
                     "glpi_plugin_connections_connections"=>array("glpi_plugin_connections_connections_items"=>"plugin_connections_connections_id"),
                     "glpi_profiles" => array ("glpi_plugin_badges_profiles" => "profiles_id"),
                     "glpi_entities"=>array("glpi_plugin_connections_connections"=>"entities_id",
												"glpi_plugin_connections_connectiontypes"=>"entities_id"));
	else
		return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_connections_getDropdown() {
	global $LANG;
	
	$plugin = new Plugin();

	if ($plugin->isActivated("connections"))
		return array('PluginConnectionsConnectionType'=>$LANG['plugin_connections']['setup'][2],
					'PluginConnectionsConnectionRate'=>$LANG['plugin_connections']['setup'][3],
					'PluginConnectionsConnectionRatesGuaranteed'=>$LANG['plugin_connections']['setup'][4]);
	else
		return array();
}

////// SEARCH FUNCTIONS ///////() {

function plugin_connections_getAddSearchOptions($itemtype) {
	global $LANG;
    
   $sopt=array();

   if (in_array($itemtype, PluginConnectionsConnection_Item::getClasses(true))) {
      if (plugin_connections_haveRight("connections","r")) {
         $sopt[4410]['table']='glpi_plugin_connections_connections';
         $sopt[4410]['field']='name';
         $sopt[4410]['linkfield']='';
         $sopt[4410]['name']=$LANG['plugin_connections']['title'][1]." - ".$LANG['plugin_connections'][7];
         $sopt[4410]['forcegroupby']='1';
         $sopt[4410]['datatype']='itemlink';
         $sopt[4410]['itemlink_type']='PluginConnectionsConnection';

         $sopt[4411]['table']='glpi_plugin_connections_connectiontypes';
         $sopt[4411]['field']='name';
         $sopt[4411]['linkfield']='';
         $sopt[4411]['name']=$LANG['plugin_connections']['title'][1]." - ".$LANG['plugin_connections'][12];
         $sopt[4411]['forcegroupby']='1';
	 
         $sopt[4412]['table']='glpi_plugin_connections_connectionrates';
         $sopt[4412]['field']='name';
         $sopt[4412]['linkfield']='';
         $sopt[4412]['name']=$LANG['plugin_connections']['title'][1]." - ".$LANG['plugin_connections']['setup'][3];
         $sopt[4412]['forcegroupby']='1';

         $sopt[4413]['table']='glpi_plugin_connections_connectionratesguaranteed';
         $sopt[4413]['field']='name';
         $sopt[4413]['linkfield']='';
         $sopt[4413]['name']=$LANG['plugin_connections']['title'][1]." - ".$LANG['plugin_connections']['setup'][3];
         $sopt[4413]['forcegroupby']='1';

      }
	}
	return $sopt;
}

function plugin_connections_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables)  {

	switch ($new_table) {

		case "glpi_plugin_connections_connections_items" :
			return " LEFT JOIN `$new_table` ON (`$ref_table`.`id` = `$new_table`.`plugin_connections_connections_id`) ";
			break;
		case "glpi_plugin_connections_connections" : // From items
			$out= " LEFT JOIN `glpi_plugin_connections_connections_items` ON (`$ref_table`.`id` = `glpi_plugin_connections_connections_items`.`items_id` AND `glpi_plugin_connections_connections_items`.`itemtype` = '$type') ";
			$out.= " LEFT JOIN `glpi_plugin_connections_connections` ON (`glpi_plugin_connections_connections`.`id` = `glpi_plugin_connections_connections_items`.`plugin_connections_connections_id`) ";
			return $out;
			break;
		case "glpi_plugin_connections_connectiontypes" : // From items
			$out=Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_connections_connections",$linkfield);
			$out.= " LEFT JOIN `glpi_plugin_connections_connectiontypes` ON (`glpi_plugin_connections_connectiontypes`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_connectiontypes_id`) ";
			return $out;
		case "glpi_plugin_connections_connectionrates" : // From items
			$out=Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_connections_connections",$linkfield);
			$out.= " LEFT JOIN `glpi_plugin_connections_connectionrates` ON (`glpi_plugin_connections_connectionrates`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_connectionrates_id`) ";
			return $out;
		case "glpi_plugin_connections_connectionratesguaranteed" : // From items
			$out=Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_connections_connections",$linkfield);
			$out.= " LEFT JOIN `glpi_plugin_connections_connectionratesguaranteed` ON (`glpi_plugin_connections_connectionratesguaranteed`.`id` = `glpi_plugin_connections_connections`.`plugin_connections_connectionratesguaranteed_id`) ";
			return $out;
			break;
	}

	return "";
}

function plugin_connections_forceGroupBy($type) {

	return true;
	switch ($type) {
		case 'PluginConnectionsConnection':
			return true;
			break;

	}
	return false;
}

function plugin_connections_giveItem($type,$ID,$data,$num) {
	global $CFG_GLPI, $DB, $LANG;

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];

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
								$item = new $itemtype();
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

function plugin_connections_MassiveActions($type) {
	global $LANG;
	
	switch ($type) {
		case 'PluginConnectionsConnection':
			return array(
				// Specific one
				"plugin_connections_install"=>$LANG['plugin_connections']['setup'][9],
				"plugin_connections_desinstall"=>$LANG['plugin_connections']['setup'][10],
				"plugin_connections_transfert"=>$LANG['buttons'][48],
				);
         break;
	}
	if (in_array($type, PluginConnectionsConnection_Item::getClasses(true))) {
			return array("plugin_connections_add_item"=>$LANG['plugin_connections']['setup'][18]);
		}
	return array();
}

function plugin_connections_MassiveActionsDisplay($options=array()) {
	global $LANG;
	
	$PluginConnectionsConnection=new PluginConnectionsConnection();

	switch ($options['itemtype']) {
		case 'PluginConnectionsConnection':
			switch ($options['action']) {
				// No case for add_document : use GLPI core one
				case "plugin_connections_install":
					Dropdown::showAllItems("item_item",0,0,-1,PluginConnectionsConnection_Item::getClasses(true));
					echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
               break;
				case "plugin_connections_desinstall":
					Dropdown::showAllItems("item_item",0,0,-1,PluginConnectionsConnection_Item::getClasses(true));
               echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
               break;
				case "plugin_connections_transfert":
					Dropdown::show('Entity');
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
               break;
			}
		break;
	}
	if (in_array($options['itemtype'], PluginConnectionsConnection_Item::getClasses(true))) {
      $PluginConnectionsConnection->dropdownConnections("plugin_connections_connections_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
   }
	return "";
}

function plugin_connections_MassiveActionsProcess($data) {
   
   $PluginConnectionsConnection=new PluginConnectionsConnection();
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
      case "plugin_connections_install":
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_connections_connections_id' => $key,
                              'items_id'      => $data["item_item"],
                              'itemtype'      => $data['itemtype']);
               if ($PluginConnectionsConnection_Item->can(-1,'w',$input)) {
                  $PluginConnectionsConnection_Item->add($input);
               }
            }
         }
			break;
      case "plugin_connections_desinstall":
         foreach ($data["item"] as $key => $val) {
           if ($val == 1) {
               $PluginConnectionsConnection_Item->deleteItemByConnectionsAndItem($key,$data['item_item'],$data['itemtype']);
            }
         }
			break;
      case "plugin_connections_transfert":
         if ($data['itemtype'] == 'PluginConnectionsConnection') {
				foreach ($data["item"] as $key => $val) {
					if ($val == 1) {
                  $PluginConnectionsConnection->getFromDB($key);

                  $type = PluginConnectionsConnectionType::transfer($PluginConnectionsConnection->fields["plugin_connections_connections_id"],
                                                                  $data['entities_id']);
                  $values["id"] = $key;
                  $values["plugin_connections_connections_id"] = $type;
                  $values["entities_id"] = $data['entities_id'];
                  $PluginConnectionsConnection->update($values);
					}
				}
			}
         break;
	}
}

//////////////////////////////

// Hook done on purge item case
function plugin_item_purge_connections($item) {

   $temp = new PluginConnectionsConnection_Item();
   $temp->clean(array('itemtype' => get_class($item),
                'items_id' => $item->getField('id')));
   return true;
}

// Define headings added by the plugin
function plugin_get_headings_connections($item,$withtemplate) {
	global $LANG;
  
	if (get_class($item)=='CronTask') {
    
		if ($item->getField('id')) {
         if ($item->getField('name')=="ConnectionsAlert") {
            return array(
               1 => $LANG['plugin_connections']['setup'][1],
            );
         }
		}
	} else if (in_array(get_class($item),PluginConnectionsConnection_Item::getClasses(true))||
		get_class($item)=='Profile') {
		// template case
		if ($item->getField('id') && !$withtemplate) {
         return array(
            1 => $LANG['plugin_connections']['title'][1],
            );
		}
	}

	return false;

}

// Define headings actions added by the plugin
function plugin_headings_actions_connections($item) {
  
	if (in_array(get_class($item),PluginConnectionsConnection_Item::getClasses(true))||
		get_class($item)=='Profile') {
		return array(
         1 => "plugin_headings_connections",
         );
	}
	return false;
}

// action heading
function plugin_headings_connections($item,$withtemplate=0) {
	global $CFG_GLPI;
  
   $PluginConnectionsProfile=new PluginConnectionsProfile();
   $PluginConnectionsConnection_Item=new PluginConnectionsConnection_Item();
  
   switch (get_class($item)) {
      case 'Profile' :
         if (!$PluginConnectionsProfile->getFromDBByProfile($item->getField('id')))
            $PluginConnectionsProfile->createAccess($item->getField('id'));
         $PluginConnectionsProfile->showForm($item->getField('id'), array('target' => $CFG_GLPI["root_doc"]."/plugins/connections/front/profile.form.php"));
         break;
      case 'Supplier':
         $PluginConnectionsConnection_Item->showPluginFromSupplier(get_class($item),$item->getField('id'));
         break;
      default :
         if (in_array(get_class($item), PluginConnectionsConnection_Item::getClasses(true))) {
            $PluginConnectionsConnection_Item->showPluginFromItems(get_class($item),$item->getField('id'));
         }
         break;
   }
}

function plugin_datainjection_populate_connections() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginConnectionsConnectionInjection'] = 'connections';
}

?>