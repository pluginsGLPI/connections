<?php
/*
   ----------------------------------------------------------------------
   GLPI - financialnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

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
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------


$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");
include ('../../../inc/includes.php');

useplugin('connections',true);

if(!isset($_GET["ID"])) $_GET["ID"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$plugin_connections=new plugin_connections();

if (isset($_POST["add"]))
{
	if( plugin_connections_HaveRight("connections","w"))
		$newID=$plugin_connections->add($_POST);
	Html::back();
} 
else if (isset($_POST["delete"]))
{

	if( plugin_connections_HaveRight("connections","w"))
		$plugin_connections->delete($_POST);
	Html::redirect($CFG_GLPI["root_doc"]."/plugins/connections/index.php");
}
else if (isset($_POST["restore"]))
{

	if( plugin_connections_HaveRight("connections","w"))
		$plugin_connections->restore($_POST);
	Html::redirect($CFG_GLPI["root_doc"]."/plugins/connections/index.php");
}
else if (isset($_POST["purge"]))
{
	if( plugin_connections_HaveRight("connections","w"))
		$plugin_connections->delete($_POST,1);
	Html::redirect($CFG_GLPI["root_doc"]."/plugins/connections/index.php");
}
else if (isset($_POST["update"]))
{
	if( plugin_connections_HaveRight("connections","w"))
		$plugin_connections->update($_POST);
	Html::back();
} 
else if (isset($_POST["additem"])){

	if ($_POST['type']>0&&$_POST['item']>0){

		if(plugin_connections_HaveRight("connections","w"))
			plugin_connections_addDevice($_POST["conID"],$_POST['item'],$_POST['type']);
	}
	Html::back();
}
else if (isset($_POST["deleteitem"])){

	if(plugin_connections_HaveRight("connections","w"))
		foreach ($_POST["item"] as $key => $val){
		if ($val==1) {
			plugin_connections_deleteDevice($key);
			}
		}

	Html::back();
}else if (isset($_GET["deleteconnections"])){

	if(plugin_connections_HaveRight("connections","w"))
		plugin_connections_deleteDevice($_GET["ID"]);
	Html::back();
} else {

	plugin_connections_checkRight("connections","r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];

	}
	$plugin = new Plugin();
	if ($plugin->isActivated("environment"))
		Html::header($LANG['plugin_connections'][1],$_SERVER['PHP_SELF'],"plugins","environment","connections");
	else
		Html::header($LANG['plugin_connections'][1],$_SERVER["PHP_SELF"],"plugins","connections");

	$plugin_connections->showForm($_SERVER["PHP_SELF"],$_GET["ID"]);

	Html::footer();
}

?>
