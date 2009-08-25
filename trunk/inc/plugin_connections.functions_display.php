<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

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
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function plugin_connections_showNetworking($instID,$search='') {
	global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

	if (!plugin_connections_haveRight("connections","r"))	return false;

	$plugin_connections=new plugin_connections();
	if ($plugin_connections->getFromDB($instID)){
		
		$canedit=$plugin_connections->can($instID,'w');
		
		$query = "SELECT DISTINCT device_type FROM glpi_plugin_connections_device WHERE FK_connection = '$instID' ORDER BY device_type";
		$result = $DB->query($query);
		$number = $DB->numrows($result);

		$i = 0;
	
		echo "<form method='post' name='connections_form' id='connections_form'  action=\"".$CFG_GLPI["root_doc"]."/plugins/connections/front/plugin_connections.form.php\">";
	
		echo "<br><div class='center'><table class='tab_cadrehov'>";
		echo "<tr><th colspan='".($canedit?6:5)."'>".$LANG['plugin_connections'][21].":</th></tr><tr>";
		if ($canedit) {
			echo "<th>&nbsp;</th>";
		}
		echo "<th>".$LANG["common"][17]."</th>";
		echo "<th>".$LANG["common"][16]."</th>";
		echo "<th>".$LANG["entity"][0]."</th>";
		echo "<th>".$LANG["common"][19]."</th>";
		echo "<th>".$LANG["common"][20]."</th>";
		echo "</tr>";
	
		$ci=new CommonItem();
		while ($i < $number) {
			$type=$DB->result($result, $i, "device_type");
			if (haveTypeRight($type,"r")){
				$column="name";
				if ($type==TRACKING_TYPE) $column="ID";
				if ($type==KNOWBASE_TYPE) $column="question";

				$query = "SELECT ".$LINK_ID_TABLE[$type].".*, glpi_plugin_connections_device.ID AS IDD, glpi_entities.ID AS entity "
				." FROM glpi_plugin_connections_device, ".$LINK_ID_TABLE[$type]
				." LEFT JOIN glpi_entities ON (glpi_entities.ID=".$LINK_ID_TABLE[$type].".FK_entities) "
				." WHERE ".$LINK_ID_TABLE[$type].".ID = glpi_plugin_connections_device.FK_device  AND glpi_plugin_connections_device.device_type='$type' AND glpi_plugin_connections_device.FK_connection = '$instID' "
				. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type])); 

				if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
					$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
				}
				$query.=" ORDER BY glpi_entities.completename, ".$LINK_ID_TABLE[$type].".$column";
				
				if ($result_linked=$DB->query($query))
					if ($DB->numrows($result_linked)){
						$ci->setType($type);
						while ($data=$DB->fetch_assoc($result_linked)){
							$ID="";
							if ($type==TRACKING_TYPE) $data["name"]=$LANG["job"][38]." ".$data["ID"];
							if ($type==KNOWBASE_TYPE) $data["name"]=$data["question"];
							if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
//							if($CFG_GLPI["view_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
								$name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data["ID"]."\">"
									.$data["name"]."$ID</a>";
	
							echo "<tr class='tab_bg_1'>";

							if ($canedit){
								echo "<td width='10'>";
								$sel="";
								if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
								echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
								echo "</td>";
							}
							echo "<td class='center'>".$ci->getType()."</td>";
							
							echo "<td class='center' ".(isset($data['deleted'])&&$data['deleted']?"class='tab_bg_2_2'":"").">".$name."</td>";
							echo "<td class='center'>".getDropdownName("glpi_entities",$data['entity'])."</td>";
							echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
							echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
							
							echo "</tr>";
						}
					}
			}
			$i++;
		}
	
		if ($canedit)	{
			echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
	
			echo "<input type='hidden' name='conID' value='$instID'>";
			dropdownAllItems("item",0,0,($plugin_connections->fields['recursive']?-1:$plugin_connections->fields['FK_entities']),plugin_connections_getTypes());							
			echo "</td>";
			echo "<td colspan='2' class='center' class='tab_bg_2'>";
			echo "<input type='submit' name='additem' value=\"".$LANG["buttons"][8]."\" class='submit'>";
			echo "</td></tr>";
			echo "</table></div>" ;
			
			echo "<div class='center'>";
			echo "<table width='80%' class='tab_glpi'>";
			echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('connections_form') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$instID&amp;select=all'>".$LANG["buttons"][18]."</a></td>";
		
			echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('connections_form') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$instID&amp;select=none'>".$LANG["buttons"][19]."</a>";
			echo "</td><td align='left' width='80%'>";
			echo "<input type='submit' name='deleteitem' value=\"".$LANG["buttons"][6]."\" class='submit'>";
			echo "</td>";
			echo "</table>";
		
			echo "</div>";


		}else{
	
			echo "</table></div>";
		}
		echo "</form>";
	}

}

