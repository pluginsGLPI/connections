ALTER TABLE `glpi_plugin_connections_connections`
ADD `locations_id` int(11)NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_locations (id)';

