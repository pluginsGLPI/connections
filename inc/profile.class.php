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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginConnectionsProfile extends CommonDBTM {
   
	static function getTypeName() {
      global $LANG;

      return $LANG['plugin_connections']['profile'][0];
   }
   
   function canCreate() {
      return Session::haveRight('profile', 'w');
   }

   function canView() {
      return Session::haveRight('profile', 'r');
   }
   
	//if profile deleted
	static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->cleanProfiles($prof->getField("id"));
   }
   
	function cleanProfiles($id) {
      global $DB;
      
		$query = "DELETE 
				FROM `".$this->getTable()."`
				WHERE `profiles_id` = '".$id."' ";
		
		$DB->query($query);
	}
   
   function getFromDBByProfile($profiles_id) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."`
					WHERE `profiles_id` = '" . $profiles_id . "' ";
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
	
	static function createFirstAccess($ID) {
      
      $myProf = new self();
      if (!$myProf->getFromDBByProfile($ID)) {

         $myProf->add(array(
            'profiles_id' => $ID,
            'connections' => 'w',
            'open_ticket' => '1'));
            
      }
   }
	
	function createAccess($ID) {

      $this->add(array(
      'profiles_id' => $ID));
   }
   
   static function changeProfile() {
      //Should we use Session::changeProfile() instead (available since GLPI v0.83)?
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id']))
         $_SESSION["glpi_plugin_connections_profile"]=$prof->fields;
      else
         unset($_SESSION["glpi_plugin_connections_profile"]);
   }

	function showForm ($ID, $options=array()) {
		global $LANG;

		if (!Session::haveRight("profile","r")) return false;

		$prof = new Profile();
		if ($ID) {
			$this->getFromDBByProfile($ID);
			$prof->getFromDB($ID);
		}

      $this->showFormHeader($options);

		echo "<tr class='tab_bg_2'>";
		
		echo "<th colspan='4'>".$LANG['plugin_connections']['profile'][0]." ".$prof->fields["name"]."</th>";
      
      echo "</tr>";
		echo "<tr class='tab_bg_2'>";
		
		echo "<td>".$LANG['plugin_connections']['profile'][1].":</td><td>";

		if ($prof->fields['interface']!='helpdesk') {
			Profile::dropdownNoneReadWrite("connections",$this->fields["connections"],1,1,1);
		} else {
			echo $LANG['profiles'][12]; // No access;
		}
		echo "</td>";

		echo "<td>" . $LANG['setup'][352] . " - " . $LANG['plugin_connections']['title'][1] . ":</td><td>";
		if ($prof->fields['create_ticket']) {
			Dropdown::showYesNo("open_ticket",$this->fields["open_ticket"]);
		} else {
			echo Dropdown::getYesNo(0);
		}
		echo "</td>";
		echo "</tr>";

		echo "<input type='hidden' name='id' value=".$this->fields["id"].">";
      
		$options['candel'] = false;
      $this->showFormButtons($options);
	}
}

?>