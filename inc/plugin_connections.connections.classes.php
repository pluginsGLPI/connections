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

class plugin_connections extends CommonDBTM {

	function plugin_connections () {
		$this->table="glpi_plugin_connections";
		$this->type=PLUGIN_CONNECTIONS_TYPE;
		$this->entity_assign=true;
		$this->may_be_recursive=true;
		$this->dohistory=true;
	}
	
	
	//if connection purged
	function cleanDBonPurge($ID) {
		global $DB;

		$query = "DELETE from glpi_plugin_connections_device WHERE FK_connection = '$ID'";
		$DB->query($query);
		
		$query = "DELETE FROM glpi_doc_device WHERE FK_device = '$ID' AND device_type= '".PLUGIN_CONNECTIONS_TYPE."' ";
		$DB->query($query);
	}
	//if user purged
	function cleanItems($ID,$type) {
	
		global $DB;
			
		$query = "DELETE FROM glpi_plugin_connections_device WHERE FK_device = '$ID' AND device_type= '$type'";
		
		$DB->query($query);
		
	}

	function defineTabs($ID,$withtemplate){
		global $LANG,$LANG;
		$ong[1]=$LANG["title"][26];
		if (haveRight("document","r"))	
			$ong[9]=$LANG["Menu"][27];
		if (haveRight("notes","r"))	
			$ong[10]=$LANG["title"][37];
		$ong[12]=$LANG["title"][38];

		return $ong;
	}

	function showForm ($target,$ID,$withtemplate='') {

		GLOBAL $CFG_GLPI, $LANG;

		if (!plugin_connections_haveRight("connections","r")) return false;

		$spotted = false;
		if ($ID>0){
			if($this->can($ID,'r')){
				$spotted = true;
			}
		}else{
			if($this->can(-1,'w')){
				$spotted = true;
				$this->getEmpty();
			}
		}
				
		if ($spotted){
			
			$this->showTabs($ID, $withtemplate,$_SESSION['glpi_tab']);
			
			$canedit=$this->can($ID,'w');
			$canrecu=$this->can($ID,'recursive');
			
			echo "<form method='post' name=form action=\"$target\">";
			if (empty($ID)||$ID<0){
					echo "<input type='hidden' name='FK_entities' value='".$_SESSION["glpiactive_entity"]."'>";
				}
			
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID,'',1);
			
			echo "<tr><td class='tab_bg_1' valign='top'>";

			echo "<table cellpadding='2' cellspacing='2' border='0'>\n";

			echo "<tr><td>".$LANG['plugin_connections'][8].":	</td>";
			echo "<td>";
			autocompletionTextField("name","glpi_plugin_connections","name",$this->fields["name"],20,$this->fields["FK_entities"]);		
			echo "</td></tr>";
			
			echo "<tr><td>".$LANG['plugin_connections'][20].":	</td><td>";
			dropdownValue("glpi_dropdown_plugin_connections_type", "type", $this->fields["type"],1,$this->fields["FK_entities"]);
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_connections'][2].":	</td><td>";
				if ($canedit)
					dropdownValue("glpi_dropdown_locations", "location", $this->fields["location"],1,$this->fields["FK_entities"]);
				else
					echo getdropdownname("glpi_dropdown_locations",$this->fields["location"]);
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_connections'][11].":	</td>";
			echo "<td>";
			autocompletionTextField("bytes","glpi_plugin_connections","bytes",$this->fields["bytes"],20,$this->fields["FK_entities"]);		
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_connections'][28].":	</td><td>";
			dropdownValue("glpi_dropdown_state", "state", $this->fields["state"]);
			echo "</td></tr>";



			echo "</table>";

			echo "</td>\n";	

			echo "<td colspan='2' class='tab_bg_1' valign='top'>";

			echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
			echo $LANG['plugin_connections'][12].":	</td></tr>";
			echo "<tr><td align='center'><textarea cols='35' rows='4' name='comments' >".$this->fields["comments"]."</textarea>";
			echo "</td></tr></table>";

			echo "</td>";
			echo "</tr>";

			if ($canedit) {
				if (empty($ID)||$ID<0){
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='3'>";
					echo "<div align='center'><input type='submit' name='add' value=\"".$LANG["buttons"][8]."\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
	
				} else {
	
					echo "<tr>";
					echo "<td class='tab_bg_2'  colspan='3' valign='top'><div align='center'>";
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"".$LANG["buttons"][7]."\" class='submit' >";
	
					if ($this->fields["deleted"]=='0'){
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG["buttons"][6]."\" class='submit'></div>";
					}else {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"".$LANG["buttons"][21]."\" class='submit'>";
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$LANG["buttons"][22]."\" class='submit'></div>";
					}
					
					echo "</td>";
					echo "</tr>";
				}	
			}
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";

		} else {
			echo "<div align='center'><b>".$LANG['plugin_connections'][4]."</b></div>";
			return false;

		}
		return true;
	}
}

?>