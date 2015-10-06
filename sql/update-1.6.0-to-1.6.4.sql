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
