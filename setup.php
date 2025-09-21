<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
 connections plugin for GLPI
 Copyright (C) 2015-2022 by the connections Development Team.

 https://github.com/pluginsGLPI/connections
-------------------------------------------------------------------------

LICENSE

This file is part of connections.

 connections is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

 connections is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with connections. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;
use GlpiPlugin\Accounts\Account;
use GlpiPlugin\Connections\Connection;
use GlpiPlugin\Connections\Connection_Item;
use GlpiPlugin\Connections\Profile;

define('PLUGIN_CONNECTIONS_VERSION', '11.0.0');

global $CFG_GLPI;

if (!defined("PLUGINCONNECTIONS_DIR")) {
    define("PLUGINCONNECTIONS_DIR", Plugin::getPhpDir("connections"));
}
if (!defined("PLUGINCONNECTIONS_WEBDIR")) {
    $root = $CFG_GLPI['root_doc'] . '/plugins/connections';
    define("PLUGINCONNECTIONS_WEBDIR", $root);
}
if (!defined("PLUGINCONNECTIONS_NOTFULL_WEBDIR")) {
    define("PLUGINCONNECTIONS_NOTFULL_WEBDIR", Plugin::getPhpDir("connections", false));
}

// Init the hooks of the plugins -Needed
function plugin_init_connections()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['connections']   = true;
    $PLUGIN_HOOKS['change_profile']['connections']   = [Profile::class, 'initProfile'];
    $PLUGIN_HOOKS['assign_to_ticket']['connections'] = true;

    if (Plugin::isPluginActive("connections")) {
        Plugin::registerClass(Connection::class, [
            //         'linkuser_types'              => true,
            //         'linkgroup_types'             => true,
            'document_types'              => true,
            'contract_types'              => true,
            'ticket_types'                => true,
            'helpdesk_visible_types'      => true,
            'notificationtemplates_types' => true,
        ]);

        Plugin::registerClass(Profile::class, [
            'addtabon' => 'Profile',
        ]);

        Plugin::registerClass(Connection_Item::class, [
            'addtabon' => ['NetworkEquipment',
                'Appliance',
                'Computer',
                'Certificate',
                'Supplier'],
        ]);

        if (class_exists(Account::class)) {
            Account::registerType(Connection::class);
        }

        if (Session::haveRight("plugin_connections_connection", READ)
            || Session::haveRight("config", READ)) {
            $PLUGIN_HOOKS['menu_toadd']['connections'] = [
                'assets' => Connection::class,
            ];
        }

        $CFG_GLPI['impact_asset_types'][Connection::class] = PLUGINCONNECTIONS_NOTFULL_WEBDIR . "/pics/icon.png";
        if (isset($_SESSION['glpiactiveprofile']['interface'])
          && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $PLUGIN_HOOKS[Hooks::ADD_CSS]['connections'] = "connections.css";
        }
        $PLUGIN_HOOKS['migratetypes']['connections']                  = 'plugin_datainjection_migratetypes_connections';
        $PLUGIN_HOOKS['plugin_datainjection_populate']['connections'] = 'plugin_datainjection_populate_connections';
    }
}

// Get the name and the version of the plugin - Needed
/**
 * @return array
 */
function plugin_version_connections()
{

    return [
        'name'           => __('Connections', 'connections'),
        'version'        => PLUGIN_CONNECTIONS_VERSION,
        'license'        => 'GPLv2+',
        'oldname'        => 'connection',
        'author'         => "<a href='https//blogglpi.infotel.com'>Infotel</a>, Xavier CAILLAUD, Jean Marc GRISARD, TECLIB'",
        'homepage'       => 'https://github.com/pluginsGLPI/connections',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0',
                'max' => '12.0',
                'dev' => false,
            ],
        ],
    ];
}

/**
 * @param $types
 *
 * @return mixed
 */
function plugin_datainjection_migratetypes_connections($types)
{
    $types[4400] = Connection::class;
    return $types;
}
