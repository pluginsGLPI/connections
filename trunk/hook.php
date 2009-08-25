<?php
/*
 * @version $Id: hook.php 7355 2008-10-03 15:31:00Z moyo $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copynetwork (C) 2003-2006 by the INDEPNET Development Team.

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
// Original Author of file: DOMBRE Julien
// Purpose of file:
// ----------------------------------------------------------------------

foreach (glob(GLPI_ROOT . '/plugins/connections/inc/*.php') as $file)
	include_once ($file);

function plugin_connections_install(){
	global $DB, $LANG, $CFG_GLPI;
		
	include_once (GLPI_ROOT."/inc/profile.class.php");
		
	if(!TableExists("glpi_application") && !TableExists("glpi_plugin_connections") )
	{
		plugin_connections_installing("1.5.0");		
	} elseif(!FieldExists("glpi_plugin_connections","recursive")) {		
		plugin_connections_update("1.4");
		plugin_connections_update("1.5.0");	
	}elseif(TableExists("glpi_plugin_connections_profiles") && FieldExists("glpi_plugin_connections_profiles","interface")) {	
		plugin_connections_update("1.5.0");		
	}	
	plugin_connections_createfirstaccess($_SESSION['glpiactiveprofile']['ID']);
	return true;
}

function plugin_connections_uninstall(){
	global $DB;
	
	$tables = array("glpi_plugin_connections",
					"glpi_dropdown_plugin_connections_type",
					"glpi_plugin_connections_profiles",
					"glpi_plugin_connections_device");
					
	foreach($tables as $table)				
		$DB->query("DROP TABLE `$table`;");
		
	$query="DELETE FROM glpi_display WHERE type='".PLUGIN_CONNECTIONS_TYPE."';";
	$DB->query($query) or die($DB->error());
	
	$query="DELETE FROM glpi_doc_device WHERE device_type='".PLUGIN_CONNECTIONS_TYPE."';";
	$DB->query($query) or die($DB->error());
	
	$query="DELETE FROM glpi_bookmark WHERE device_type='".PLUGIN_CONNECTIONS_TYPE."';";
	$DB->query($query) or die($DB->error());
	
	$query="DELETE FROM glpi_history WHERE device_type='".PLUGIN_CONNECTIONS_TYPE."';";
	$DB->query($query) or die($DB->error());
	
	if (TableExists("glpi_plugin_data_injection_models"))
		$DB->query("DELETE FROM glpi_plugin_data_injection_models, glpi_plugin_data_injection_mappings, glpi_plugin_data_injection_infos USING glpi_plugin_data_injection_models, glpi_plugin_data_injection_mappings, glpi_plugin_data_injection_infos
		WHERE glpi_plugin_data_injection_models.device_type=".PLUGIN_CONNECTIONS_TYPE."
		AND glpi_plugin_data_injection_mappings.model_id=glpi_plugin_data_injection_models.ID
		AND glpi_plugin_data_injection_infos.model_id=glpi_plugin_data_injection_models.ID");
	
	plugin_init_connections();
	cleanCache("GLPI_HEADER_".$_SESSION["glpiID"]);

	return true;
}

// Define dropdown relations
function plugin_connections_getDatabaseRelations(){
	
	$plugin = new Plugin();
	
	if ($plugin->isActivated("connections"))
		return array("glpi_dropdown_plugin_connections_type"=>array("glpi_plugin_connections"=>"type"),
					 "glpi_entities"=>array("glpi_plugin_connections"=>"FK_entities",
											"glpi_dropdown_plugin_connections_type"=>"FK_entities"));
	else
		return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_connections_getDropdown(){
	// Table => Name
	global $LANG;	
	$plugin = new Plugin();	
	if ($plugin->isActivated("connections"))
		return array("glpi_dropdown_plugin_connections_type"=>$LANG['plugin_connections']['setup'][2]);
	else
		return array();
}

function plugin_item_delete_connections($parm){
	
	if (isset($parm["type"]))
		switch ($parm["type"])
		{
			case TRACKING_TYPE :
				$plugin_connections=new plugin_connections;
				$plugin_connections->cleanItems($parm['ID'], $parm['type']);
				return true;
				break;
		}
		
	return false;
}

// Define headings added by the plugin
function plugin_get_headings_connections($type,$ID,$withtemplate){
	global $LANG;
	
	if (in_array($type,plugin_connections_getTypes())||
		$type==PROFILE_TYPE) {
		// template case
		if ($ID>0 && !$withtemplate){
				return array(
					1 => $LANG['plugin_connections'][1],
					);
		}
	}
	
	return false;
}

// Define headings actions added by the plugin	 
function plugin_headings_actions_connections($type){
		
	if (in_array($type,plugin_connections_getTypes())||
		$type==PROFILE_TYPE) {
		return array(
			1 => "plugin_headings_connections",
		);
	}
	return false;
	
}

// action heading
function plugin_headings_connections($type,$ID,$withtemplate=0){
	global $CFG_GLPI;
	
	switch ($type){
			case PROFILE_TYPE :
				$prof=new plugin_connections_Profile();	
				if (!$prof->GetfromDB($ID))
					plugin_connections_createaccess($ID);
				$prof->showForm($CFG_GLPI["root_doc"]."/plugins/connections/front/plugin_connections.profile.php",$ID);
				break;
			default :
				if (in_array($type, plugin_connections_getTypes())){
					echo "<div align='center'>";
					echo plugin_connections_showAssociated($type,$ID,$withtemplate);
					echo "</div>";
				}
			break;
		}
}

// Hook done on delete item case
function plugin_pre_item_delete_connections($input){
	if (isset($input["_item_type_"]))
		switch ($input["_item_type_"]){
			case PROFILE_TYPE :
				// Manipulate data if needed 
				$plugin_connections_Profile=new plugin_connections_Profile;
				$plugin_connections_Profile->cleanProfiles($input["ID"]);
				break;
		}
	return $input;
}

// Hook done on purge item case
function plugin_item_purge_connections($parm){
		
	if (in_array($parm["type"], plugin_connections_getTypes())
		&& $parm["type"]!=TRACKING_TYPE) {
		$plugin_connections=new plugin_connections;
		$plugin_connections->cleanItems($parm["ID"],$parm["type"]);
		return true;
	}else
		return false;
	
}


////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_connections_getSearchOption(){
	global $LANG;
	$sopt=array();
	if (plugin_connections_haveRight("connections","r")){
		// Part header
		$sopt[PLUGIN_CONNECTIONS_TYPE]['common']=$LANG['plugin_connections'][1];
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][1]['table']='glpi_plugin_connections';
		$sopt[PLUGIN_CONNECTIONS_TYPE][1]['field']='name';
		$sopt[PLUGIN_CONNECTIONS_TYPE][1]['linkfield']='name';
		$sopt[PLUGIN_CONNECTIONS_TYPE][1]['name']=$LANG['plugin_connections'][8];
		$sopt[PLUGIN_CONNECTIONS_TYPE][1]['datatype']='itemlink';
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][2]['table']='glpi_dropdown_plugin_connections_type';
		$sopt[PLUGIN_CONNECTIONS_TYPE][2]['field']='name';
		$sopt[PLUGIN_CONNECTIONS_TYPE][2]['linkfield']='type';
		$sopt[PLUGIN_CONNECTIONS_TYPE][2]['name']=$LANG['plugin_connections'][20];
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][3]['table']='glpi_plugin_connections';
		$sopt[PLUGIN_CONNECTIONS_TYPE][3]['field']='bytes';
		$sopt[PLUGIN_CONNECTIONS_TYPE][3]['linkfield']='bytes';
		$sopt[PLUGIN_CONNECTIONS_TYPE][3]['name']=$LANG['plugin_connections'][11];
	
		$sopt[PLUGIN_CONNECTIONS_TYPE][4]['table']='glpi_dropdown_locations';
		$sopt[PLUGIN_CONNECTIONS_TYPE][4]['field']='completename';
		$sopt[PLUGIN_CONNECTIONS_TYPE][4]['linkfield']='location';
		$sopt[PLUGIN_CONNECTIONS_TYPE][4]['name']=$LANG['plugin_connections'][2];
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][5]['table']='glpi_dropdown_state';
		$sopt[PLUGIN_CONNECTIONS_TYPE][5]['field']='name';
		$sopt[PLUGIN_CONNECTIONS_TYPE][5]['linkfield']='state';
		$sopt[PLUGIN_CONNECTIONS_TYPE][5]['name']=$LANG['plugin_connections'][28];
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][6]['table']='glpi_plugin_connections';
		$sopt[PLUGIN_CONNECTIONS_TYPE][6]['field']='comments';
		$sopt[PLUGIN_CONNECTIONS_TYPE][6]['linkfield']='comments';
		$sopt[PLUGIN_CONNECTIONS_TYPE][6]['name']=$LANG['plugin_connections'][12];
		$sopt[PLUGIN_CONNECTIONS_TYPE][6]['datatype']='text';
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][7]['table']='glpi_plugin_connections_device';
		$sopt[PLUGIN_CONNECTIONS_TYPE][7]['field']='FK_device';
		$sopt[PLUGIN_CONNECTIONS_TYPE][7]['linkfield']='';
		$sopt[PLUGIN_CONNECTIONS_TYPE][7]['name']=$LANG['plugin_connections'][7];
		$sopt[PLUGIN_CONNECTIONS_TYPE][7]['forcegroupby']=true;
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][10]['table']='glpi_plugin_connections';
		$sopt[PLUGIN_CONNECTIONS_TYPE][10]['field']='recursive';
		$sopt[PLUGIN_CONNECTIONS_TYPE][10]['linkfield']='recursive';
		$sopt[PLUGIN_CONNECTIONS_TYPE][10]['name']=$LANG["entity"][9];
		$sopt[PLUGIN_CONNECTIONS_TYPE][10]['datatype']='bool';
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][30]['table']='glpi_plugin_connections';
		$sopt[PLUGIN_CONNECTIONS_TYPE][30]['field']='ID';
		$sopt[PLUGIN_CONNECTIONS_TYPE][30]['linkfield']='';
		$sopt[PLUGIN_CONNECTIONS_TYPE][30]['name']=$LANG["common"][2];
		
		$sopt[PLUGIN_CONNECTIONS_TYPE][80]['table']='glpi_entities';
		$sopt[PLUGIN_CONNECTIONS_TYPE][80]['field']='completename';
		$sopt[PLUGIN_CONNECTIONS_TYPE][80]['linkfield']='FK_entities';
		$sopt[PLUGIN_CONNECTIONS_TYPE][80]['name']=$LANG["entity"][0];
		
		/*$sopt[PLUGIN_CONNECTIONS_TYPE]['tracking']=$LANG['title'][24];

		$sopt[PLUGIN_CONNECTIONS_TYPE][60]['table']='glpi_tracking';
		$sopt[PLUGIN_CONNECTIONS_TYPE][60]['field']='count';
		$sopt[PLUGIN_CONNECTIONS_TYPE][60]['linkfield']='';
		$sopt[PLUGIN_CONNECTIONS_TYPE][60]['name']=$LANG['stats'][13];
		$sopt[PLUGIN_CONNECTIONS_TYPE][60]['forcegroupby']=true;
		$sopt[PLUGIN_CONNECTIONS_TYPE][60]['usehaving']=true;
		$sopt[PLUGIN_CONNECTIONS_TYPE][60]['datatype']='number';*/
		
		$sopt[NETWORKING_TYPE][2710]['table']='glpi_plugin_connections';
		$sopt[NETWORKING_TYPE][2710]['field']='name';
		$sopt[NETWORKING_TYPE][2710]['linkfield']='';
		$sopt[NETWORKING_TYPE][2710]['name']=$LANG['plugin_connections'][1]." - ".$LANG['plugin_connections'][8];
		$sopt[NETWORKING_TYPE][2710]['forcegroupby']='1';
		$sopt[NETWORKING_TYPE][2710]['datatype']='itemlink';
		$sopt[NETWORKING_TYPE][2710]['itemlink_type']=PLUGIN_CONNECTIONS_TYPE;
		
		$sopt[NETWORKING_TYPE][2711]['table']='glpi_dropdown_plugin_connections_type';
		$sopt[NETWORKING_TYPE][2711]['field']='name';
		$sopt[NETWORKING_TYPE][2711]['linkfield']='';
		$sopt[NETWORKING_TYPE][2711]['name']=$LANG['plugin_connections'][1]." - ".$LANG['plugin_connections'][20];
	}	
	return $sopt;
}

