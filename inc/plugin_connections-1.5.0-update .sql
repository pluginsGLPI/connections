ALTER TABLE `glpi_plugin_connections` ADD INDEX `name` (`name`);

ALTER TABLE `glpi_dropdown_plugin_connections_type` ADD INDEX `FK_entities` (`FK_entities`);


ALTER TABLE `glpi_plugin_connections` ADD `recursive` tinyint(1) NOT NULL default '0';

ALTER TABLE `glpi_plugin_appweb_profile` ADD `open_ticket` char(1) default NULL;



