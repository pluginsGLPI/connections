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

include('../../../inc/includes.php');

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$PluginConnectionsConnection      = new PluginConnectionsConnection();
$PluginConnectionsConnection_Item = new PluginConnectionsConnection_Item();

if (isset($_POST["add"]) && !isset($_POST["additem"])) {
   $PluginConnectionsConnection->check(-1, UPDATE, $_POST);
   $newID = $PluginConnectionsConnection->add($_POST);
   Html::back();

} elseif (isset($_POST["delete"])) {
   $PluginConnectionsConnection->check($_POST['id'], UPDATE);
   $PluginConnectionsConnection->delete($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginConnectionsConnection'));

} elseif (isset($_POST["restore"])) {
   $PluginConnectionsConnection->check($_POST['id'], UPDATE);
   $PluginConnectionsConnection->restore($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginConnectionsConnection'));

} elseif (isset($_POST["purge"])) {
   $PluginConnectionsConnection->check($_POST['id'], UPDATE);
   $PluginConnectionsConnection->delete($_POST, 1);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginConnectionsConnection'));

} elseif (isset($_POST["update"])) {
   $PluginConnectionsConnection->check($_POST['id'], UPDATE);
   $PluginConnectionsConnection->update($_POST);
   Html::back();

} elseif (isset($_POST["additem"])) {
   if (!empty($_POST['itemtype']) && $_POST['items_id'] > 0) {
      $PluginConnectionsConnection_Item->check(-1, UPDATE, $_POST);
      $PluginConnectionsConnection_Item->addItem(
         $_POST["plugin_connections_connections_id"],
         $_POST['items_id'],
         $_POST['itemtype']
      );
   }
   Html::back();

} elseif (isset($_POST["deleteitem"])) {
   foreach ($_POST["item"] as $key => $val) {
      $input = ['id' => $key];
      if ($val == 1) {
         $PluginConnectionsConnection_Item->deleteItem($input);
      }
   }
   Html::back();

} elseif (isset($_POST["deleteconnections"])) {
   $input = ['id' => $_POST["id"]];
   $PluginConnectionsConnection_Item->check($_POST["id"], UPDATE);
   $PluginConnectionsConnection_Item->delete($input);
   Html::back();

} else {
   Session::checkRight('plugin_connections_connection', READ);

   if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab'] = 1;
   if (isset($_GET['onglet'])) {
      $_SESSION['glpi_tab'] = $_GET['onglet'];
   }

   $plugin = new Plugin();

   Html::header(
      __('Connections', 'connections'),
      $_SERVER["PHP_SELF"],
      "assets",
      "pluginconnectionsconnection",
      "connections"
   );

   $PluginConnectionsConnection->display($_GET);

   Html::footer();
}
