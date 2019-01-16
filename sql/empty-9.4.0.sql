DROP TABLE IF EXISTS `glpi_plugin_connections_connections`;
CREATE TABLE `glpi_plugin_connections_connections` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_connections_connectiontypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_connections_connectiontypes (id)',
   `plugin_connections_connectionrates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_connections_connectionrates (id)',
   `plugin_connections_guaranteedconnectionrates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_connections_connectionratesguaranteed (id)',
   `bytes` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   `comment` text collate utf8_unicode_ci,
   `notepad` longtext collate utf8_unicode_ci,
   `others` varchar(255) collate utf8_unicode_ci default NULL,
   `is_helpdesk_visible` int(11) NOT NULL default '1',
   `date_mod` datetime default NULL,
   `is_deleted` tinyint(1) NOT NULL default '0',
    PRIMARY KEY  (`id`),
    KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_connections_connectiontypes_id` (`plugin_connections_connectiontypes_id`),
   KEY `plugin_connections_connectionrates_id` (`plugin_connections_connectionrates_id`),
   KEY `plugin_connections_guaranteedconnectionrates_id` (`plugin_connections_guaranteedconnectionrates_id`),
   KEY `users_id` (`users_id`),
   KEY `groups_id` (`groups_id`),
   KEY `suppliers_id` (`suppliers_id`),
   KEY `date_mod` (`date_mod`),
   KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_connections_connections_items`;
CREATE TABLE `glpi_plugin_connections_connections_items` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_connections_connections_id` int(11) NOT NULL default '0',
   `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`plugin_connections_connections_id`,`itemtype`,`items_id`),
   KEY `FK_device` (`items_id`,`itemtype`),
   KEY `item` (`itemtype`,`items_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_connections_connectiontypes`;
CREATE TABLE `glpi_plugin_connections_connectiontypes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `is_recursive` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_connections_connectionrates`;
CREATE TABLE `glpi_plugin_connections_connectionrates` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `is_recursive` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_connections_guaranteedconnectionrates`;
CREATE TABLE `glpi_plugin_connections_guaranteedconnectionrates` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `is_recursive` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_connections_configs`;
CREATE TABLE `glpi_plugin_connections_configs` (
   `id` int(11) NOT NULL auto_increment,
   `delay_expired` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   `delay_whichexpire` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginConnectionsConnection','2','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginConnectionsConnection','3','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginConnectionsConnection','4','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginConnectionsConnection','6','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginConnectionsConnection','7','5','0');
