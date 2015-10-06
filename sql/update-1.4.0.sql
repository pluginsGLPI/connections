ALTER TABLE `glpi_plugin_connections` ADD `recursive` tinyint(1) NOT NULL default '0' AFTER `FK_entities`;
ALTER TABLE `glpi_plugin_connections` CHANGE `type` `type` INT(4) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_connections` CHANGE `location` `location` INT(4) NOT NULL DEFAULT '0';