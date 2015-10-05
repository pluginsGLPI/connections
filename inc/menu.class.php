<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
Accounts plugin for GLPI
Copyright (C) 2003-2011 by the accounts Development Team.

https://forge.indepnet.net/projects/accounts
-------------------------------------------------------------------------

LICENSE

This file is part of accounts.

accounts is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

accounts is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with accounts. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
*/
 
class PluginConnectionsMenu extends CommonGLPI {
   static $rightname = 'plugin_connections'; //WARNING, not existed now

   static function getMenuName() {
      return __('Connections', 'connections');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu                                           = array();
      $menu['title']                                  = self::getMenuName();
		$menu['page']                                	= "/plugins/connections/front/connection.php";

		$menu['options']['model']['title'] = "/t1"; //PluginItilcategorygroupsMenu::getTypeName();
      $menu['options']['model']['page'] = "/t2"; //Toolbox::getItemTypeSearchUrl('PluginItilcategorygroupsCategory', false);
      $menu['options']['model']['links']['search'] = "/t3"; //Toolbox::getItemTypeSearchUrl('PluginItilcategorygroupsCategory', false);

      $image = "<img src='".
            $CFG_GLPI["root_doc"]."/plugins/accounts/pics/cadenas.png' title='".
            _n('Encryption key', 'Encryption keys', 2)."' alt='".
            _n('Encryption key', 'Encryption keys', 2, 'accounts')."'>";

      $menu['links']['search']                        = "/test"; //PluginAccountsAccount::getSearchURL(false);
      $menu['links'][$image]                          = "/test2"; //PluginAccountsHash::getSearchURL(false);
      //if (PluginAccountsAccount::canCreate()) {
         $menu['links']['add']                        = "/test3"; //PluginAccountsAccount::getFormURL(false);
      //}
      
      $menu['options']['connections']['title']            = "/test4"; //PluginAccountsAccount::getTypeName(2);
      $menu['options']['connections']['page']             = "/test5"; //PluginAccountsAccount::getSearchURL(false);
      $menu['options']['connections']['links']['search']  = "/test6"; //PluginAccountsAccount::getSearchURL(false);
      $menu['options']['connections']['links'][$image] = "/test7"; //PluginAccountsHash::getSearchURL(false);
      //if (PluginAccountsAccount::canCreate()) {
         $menu['options']['connections']['links']['add']  = "/test8"; //PluginAccountsAccount::getFormURL(false);
      //}

      $menu['options']['connection']['title']            = "/test-1"; //PluginAccountsAccount::getTypeName(2);
      $menu['options']['connection']['page']             = "/test-2"; //PluginAccountsAccount::getSearchURL(false);
      $menu['options']['connection']['links']['search']  = "/test-3"; //PluginAccountsAccount::getSearchURL(false);
      $menu['options']['connection']['links'][$image] = "/test-4"; //PluginAccountsHash::getSearchURL(false);
      //if (PluginAccountsAccount::canCreate()) {
         $menu['options']['connection']['links']['add']  = "/test-5"; //PluginAccountsAccount::getFormURL(false);
      //}


      $menu['options']['hash']['title']               = ""; //PluginAccountsHash::getTypeName(2);
      $menu['options']['hash']['page']                = ""; //PluginAccountsHash::getSearchURL(false);
      $menu['options']['hash']['links']['search']     = ""; //PluginAccountsHash::getSearchURL(false);
      $menu['options']['hash']['links'][$image]       = ""; //PluginAccountsHash::getSearchURL(false);;

      //if (PluginAccountsHash::canCreate()) {
         $menu['options']['hash']['links']['add']     = ""; //PluginAccountsHash::getFormURL(false);
      //}

      return $menu;
   }

   /*
   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['admin']['types']['PluginConnectionsMenu'])) {
         unset($_SESSION['glpimenu']['admin']['types']['PluginConnectionsMenu']); 
      }
      if (isset($_SESSION['glpimenu']['admin']['content']['pluginconnectionsmenu'])) {
         unset($_SESSION['glpimenu']['admin']['content']['pluginconnectionsmenu']); 
      }
   }
   */
}