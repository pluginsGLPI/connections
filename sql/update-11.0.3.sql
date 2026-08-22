--
-- -------------------------------------------------------------------------
-- connections plugin for GLPI
-- Copyright (C) 2015-2026 by the connections Development Team.
--
-- https://github.com/pluginsGLPI/connections
-- -------------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of connections.
--
-- connections is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- connections is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with connections. If not, see <http://www.gnu.org/licenses/>.
-- --------------------------------------------------------------------------
--

UPDATE `glpi_items_tickets` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_items_problems` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_changes_items` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_documents_items` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
