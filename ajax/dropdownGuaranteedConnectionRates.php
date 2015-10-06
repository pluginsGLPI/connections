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

if (strpos($_SERVER['PHP_SELF'],"dropdownGuaranteedConnectionRates.php")) {
   $AJAX_INCLUDE = 1;

   include ('../../../inc/includes.php');

   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box
if (isset($_POST["plugin_connections_guaranteedconnectionrates_id"])) {
   $rand     = $_POST['rand'];
   $use_ajax = false;

   if ($CFG_GLPI["use_ajax"] &&
      countElementsInTable(
         'glpi_plugin_connections_connections',
         "glpi_plugin_connections_connections.plugin_connections_guaranteedconnectionrates_id='"
            . $_POST["plugin_connections_guaranteedconnectionrates_id"] . "' "
            . getEntitiesRestrictRequest(
               "AND",
               "glpi_plugin_connections_connections",
               "",
               $_POST["entity_restrict"],
               true)
            ) > $CFG_GLPI["ajax_limit_count"]
   ) {
      $use_ajax = true;
   }


   $params = array(
      'searchText'                                      => '__VALUE__',
      'plugin_connections_guaranteedconnectionrates_id' => $_POST["plugin_connections_guaranteedconnectionrates_id"],
      'entity_restrict'                                 => $_POST["entity_restrict"],
      'rand'                                            => $_POST['rand'],
      'myname'                                          => $_POST['myname'],
      'used'                                            => $_POST['used'],
   );

   $default = "<select name='" . $_POST["myname"] . "'>
                  <option value='0'>" . Dropdown::EMPTY_VALUE . "</option>
               </select>";
   Ajax::dropdown(
      $use_ajax,
      "/plugins/connections/ajax/dropdownGuaranteedConnectionRates.php",
      $params,
      $default,
      $rand
   );
}
