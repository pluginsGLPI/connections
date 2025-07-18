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

use Glpi\Exception\Http\AccessDeniedHttpException;

include('../../../inc/includes.php');

Html::header(
   __('Connections', 'connections'),
   $_SERVER["PHP_SELF"],
   "assets",
   "pluginconnectionsconnection",
   ""
);

$PluginConnectionsConnection = new PluginConnectionsConnection();

if ($PluginConnectionsConnection->canView() || Session::haveRight("config", UPDATE)) {
   Search::show("PluginConnectionsConnection");

} else {
    throw new AccessDeniedHttpException();
}

Html::footer();
