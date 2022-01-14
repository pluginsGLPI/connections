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

// Class for a Dropdown

/**
 * Class PluginConnectionsGuaranteedConnectionRate
 */
class PluginConnectionsGuaranteedConnectionRate extends CommonDropdown {
   static $rightname = 'plugin_connections_connection';

   /**
    * @param int $nb
    *
    * @return string
    */
   public static function getTypeName($nb = 0) {
      return __('Guaranteed Rates', 'connections');
   }

   /**
    * @param $ID
    * @param $entity
    *
    * @return int
    */
   public static function transfer($ID, $entity) {
      global $DB;

      $temp = new self();
      if ($ID <= 0 || !$temp->getFromDB($ID)) {
         return 0;
      }
      $query = "SELECT `id`
                FROM `" . $temp->getTable() . "`
                WHERE `entities_id` = '$entity'
                AND `name` = '" . addslashes($temp->fields['name']) . "'";
      foreach ($DB->request($query) as $data) {
         return $data['id'];
      }
      $input                = $temp->fields;
      $input['entities_id'] = $entity;
      unset($input['id']);

      return $temp->add($input);
   }
}
