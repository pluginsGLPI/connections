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
// Purpose of file: plugin connections v1.6.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$PluginConnectionsConnection=new PluginConnectionsConnection();
$PluginConnectionsConnection_Item=new PluginConnectionsConnection_Item();

if (isset($_POST["add"])) {
	$PluginConnectionsConnection->check(-1,'w',$_POST);
   $newID=$PluginConnectionsConnection->add($_POST);
	Html::back();
	
} else if (isset($_POST["delete"])) {

	$PluginConnectionsConnection->check($_POST['id'],'w');
   $PluginConnectionsConnection->delete($_POST);
	Html::redirect(Toolbox::getItemTypeSearchURL('PluginConnectionsConnection'));
	
} else if (isset($_POST["restore"])) {

	$PluginConnectionsConnection->check($_POST['id'],'w');
   $PluginConnectionsConnection->restore($_POST);
	Html::redirect(Toolbox::getItemTypeSearchURL('PluginConnectionsConnection'));
	
} else if (isset($_POST["purge"])) {

	$PluginConnectionsConnection->check($_POST['id'],'w');
   $PluginConnectionsConnection->delete($_POST,1);
	Html::redirect(Toolbox::getItemTypeSearchURL('PluginConnectionsConnection'));
	
} else if (isset($_POST["update"])) {

	$PluginConnectionsConnection->check($_POST['id'],'w');
   $PluginConnectionsConnection->update($_POST);
	Html::back();
	
} else if (isset($_POST["additem"])) {

	if (!empty($_POST['itemtype'])&&$_POST['items_id']>0) {
      $PluginConnectionsConnection_Item->check(-1,'w',$_POST);
		$PluginConnectionsConnection_Item->addItem($_POST["plugin_connections_connections_id"],$_POST['items_id'],$_POST['itemtype']);
	}
	Html::back();
	
} else if (isset($_POST["deleteitem"])) {
   
   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val==1) {
         $PluginConnectionsConnection_Item->check($key,'w');
         $PluginConnectionsConnection_Item->delete($input);
      }
   }

	Html::back();
	
} else if (isset($_GET["deleteconnections"])) {

	$input = array('id' => $_GET["id"]);
   $PluginConnectionsConnection_Item->check($_GET["id"],'w');
	$PluginConnectionsConnection_Item->delete($input);
	Html::back();
	
} else {

	$PluginConnectionsConnection->checkGlobal("r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];
		//		Html::back();
	}
	
	$plugin = new Plugin();
	if ($plugin->isActivated("environment"))
		Html::header($LANG['plugin_connections']['title'][1],'',"plugins","environment","connections");
	else
		Html::header($LANG['plugin_connections']['title'][1],'',"plugins","connections");

	$PluginConnectionsConnection->showForm($_GET["id"]);

	Html::footer();
}

?>