//for search
function plugin_connections_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables){

	switch ($new_table){
		
		case "glpi_plugin_connections_device" : //from appweb list
			return " LEFT JOIN $new_table ON ($ref_table.ID = $new_table.FK_connection) ";
			break;
		case "glpi_plugin_connections" : // From items
			$out= " LEFT JOIN glpi_plugin_connections_device ON ($ref_table.ID = glpi_plugin_connections_device.FK_device AND glpi_plugin_connections_device.device_type=$type) ";
			$out.= " LEFT JOIN glpi_plugin_connections ON (glpi_plugin_connections.ID = glpi_plugin_connections_device.FK_connection) ";
			return $out;
			break;
		case "glpi_dropdown_plugin_connections_type" : // From items
			$out=addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_connections",$linkfield);
			$out.= " LEFT JOIN glpi_dropdown_plugin_connections_type ON (glpi_dropdown_plugin_connections_type.ID = glpi_plugin_connections.type) ";
			return $out;
			break;
	}
	
	return "";
}

//force groupby for multible links to items
function plugin_connections_forceGroupBy($type){

	return true;					// ????????
	switch ($type){
		case PLUGIN_CONNECTIONS_TYPE:
			return true;
			break;
		
	}
	return false;
}


function plugin_connections_giveItem($type,$ID,$data,$num){
	global $CFG_GLPI, $INFOFORM_PAGES, $LANG,$SEARCH_OPTION,$LINK_ID_TABLE,$DB;

	$table=$SEARCH_OPTION[$type][$ID]["table"];
	$field=$SEARCH_OPTION[$type][$ID]["field"];
	
	switch ($table.'.'.$field){	
		case "glpi_plugin_connections_device.FK_device" :
			$query_device = "SELECT DISTINCT device_type FROM glpi_plugin_connections_device WHERE FK_connection = '".$data['ID']."' ORDER BY device_type";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);
			$y = 0;
			$out='';
			$connection=$data['ID'];
			if ($number_device>0){
				$ci=new CommonItem();
				while ($y < $number_device) {
					$column="name";
					if ($type==TRACKING_TYPE) $column="ID";
					$type=$DB->result($result_device, $y, "device_type");
					if (!empty($LINK_ID_TABLE[$type])){
						$query = "SELECT ".$LINK_ID_TABLE[$type].".*, glpi_plugin_connections_device.ID AS IDD, glpi_entities.ID AS entity "
						." FROM glpi_plugin_connections_device, ".$LINK_ID_TABLE[$type]
						." LEFT JOIN glpi_entities ON (glpi_entities.ID=".$LINK_ID_TABLE[$type].".FK_entities) "
						." WHERE ".$LINK_ID_TABLE[$type].".ID = glpi_plugin_connections_device.FK_device  AND glpi_plugin_connections_device.device_type='$type' AND glpi_plugin_connections_device.FK_connection = '".$connection."' "
						. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type])); 
	
						if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
							$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
					}
					$query.=" ORDER BY glpi_entities.completename, ".$LINK_ID_TABLE[$type].".$column";
					
					if ($result_linked=$DB->query($query))
						if ($DB->numrows($result_linked)){
							$ci->setType($type);
							while ($data=$DB->fetch_assoc($result_linked)){
								$out.=$ci->getType()." - ";
								$ID="";
								if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
								$name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data["ID"]."\">"
								.$data["name"]."$ID</a>";
								$out.=$name."<br>";
								
							}
						}else
							$out=' ';
					}else
						$out=' ';
					$y++;
				}
			}else
				$out=' ';
		return $out;
		break;		
	}
	return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_connections_MassiveActions($type){
	global $LANG;

	switch ($type){
		case PLUGIN_CONNECTIONS_TYPE:
			return array(
				// GLPI core one
				"add_document"=>$LANG['document'][16],
				// association with glpi items
				"plugin_connections_install"=>$LANG['plugin_connections']['setup'][9],	
				"plugin_connections_desinstall"=>$LANG['plugin_connections']['setup'][10],
				//tranfer connections to another entity
				"plugin_connections_transfert"=>$LANG['buttons'][48],
				);
			break;
		default:
			//adding connections from items lists
			if (in_array($type, plugin_connections_getTypes())) {
				return array("plugin_connections_add_item"=>$LANG['plugin_connections']['setup'][19]);
			}
			break;
	}	
	return array();
}

