ALTER TABLE `glpi_plugin_connections` ADD INDEX `name` (`name`);
ALTER TABLE `glpi_dropdown_plugin_connections_type` ADD INDEX `FK_entities` (`FK_entities`);
ALTER TABLE `glpi_plugin_connections_profiles` DROP COLUMN `interface` , DROP COLUMN `is_default`;