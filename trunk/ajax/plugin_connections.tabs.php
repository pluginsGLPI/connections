<?php
/*
 * @version $Id: computer.tabs.php 7152 2008-07-29 12:27:18Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

useplugin('connections',true);

if(!isset($_POST["ID"])) {
	exit();
}
if(!isset($_POST["sort"])) $_POST["sort"] = "";
if(!isset($_POST["order"])) $_POST["order"] = "";
if(!isset($_POST["withtemplate"])) $_POST["withtemplate"] = "";

	plugin_connections_checkRight("connections","r");
		
	if (empty($_POST["ID"])){
		switch($_POST['glpi_tab']){
			default :
				break;
		}
	}else{
	
		switch($_POST['glpi_tab']){
				case -1 :
					plugin_connections_showNetworking($_POST["ID"]);
					showDocumentAssociated(PLUGIN_CONNECTIONS_TYPE,$_POST["ID"],$_POST["withtemplate"]);
					displayPluginAction(PLUGIN_CONNECTIONS_TYPE,$_POST["ID"],$_POST['glpi_tab']);
					break;
				/*case 6:
				showJobListForItem(PLUGIN_APPWEB_TYPE,$_POST["ID"]);
				plugin_appweb_showTickets($_POST["ID"]);
				break;
				case 8 :
				if ($plugin->isInstalled("centreon") && $plugin->isActivated("centreon"))
					plugin_headings_centreon_service(PLUGIN_CONNECTIONS_TYPE,$_POST["ID"]);
				break;*/
				case 9 :
					showDocumentAssociated(PLUGIN_CONNECTIONS_TYPE,$_POST["ID"],$_POST["withtemplate"]);
					break;
				case 10 :
					showNotesForm($_POST['target'],PLUGIN_CONNECTIONS_TYPE,$_POST["ID"]);
					break;
				case 12 :
					showHistory(PLUGIN_CONNECTIONS_TYPE,$_POST["ID"]);
					break;
				default :
					if (!displayPluginAction(PLUGIN_CONNECTIONS_TYPE,$_POST["ID"],$_POST['glpi_tab'])) {
						plugin_connections_showNetworking($_POST["ID"]);
					}			
					break;
		}
		ajaxFooter();
	}

?>