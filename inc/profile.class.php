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

class PluginConnectionsProfile extends CommonDBTM
{
   static $rightname = 'profile';

   public static function getTypeName($nb = 0)
   {
      global $LANG;

      return $LANG['plugin_connections']['profile'][0];
   }

   //if profile deleted
   public static function purgeProfiles(Profile $prof)
   {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }

   public function getFromDBByProfile($profiles_id)
   {
      global $DB;

      $query = "SELECT * FROM `" . $this->getTable() . "`
                WHERE `profiles_id` = '" . (int) $profiles_id . "';";
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

   public static function createFirstAccess($ID)
   {
      $myProf = new self();
      if (!$myProf->getFromDBByProfile($ID)) {

         $myProf->add(array(
            'profiles_id' => $ID,
            'connections' => ALLSTANDARDRIGHT
         ));
      }
   }

   public function createAccess($ID)
   {
      $this->add(array(
         'profiles_id' => $ID,
      ));
   }

   public static function changeProfile()
   {
      $profile = new self();
      if ($profile->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpiactiveprofile"]['connections']         = $profile->getField('connections');
      } else {
         unset($_SESSION['glpiactiveprofile']['connections']);
      }
   }

   public static function getAllRights()
   {
      global $LANG;
      
      return array(
         array(
            'itemtype' => 'PluginConnectionsProfile',
            'label'    =>  $LANG['plugin_connections']['title'][1],
            'field'    => 'connections'
         ),
      );
   }

   public function showForm ($ID, $options=array())
   {
      $profile = new Profile();
      $profile->getFromDB($ID);

      if ($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE))) {
         echo "<form action='" . $profile->getFormURL() . "' method='post'>";
      }

      $profile = new Profile();
      $profile->getFromDB($ID);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, array(
         'canedit'       => $canedit,
         'default_class' => 'tab_bg_2',
         'title'         => $this->getTypeName(),
      ));

      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $ID));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      global $LANG;

      if ($item->getType() == 'Profile') {
         return $LANG['plugin_connections']['title'][1];
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      global $CFG_GLPI;

      $PluginConnectionsProfile         = new self();
      $PluginConnectionsConnection_Item = new PluginConnectionsConnection_Item();

      switch ($item->getType()) {
         case 'Profile':
            if (!$PluginConnectionsProfile->getFromDBByProfile($item->getField('id'))) {
               $PluginConnectionsProfile->createAccess($item->getField('id'));
            }
            $PluginConnectionsProfile->showForm(
               $item->getField('id'),
               array(
                  'target' => $CFG_GLPI["root_doc"] . "/plugins/connections/front/profile.form.php"
               )
            );
            break;
         case 'Supplier':
            $PluginConnectionsConnection_Item->showPluginFromSupplier(
               $item->getType(),
               $item->getField('id')
            );
            break;
         default:
            if (in_array($item->getType(), PluginConnectionsConnection_Item::getClasses(true))) {
               $PluginConnectionsConnection_Item->showPluginFromItems(
                  $item->getType(),
                  $item->getField('id')
               );
            }
            break;
      }
      return true;
   }
}
