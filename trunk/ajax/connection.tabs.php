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
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST["id"])) {
	exit();
}

if (!isset($_POST["withtemplate"])) $_POST["withtemplate"] = "";

$PluginConnectionsConnection=new PluginConnectionsConnection();
$PluginConnectionsConnection_Item=new PluginConnectionsConnection_Item();

$PluginConnectionsConnection->checkGlobal("r");
	
if ($_POST["id"]>0 && $PluginConnectionsConnection->can($_POST["id"],'r')) {
   if (!empty($_POST["withtemplate"])) {
		switch($_REQUEST['glpi_tab']) {
			default :
				break;
		}
	} else {
	
		switch($_REQUEST['glpi_tab']) {
		
			case -1 :
				$PluginConnectionsConnection_Item->showItemFromPlugin($_POST["id"]);
				Ticket::showListForItem($PluginConnectionsConnection);
				Document::showAssociated($PluginConnectionsConnection);
				Plugin::displayAction($PluginConnectionsConnection,$_REQUEST['glpi_tab']);
				break;
			case 8 :
				Contract::showAssociated($PluginConnectionsConnection);
            break;	
			default :
				if (!CommonGLPI::displayStandardTab($PluginConnectionsConnection, $_REQUEST['glpi_tab'])) {
					$PluginConnectionsConnection_Item->showItemFromPlugin($_POST["id"]);
				}
				break;
		}
	}
}

Html::ajaxFooter();

?>