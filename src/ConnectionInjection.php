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

use AllowDynamicProperties;
use PluginDatainjectionCommonInjectionLib;
use PluginDatainjectionInjectionInterface;
use Search;

#[AllowDynamicProperties]
class ConnectionInjection extends Connection implements PluginDatainjectionInjectionInterface
{
    static function getTable($classname = null)
    {
        return Connection::getTable();
    }

    /**
     * @return bool
     */
    function isPrimaryType()
    {
        return true;
    }

    /**
     * @return array
     */
    function connectedTo()
    {
        return [];
    }

    /**
     * @param string $primary_type
     *
     * @return array|the
     */
    function getOptions($primary_type = '')
    {

        $tab = Search::getOptions(get_parent_class($this));

        //$blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
        //Remove some options because some fields cannot be imported
        $notimportable            = [80];
        $options['ignore_fields'] = $notimportable;
        $options['displaytype']   = ["dropdown"       => [2, 4, 5, 6],
            "text"           => [9],
            "user"           => [8],
            "multiline_text" => [7],
            "bool"           => [13]];

        $tab = PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);

        return $tab;
    }


    /**
     * @param array|fields  $values
     * @param array|options $options
     *
     * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
     * @internal param fields $values to add into glpi
     * @internal param options $options used during creation
     */
    function addOrUpdateObject($values = [], $options = [])
    {

        $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
        $lib->processAddOrUpdate();
        return $lib->getInjectionResults();
    }
}
