<?php
/*
 * @version $Id: domaininjection.class.php 105 2010-05-27 11:33:59Z walid $
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
 */

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

/// Computer class
class PluginConnectionsConnectionInjection extends PluginConnectionsConnection
   implements PluginDatainjectionInjectionInterface {

   function __construct() {
      //Needed for getSearchOptions !
      $this->table = getTableForItemType('PluginConnectionsConnection');
   }

   function isPrimaryType() {
      return true;
   }

   function connectedTo() {
      return array();
   }

   function getOptions($primary_type = '') {
      $tab = parent::getSearchOptions();

      //Specific to location
      $tab[3]['linkfield'] = 'locations_id';

      //Add linkfield for theses fields : no massive action is allowed in the core, but they can be
      //imported using the commonlib
      $add_linkfield = array('comment' => 'comment', 'notepad' => 'notepad');
      foreach ($tab as $id => $tmp) {
         if (in_array($tmp['field'],$add_linkfield)) {
            $tab[$id]['linkfield'] = $add_linkfield[$tmp['field']];
         }
         if (!isset($tmp['linkfield'])) {
            $tab[$id]['injectable'] = PluginDatainjectionCommonInjectionLib::FIELD_VIRTUAL;
         }
         else {
            $tab[$id]['injectable'] = PluginDatainjectionCommonInjectionLib::FIELD_INJECTABLE;
         }
         if (isset($tmp['linkfield']) && !isset($tmp['displaytype'])) {
            $tab[$id]['displaytype'] = 'text';
          }
          if (isset($tmp['linkfield']) && !isset($tmp['checktype'])) {
             $tab[$id]['checktype'] = 'text';
          }
      }

      return $tab;
   }

   /**
    * Standard method to delete an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    * @param fields fields to add into glpi
    * @param options options used during creation
    */
   function deleteObject($values=array(), $options=array()) {
      $lib = new PluginDatainjectionCommonInjectionLib($this,$values,$options);
      $lib->deleteObject();
      return $lib->getInjectionResults();
   }

   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    * @param values fields to add into glpi
    * @param options options used during creation
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    */
   function addOrUpdateObject($values=array(), $options=array()) {
      global $LANG;
      $lib = new PluginDatainjectionCommonInjectionLib($this,$values,$options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }

}

?>