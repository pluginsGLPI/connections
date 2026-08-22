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

ALTER TABLE `glpi_plugin_connections_connectionratesguaranteed` RENAME TO `glpi_plugin_connections_guaranteedconnectionrates`;
ALTER TABLE `glpi_plugin_connections_connections`
   CHANGE COLUMN `plugin_connections_connectionratesguaranteed_id`
      `plugin_connections_guaranteedconnectionrates_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_connections_guaranteedconnectionrates (id)' AFTER `plugin_connections_connectionrates_id`;
ALTER TABLE `glpi_plugin_connections_connections`
   DROP INDEX `plugin_connections_connectionratesguaranteed_id`,
   ADD INDEX `plugin_connections_guaranteedconnectionrates_id` (`plugin_connections_guaranteedconnectionrates_id`);

CREATE TABLE IF NOT EXISTS `glpi_plugin_connections_profiles` (
   `id` int(11) NOT NULL auto_increment,
   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   `connections` char(1) collate utf8_unicode_ci default NULL,
   `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
