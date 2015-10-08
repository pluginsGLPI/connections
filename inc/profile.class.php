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

class PluginConnectionsProfile extends Profile {

   static $rightname = "profile";

   static function getAllRights() {
      global $LANG;

      $rights = array(
          array('itemtype'  => 'PluginConnectionsConnection',
                'label'     => $LANG['plugin_connections']['title'][1],
                'field'     => 'plugin_connections_connection'));
      return $rights;
   }
   
   /**
    * Clean profiles_id from plugin's profile table
    *
    * @param $ID
   **/
   function cleanProfiles($ID) {
      global $DB;
      $query = "DELETE FROM `glpi_profiles` 
                WHERE `profiles_id`='$ID' 
                   AND `name` LIKE '%plugin_connections%'";
      $DB->query($query);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType() == 'Profile') {
         if ($item->getField('interface') == 'central') {
            return $LANG['plugin_connections']['title'][1];
         }
         return '';
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         $profile = new self();
         $ID   = $item->getField('id');
         //In case there's no right for this profile, create it
         self::addDefaultProfileInfos($item->getID(), 
                                      array('plugin_connections_connection' => 0));
         $profile->showForm($ID);
      }
      return true;
   }


   /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   /**
    * @param $ID  integer
    */
   static function createFirstAccess($profiles_id) {
      include_once(GLPI_ROOT."/plugins/connections/inc/profile.class.php");
      foreach (self::getAllRights() as $right) {
         self::addDefaultProfileInfos($profiles_id, 
                                    array('plugin_connections_connection' => ALLSTANDARDRIGHT));
      }
   }


   static function migrateProfiles() {
      global $DB;
      $profiles = getAllDatasFromTable('glpi_plugin_connections_profiles');
      foreach ($profiles as $id => $profile) {
         $query = "SELECT `id` FROM `glpi_profiles` WHERE `name`='".$profile['name']."'";
         $result = $DB->query($query);
         if ($DB->numrows($result) == 1) {
            $id = $DB->result($result, 0, 'id');
            switch ($profile['connections']) {
               case 'r' :
                  $value = READ;
                  break;
               case 'w':
                  $value = ALLSTANDARDRIGHT;
                  break;
               case 0:
               default:
                  $value = 0;
                  break;
            }
            self::addDefaultProfileInfos($id, array('plugin_connections_connection' => $value));
         }
      }
   }
   
    /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = self::getAllRights();
      $profile->displayRightsChoiceMatrix(self::getAllRights(), 
                                          array('canedit'       => $canedit,
                                                'default_class' => 'tab_bg_2',
                                                'title'         => __('General')));
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }
}