function plugin_connections_MassiveActionsDisplay($type,$action){
	global $LANG,$CFG_GLPI;

	switch ($type){		
		case PLUGIN_CONNECTIONS_TYPE:
			switch ($action){
				
				// No case for add_document : use GLPI core one
				case "plugin_connections_install":
					dropdownAllItems("item_item",0,0,-1,plugin_connections_getTypes());
					echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_connections_desinstall":
					dropdownAllItems("item_item",0,0,-1,plugin_connections_getTypes());
				echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_connections_transfert":
					dropdownValue("glpi_entities", "FK_entities", '');
				echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
			}
		break;
	}
	if (in_array($type, plugin_connections_getTypes())) {
				plugin_connections_dropdownconnections("conID");
				echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
		}
	return "";
}

function plugin_connections_MassiveActionsProcess($data){
	global $LANG,$DB;

	switch ($data['action']){
		case "plugin_connections_add_item":
			$plugin_connections=new plugin_connections();
			$ci2=new CommonItem();
			if ($plugin_connections->getFromDB($data['conID'])){
				foreach ($data["item"] as $key => $val){
					if ($val==1) {
						// Items exists ?
						if ($ci2->getFromDB($data["device_type"],$key)){
							// Entity security
							 if (!isset($plugin_connections->obj->fields["FK_entities"])
								||$ci2->obj->fields["FK_entities"]==$plugin_connections->obj->fields["FK_entities"]
								||($ci2->obj->fields["recursive"] && in_array($ci2->obj->fields["FK_entities"], getEntityAncestors($plugin_connections->obj->fields["FK_entities"])))){
 	                         plugin_connections_addDevice($data["conID"],$key,$data['device_type']);
							}
						}
					}
				}
			}
		break;
		
		case "plugin_connections_install":
			if ($data['device_type']==PLUGIN_CONNECTIONS_TYPE){

			$plugin_connections=new plugin_connections();
			$ci=new CommonItem();
			foreach ($data["item"] as $key => $val){
				if ($val==1){
					// Items exists ?
					if ($plugin_connections->getFromDB($key)){
						// Entity security
						if ($ci->getFromDB($data['type'],$data['item_item'])){
							if (!isset($plugin_connections->obj->fields["FK_entities"])
								||$ci->obj->fields["FK_entities"]==$plugin_connections->obj->fields["FK_entities"]
								||($ci->obj->fields["recursive"] && in_array($ci->obj->fields["FK_entities"], getEntityAncestors($plugin_connections->obj->fields["FK_entities"])))){
								plugin_connections_addDevice($key,$data["item_item"],$data['type']); 
							}
						}
					}
				}
			}
		}
	break;
	case "plugin_connections_desinstall":
			if ($data['device_type']==PLUGIN_CONNECTIONS_TYPE){
				foreach ($data["item"] as $key => $val){
					if ($val==1){
						$query="DELETE 
								FROM glpi_plugin_connections_device 
								WHERE device_type='".$data['type']."' 
								AND FK_device='".$data['item_item']."' 
								AND FK_connection = '$key'";
						$DB->query($query);
				}
			}
		}
	break;
	case "plugin_connections_transfert":
		if ($data['device_type']==PLUGIN_CONNECTIONS_TYPE){
			foreach ($data["item"] as $key => $val){
				if ($val==1){
					$plugin_connections=new plugin_connections;
					$plugin_connections->getFromDB($key);

					$type=plugin_connections_transferDropdown($plugin_connections->fields["type"],$data['FK_entities']);
					$query="UPDATE glpi_plugin_connections 
							SET type = '".$type."' 
							WHERE ID ='$key'";
					$DB->query($query);
					
					$query="UPDATE glpi_plugin_connections 
							SET FK_entities = '".$data['FK_entities']."' 
							WHERE ID ='$key'";
					$DB->query($query);
				}
			}
		}	
		break;
	}
}

