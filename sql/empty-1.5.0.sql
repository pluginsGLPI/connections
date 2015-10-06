DROP TABLE IF EXISTS `glpi_plugin_connections`;
CREATE TABLE `glpi_plugin_connections` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_entities` int(11) NOT NULL default '0',
   `recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `type` INT(4) NOT NULL DEFAULT '0',
   `bytes` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `location` INT(4) NOT NULL DEFAULT '0',
   `state` tinyint(4) NOT NULL default '0',
   `comments` text collate utf8_unicode_ci,
   `notes` longtext collate utf8_unicode_ci,
   `deleted` smallint(6) NOT NULL default '0',
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_connections_type`;
CREATE TABLE `glpi_dropdown_plugin_connections_type` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_entities` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `comments` text,
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_connections_device`;
CREATE TABLE `glpi_plugin_connections_device` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_connection` int(11) NOT NULL default '0',
   `FK_device` int(11) NOT NULL default '0',
   `device_type` int(11) NOT NULL default '0',
   PRIMARY KEY  (`ID`),
   UNIQUE KEY `FK_connection` (`FK_connection`,`FK_device`,`device_type`),
   KEY `FK_connection_2` (`FK_connection`),
   KEY `FK_device` (`FK_device`,`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_connections_profiles`;
CREATE TABLE `glpi_plugin_connections_profiles` (
   `ID` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `connections` char(1) default NULL,
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'2700','3','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'2700','4','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'2700','5','4','0');
