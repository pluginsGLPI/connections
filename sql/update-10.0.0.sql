ALTER TABLE `glpi_plugin_connections_connections`
CHANGE `users_id` `users_id_tech` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)';
ALTER TABLE `glpi_plugin_connections_connections`
    CHANGE `groups_id` `groups_id_tech` int unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)';