//////////////////////////////

function plugin_connections_data_injection_variables() {
	global $IMPORT_PRIMARY_TYPES, $DATA_INJECTION_MAPPING, $LANG, $IMPORT_TYPES,$DATA_INJECTION_INFOS;
	
	$plugin = new Plugin();
	
	if (plugin_connections_haveRight("connections","w") && $plugin->isActivated("connections")){
	
		if (!in_array(PLUGIN_CONNECTIONS_TYPE, $IMPORT_PRIMARY_TYPES)) {
			
			//Add types of objects to be injected by data_injection plugin
			array_push($IMPORT_PRIMARY_TYPES, PLUGIN_CONNECTIONS_TYPE);	
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['name']['table'] = 'glpi_plugin_connections';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['name']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['name']['name'] = $LANG["connections"][8];
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['name']['type'] = "text";
	
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_connections_type';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['type']['linkfield'] = 'type';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['type']['name'] = $LANG["connections"][20];
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['type']['table_type'] = "dropdown";
			
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['bytes']['table'] = 'glpi_plugin_connections';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['bytes']['field'] = 'bytes';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['bytes']['name'] = $LANG["connections"][11];
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['bytes']['type'] = "text";
			
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['location']['table'] = 'glpi_dropdown_locations';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['location']['field'] = 'completename';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['location']['linkfield'] = 'location';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['location']['name'] = $LANG["connections"][2];
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['location']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['location']['table_type'] = "dropdown";
			
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['state']['table'] = 'glpi_dropdown_state';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['state']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['state']['linkfield'] = 'state';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['state']['name'] = $LANG["connections"][28];
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['state']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['state']['table_type'] = "dropdown";
			
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['comments']['table'] = 'glpi_plugin_connections';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['comments']['field'] = 'comments';
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['comments']['name'] = $LANG["connections"][12];
			$DATA_INJECTION_MAPPING[PLUGIN_CONNECTIONS_TYPE]['comments']['type'] = "text";
	
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['type']['table'] = 'glpi_dropdown_plugin_connections_type';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['type']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['type']['linkfield'] = 'type';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['type']['name'] = $LANG["connections"][20];
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['type']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['type']['table_type'] = "dropdown";
			
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['bytes']['table'] = 'glpi_plugin_connections';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['bytes']['field'] = 'bytes';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['bytes']['name'] = $LANG["connections"][11];
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['bytes']['type'] = "text";
			
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['location']['table'] = 'glpi_dropdown_locations';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['location']['field'] = 'completename';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['location']['linkfield'] = 'location';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['location']['name'] = $LANG["connections"][2];
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['location']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['location']['table_type'] = "dropdown";
			
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['state']['table'] = 'glpi_dropdown_state';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['state']['field'] = 'name';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['state']['linkfield'] = 'state';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['state']['name'] = $LANG["connections"][28];
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['state']['type'] = "text";
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['state']['table_type'] = "dropdown";
			
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['comments']['table'] = 'glpi_plugin_connections';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['comments']['field'] = 'comments';
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['comments']['name'] = $LANG["connections"][12];
			$DATA_INJECTION_INFOS[PLUGIN_CONNECTIONS_TYPE]['comments']['type'] = "text";
			
		}
	}
}
?>