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
include ('../../../inc/includes.php');

$plugin = new Plugin();
if ($plugin->isActivated("environment")) {
	Html::header(__('Connections', 'connections'),'',"plugins","environment","connections");
} else {
   //TODO : à porter en 0.85
	Html::header(__('Connections', 'connections'),'',"plugins","connections");
}

$PluginConnectionsConnection = new PluginConnectionsConnection();

if ($PluginConnectionsConnection->canView() || Session::haveRight("config", READ)) {
	Search::show("PluginConnectionsConnection");
} else {
	//Note : a GLPI function exist for that
	echo "<div align='center'>";
   echo "<br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
	echo "<b>".__('Access Denied')."</b>";
   echo "</div>";
}

Html::footer();