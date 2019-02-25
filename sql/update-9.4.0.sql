ALTER TABLE `glpi_plugin_connections_connectionrates`
ADD `is_recursive` tinyint(1) NOT NULL default '0';

ALTER TABLE `glpi_plugin_connections_guaranteedconnectionrates`
ADD `is_recursive` tinyint(1) NOT NULL default '0';

ALTER TABLE `glpi_plugin_connections_connectiontypes`
ADD `is_recursive` tinyint(1) NOT NULL default '0';

