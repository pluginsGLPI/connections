<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
 connections plugin for GLPI
 Copyright (C) 2015-2022 by the connections Development Team.

 https://github.com/pluginsGLPI/connections
-------------------------------------------------------------------------

LICENSE

This file is part of connections.

 connections is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

 connections is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with connections. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginConnectionsConnectionInjection
 */
class PluginConnectionsConnectionInjection extends PluginConnectionsConnection implements PluginDatainjectionInjectionInterface {

   public function __construct() {
      //Needed for getSearchOptions !
      $this->table = getTableForItemType('PluginConnectionsConnection');
   }

   /**
    * @return \a|bool
    */
   public function isPrimaryType() {
      return true;
   }

   /**
    * @return \an|array
    */
   public function connectedTo() {
      return [];
   }

   /**
    * @param string $primary_type
    *
    * @return \an|array
    */
   public function getOptions($primary_type = '') {
      $tab = parent::getSearchOptions();

      //Specific to location
      $tab[3]['linkfield'] = 'locations_id';

      //Add linkfield for theses fields : no massive action is allowed in the core, but they can be
      //imported using the commonlib
      $add_linkfield = [
         'comment' => 'comment',
         'notepad' => 'notepad',
      ];

      foreach ($tab as $id => $tmp) {
         if (in_array($tmp['field'], $add_linkfield)) {
            $tab[$id]['linkfield'] = $add_linkfield[$tmp['field']];
         }

         if (!isset($tmp['linkfield'])) {
            $tab[$id]['injectable'] = PluginDatainjectionCommonInjectionLib::FIELD_VIRTUAL;
         } else {
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
    *
    * @param array $values
    * @param array $options
    *
    * @return \an
    */
   public function deleteObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->deleteObject();

      return $lib->getInjectionResults();
   }

   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param array $values
    * @param array $options
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    */
   public function addOrUpdateObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();

      return $lib->getInjectionResults();
   }
}
