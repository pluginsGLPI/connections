ALTER TABLE `glpi_plugin_connections` RENAME `glpi_plugin_connections_connections`;
ALTER TABLE `glpi_plugin_connectiond_device` RENAME `glpi_plugin_connections_connections_items`;
ALTER TABLE `glpi_dropdown_plugin_connections_type` RENAME `glpi_plugin_connections_connectiontypes`;
ALTER TABLE `glpi_plugin_connectiond_profiles` RENAME `glpi_plugin_connections_profiles`;

ALTER TABLE `glpi_plugin_connections_connections` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `type` `plugin_connections_connectiontypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_connections_connectiontypes (id)',
   CHANGE `FK_users` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `FK_groups` `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   CHANGE `FK_enterprise` `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   CHANGE `others` `others` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `helpdesk_visible` `is_helpdesk_visible` int(11) NOT NULL default '1',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   CHANGE `notes` `notepad` longtext collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD `rates` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_connections_connectionrates (id)',
   ADD `ratesguaranteedd` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_connections_connectionratesguaranteed (id)',
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`plugin_connections_connectiontypes_id`),
   ADD INDEX (`plugin_connections_connectionrates`),
   ADD INDEX (`plugin_connections_connectionratesguaranteedd`),
   ADD INDEX (`users_id`),
   ADD INDEX (`groups_id`),
   ADD INDEX (`suppliers_id`),
   ADD INDEX (`is_helpdesk_visible`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_connections_connections_items` 
   DROP INDEX `FK_connection`,
   DROP INDEX `FK_connection_2`,
   DROP INDEX `FK_device`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_connection` `plugin_connections_connections_id` int(11) NOT NULL default '0',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   ADD UNIQUE `unicity` (`plugin_connections_connections_id`,`itemtype`,`items_id`),
   ADD INDEX `FK_device` (`items_id`,`itemtype`),
   ADD INDEX `item` (`itemtype`,`items_id`);

ALTER TABLE `glpi_plugin_connections_connectiontypes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_connections_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `connection` `connections` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `open_ticket` `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);


