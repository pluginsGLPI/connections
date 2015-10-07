<?php
/*
 * @version $Id: HEADER 1 2010-02-24 00:12 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Author of file: CAILLAUD Xavier, GRISARD Jean Marc
// Purpose of file: plugin connections v1.6.5 - GLPI 0.85 / 0.90
// ----------------------------------------------------------------------
 */
class PluginConnectionsMenu extends CommonGLPI
{
   static $rightname = 'connections';
   
   static function getMenuName() {
      global $LANG;
      
      return $LANG['plugin_connections']['title'][1];
   }
   
   static function getMenuContent() {
      global $CFG_GLPI;
      
      $menu          = array();
      $menu['title'] = self::getMenuName();
      $menu['page']  = '/plugins/connections/front/connection.php';
      $menu['links'] = array(
         'add'    => Toolbox::getItemTypeFormURL('PluginConnectionsConnection', false),
         'search' => Toolbox::getItemTypeSearchURL('PluginConnectionsConnection', false),
      );
      
      if (Session::haveRight(static::$rightname, READ)) {
         $menu['options']['connections'] = array(
            'title' => self::getMenuName(),
            'page'  => Toolbox::getItemTypeFormURL('PluginConnectionsConnection', false),
            'links' => array(
               'add'    => Toolbox::getItemTypeFormURL('PluginConnectionsConnection', false),
               'search' => Toolbox::getItemTypeSearchURL('PluginConnectionsConnection', false),
            ),
         );
      }

      return $menu;
   }
}
