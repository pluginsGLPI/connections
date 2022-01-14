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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "dropdownConnections.php")) {
   $AJAX_INCLUDE = 1;

   include('../../../inc/includes.php');

   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}
if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkCentralAccess();
// Make a select box with all glpi users

$where = " WHERE (`glpi_plugin_connections_connections`.`plugin_connections_connectiontypes_id` = '" . $_POST['plugin_connections_connectiontypes_id'] . "')
           AND `glpi_plugin_connections_connections`.`is_deleted` = '0'";

if (isset($_POST["entity_restrict"]) && $_POST["entity_restrict"] >= 0) {
   $where .= getEntitiesRestrictRequest(
      "AND",
      "glpi_plugin_connections_connections",
      '',
      $_POST["entity_restrict"],
      true
   );
} else {
   $where .= getEntitiesRestrictRequest(
      "AND",
      "glpi_plugin_connections_connections",
      '',
      '',
      true
   );
}

$used = [];
if (isset($_POST['used'])) {
   if (is_array($_POST['used'])) {
      $used = $_POST['used'];
   } else {
      $used = Toolbox::decodeArrayFromInput($_POST['used']);
   }
}
if (!empty($used)) {
   $where .= ' AND `id` NOT IN (' . implode(', ', $used) . ') ';
}

if ($_POST['searchText'] != $CFG_GLPI["ajax_wildcard"]) {
   $where .= " AND `glpi_plugin_connections_connections`.`name` " . Search::makeTextSearch($_POST['searchText']);
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";
if ($_POST['searchText'] == $CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

$query  = "SELECT *
          FROM `glpi_plugin_connections_connections`
          $where
          ORDER BY `entities_id`, `name`
          $LIMIT";
$result = $DB->query($query);

echo "<select class='form-select' name=\"" . $_POST['myname'] . "\">";

echo "<option value=\"0\">" . Dropdown::EMPTY_VALUE . "</option>";

if ($DB->numrows($result)) {
   $prev = -1;
   while ($data = $DB->fetchArray($result)) {
      if ($data["entities_id"] != $prev) {
         if ($prev >= 0) {
            echo "</optgroup>";
         }
         $prev = $data["entities_id"];
         echo "<optgroup label=\"" . Dropdown::getDropdownName("glpi_entities", $prev) . "\">";
      }
      $output = $data["name"];
      echo "<option value=\"" . $data["id"] . "\" title=\"$output\">" . substr($output, 0, $CFG_GLPI["dropdown_chars_limit"]) . "</option>";
   }
   if ($prev >= 0) {
      echo "</optgroup>";
   }
}
echo "</select>";
