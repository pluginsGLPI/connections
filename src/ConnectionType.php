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

namespace GlpiPlugin\Connections;

use CommonDropdown;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

// Class for a Dropdown

/**
 * Class ConnectionType
 */
final class ConnectionType extends CommonDropdown
{
    public static $rightname = 'plugin_connections_connection';

    /**
     * @param int $nb
     *
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('Type of Connections', 'connections');
    }

    /**
     * @param $ID
     * @param $entity
     *
     * @return int
     */
    public static function transfer($ID, $entity)
    {
        global $DB;

        if ($ID > 0) {
            $table = self::getTable();
            $iterator = $DB->request([
                'FROM'   => $table,
                'WHERE'  => ['id' => $ID],
            ]);

            foreach ($iterator as $data) {
                $input['name']        = $data['name'];
                $input['entities_id'] = $entity;
                $temp                 = new self();
                $newID                = $temp->getID();
                if ($newID < 0) {
                    $newID = $temp->import($input);
                }

                return $newID;
            }
        }
    }
}
