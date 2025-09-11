UPDATE `glpi_displaypreferences` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `glpi_displaypreferences`.`itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_notificationtemplates` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_notifications` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_impactrelations` SET `itemtype_source` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype_source` = 'PluginConnectionsConnection';
UPDATE `glpi_impactrelations` SET `itemtype_impacted` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype_impacted` = 'PluginConnectionsConnection';

UPDATE `glpi_documents_items` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_savedsearches` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_items_tickets` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_dropdowntranslations` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_savedsearches_users` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
UPDATE `glpi_notepads` SET `itemtype` = 'GlpiPlugin\\Connections\\Connection' WHERE `itemtype` = 'PluginConnectionsConnection';
