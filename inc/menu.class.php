<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
 connections plugin for GLPI
 Copyright (C) 2015-2016 by the connections Development Team.

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

/**
 * Class PluginConnectionsMenu
 */
class PluginConnectionsMenu extends CommonGLPI {
   static $rightname = 'plugin_connections_connection';

   /**
    * @return string
    */
   static function getMenuName() {
      return __('Connections', 'connections');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu          = [];
      $menu['title'] = self::getMenuName();
      $menu['page']  = PluginConnectionsConnection::getSearchURL(false);
      $menu['links']['search'] = PluginConnectionsConnection::getSearchURL(false);
      if (Session::haveRight(static::$rightname, CREATE)) {
         $menu['links']['add'] = PluginConnectionsConnection::getFormURL(false);
      }

      $menu['icon'] = PluginConnectionsConnection::getIcon();

      return $menu;
   }

   static function removeRightsFromSession()
   {
      if (isset($_SESSION['glpimenu']['assets']['types']['PluginConnectionsMenu'])) {
         unset($_SESSION['glpimenu']['assets']['types']['PluginConnectionsMenu']);
      }
      if (isset($_SESSION['glpimenu']['assets']['content']['pluginconnectionsmenu'])) {
         unset($_SESSION['glpimenu']['assets']['content']['pluginconnectionsmenu']);
      }
   }
}
