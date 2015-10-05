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

class PluginConnectionsProfile extends CommonDBTM {

   static $rightname = 'profile';
   
   static function getTypeName($nb=0) {
      return __('Rights management', 'connections');
   }
   
   static function canCreate() {
      return Session::haveRight('profile', UPDATE);
   }

   static function canView() {
      return Session::haveRight('profile', READ);
   }
   
	//if profile deleted
	static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->cleanProfiles($prof->getField("id"));
   }
   
	function cleanProfiles($id) {
      global $DB;
      
		$query = "DELETE FROM `".$this->getTable()."`
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
      $this->add(array('profiles_id' => $ID));
   }
   
   static function changeProfile() {

      //Should we use Session::changeProfile() instead (available since GLPI v0.83)?
      $profil = new self();
      if ($profil->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_connections_profile"] = $profil->fields;
      } else {
         unset($_SESSION["glpi_plugin_connections_profile"]);
      }
   }

	function showForm($ID, $options = array()) {

		if (!Session::haveRight("profile", READ)) { //useless because "static $rightname = 'profile';" existed ?
         return false;
      }

		$prof = new Profile();
		if ($ID) {
			$this->getFromDBByProfile($ID);
			$prof->getFromDB($ID);
		}

      $this->showFormHeader($options);

		echo "<tr class='tab_bg_2'>";
		echo "<th colspan='4'>".__('Rights management', 'connections')." ".$prof->fields["name"]."</th>";
      echo "</tr>";

		echo "<tr class='tab_bg_2'>";
		echo "<td>".__("Connections", 'connections').":</td>";
      echo "<td>";

		if ($prof->fields['interface'] == 'helpdesk') {
         echo __('No access');
		} else {
         Profile::dropdownNoneReadWrite("connections",$this->fields["connections"],1,1,1);
		}
		echo "</td>";

		echo "<td>" . __('Linkable items to a ticket') . " - " . __("Connections", 'connections') . ":</td>"; //TODO : __('Linkable items to a ticket')
      echo "<td>";
      //TODO : PHP Notice: Undefined index: create_ticket
		//if ($prof->fields['create_ticket']) {
			Dropdown::showYesNo("open_ticket", $this->fields["open_ticket"]);
		//} else {
		//	echo Dropdown::getYesNo(0);
		//}
		echo "</td>";
		echo "</tr>";

		echo "<input type='hidden' name='id' value=".$this->fields["id"].">";
      
		$options['candel'] = false;
      $this->showFormButtons($options);
      Html::closeForm();
	}

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType() == 'Profile') {
         return __("Connections", 'connections');
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      $ID = $item->getField('id');

      switch ($item->getType()) {
         case 'Profile':
            $PluginConnectionsProfile = new self();
            if (!$PluginConnectionsProfile->getFromDBByProfile($ID)) {
               $PluginConnectionsProfile->createAccess($ID);
            }
            $PluginConnectionsProfile->showForm($ID); // array('target' => self::getFormURL())
            break;
         case 'Supplier': //Note : NOT USED
            PluginConnectionsConnection_Item::showPluginFromSupplier($item->getType(), $ID);
            break;
         default:
            $PluginConnectionsConnection_Item = new PluginConnectionsConnection_Item();
            if (in_array($item->getType(), PluginConnectionsConnection_Item::getClasses(true))) {
               $PluginConnectionsConnection_Item->showPluginFromItems($item->getType(), $ID);
            }
            break;
      }
      return true;
   }
}