//items
function plugin_connections_showAssociated($device_type,$ID,$withtemplate){

	global $DB,$CFG_GLPI, $LANG;
	
	$ci=new CommonItem(); 
	$ci->getFromDB($device_type,$ID); 
	$canread=$ci->obj->can($ID,'r'); 
	$canedit=$ci->obj->can($ID,'w');

	$query = "SELECT glpi_plugin_connections_device.ID AS entID,glpi_plugin_connections.* "
			."FROM glpi_plugin_connections_device,glpi_plugin_connections "
			." LEFT JOIN glpi_entities ON (glpi_entities.ID=glpi_plugin_connections.FK_entities) "
			." WHERE glpi_plugin_connections_device.FK_device = '".$ID."' AND glpi_plugin_connections_device.device_type = '".$device_type."' AND glpi_plugin_connections_device.FK_connection=glpi_plugin_connections.ID "
			. getEntitiesRestrictRequest(" AND ","glpi_plugin_connections",'','',isset($CFG_GLPI["recursive_type"][PLUGIN_CONNECTIONS_TYPE]));
	$query.= " ORDER BY glpi_plugin_connections.name ";
	
	$result = $DB->query($query);
	$number = $DB->numrows($result);

	if ($withtemplate!=2) echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/connections/front/plugin_connections.form.php\">";
	echo "<br><div align='center'><table class='tab_cadre_fixe'>";
	echo "<tr><th colspan='7'>".$LANG['plugin_connections'][27].":</th></tr>";
	echo "<tr><th>".$LANG['plugin_connections'][8]."</th>";
	echo "<th>".$LANG["entity"][0]."</th>";
	echo "<th>".$LANG['plugin_connections'][20]."</th>";
	echo "<th>".$LANG['plugin_connections'][11]."</th>";
	echo "<th>".$LANG['plugin_connections'][2]."</th>";
	echo "<th>".$LANG['plugin_connections'][28]."</th>";
	if(plugin_connections_haveRight("connections","w"))
		echo "<th width='10%'>&nbsp;</th>";
	echo "</tr>";
	$used=array();
	while ($data=$DB->fetch_array($result)){
		$connectionsID=$data["ID"];
		$used[]=$connectionsID;
		echo "<tr class='tab_bg_1".($data["deleted"]=='1'?"_2":"")."'>";
		
		if ($withtemplate!=3 && $canread && (in_array($data['FK_entities'],$_SESSION['glpiactiveentities']) || $data["recursive"])){
			echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/connections/front/plugin_connections.form.php?ID=".$data["ID"]."'>".$data["name"];
			if ($CFG_GLPI["view_ID"]) echo " (".$data["ID"].")";
			echo "</a></td>";
		} else {
			echo "<td class='center'>".$data["name"];
			if ($CFG_GLPI["view_ID"]) echo " (".$data["ID"].")";
			echo "</td>";
		}
		
		echo "<td class='center'>".getDropdownName("glpi_entities",$data['FK_entities'])."</td>";
		
		echo "<td>".getdropdownname("glpi_dropdown_plugin_connections_type","".$data["type"])."</td>";
		echo "<td align='center'>".$data["bytes"]."</td>";
		echo "<td>".getdropdownname("glpi_dropdown_locations","".$data["location"])."</td>";
		echo "<td>".getdropdownname("glpi_dropdown_state","".$data["state"])."</td>";
		if(plugin_connections_haveRight("connections","w"))
			echo "<td align='center' class='tab_bg_2'><a href='".$CFG_GLPI["root_doc"]."/plugins/connections/front/plugin_connections.form.php?deleteconnections=deleteconnections&amp;ID=".$data["entID"]."'>".$LANG["buttons"][6]."</a></td>";
		echo "</tr>";
	}
	
	if ($canedit){
		
		$ci=new CommonItem();
		$entities=""; 
		if ($ci->getFromDB($device_type,$ID) && isset($ci->obj->fields["FK_entities"])) {                
		
			if (isset($ci->obj->fields["recursive"]) && $ci->obj->fields["recursive"]) { 
				$entities = getEntitySons($ci->obj->fields["FK_entities"]); 
			} else { 
				$entities = $ci->obj->fields["FK_entities"]; 
			} 
		} 
		$limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_connections",'',$entities,true);
		
		$q="SELECT count(*) FROM glpi_plugin_connections WHERE deleted='0' $limit";
		$result = $DB->query($q);
		$nb = $DB->result($result,0,0);

		if ($withtemplate<2&&$nb>count($used)){
			if(plugin_connections_haveRight("connections","w")){
				echo "<tr class='tab_bg_1'><td align='right' colspan='6'>";
				echo "<input type='hidden' name='item' value='$ID'><input type='hidden' name='type' value='$device_type'>";
				plugin_connections_dropdownconnections("conID",$entities,$used);
				echo "</td><td align='center'>";
				echo "<input type='submit' name='additem' value=\"".$LANG["buttons"][8]."\" class='submit'>";
				echo "</td>";
	
				echo "</tr>";
			}
		}
	}
	
	echo "</table></div>";
	echo "</form>";

}

?>