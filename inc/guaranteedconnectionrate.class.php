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

// Class for a Dropdown
class PluginConnectionsGuaranteedConnectionRate extends CommonDropdown
{
   static $rightname = 'plugin_connections_connection';

   public static function getTypeName($nb = 0)
   {
      return __('Guaranteed Rates', 'connections');
   }

   public static function transfer($ID, $entity)
   {
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
