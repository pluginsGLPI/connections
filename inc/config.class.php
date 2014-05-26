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
// Purpose of file: plugin connections v1.6.2 - GLPI 0.83
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginConnectionsConfig extends CommonDBTM {

	function showForm($target,$ID) {
      global $LANG;
    
      $this->getFromDB($ID);
      $delay_expired=$this->fields["delay_expired"];
      $delay_whichexpire=$this->fields["delay_whichexpire"];
      echo "<div align='center'>";
      echo "<form method='post' action=\"$target\">";
      echo "<table class='tab_cadre' cellpadding='5'><tr><th>";
      echo $LANG['plugin_connections']['setup'][11]." : </th></tr>";
      echo "<tr class='tab_bg_1'><td><div align='center'>";

      $delay_stamp_first= mktime(0, 0, 0, date("m"), date("d")-$delay_expired, date("y"));
      $delay_stamp_next= mktime(0, 0, 0, date("m"), date("d")+$delay_whichexpire, date("y"));
      $date_first=date("Y-m-d",$delay_stamp_first);
      $date_next=date("Y-m-d",$delay_stamp_next);
      
      echo "<tr class='tab_bg_1'><td><div align='left'>";
      echo $LANG['plugin_connections']['mailing'][4]." <input type='text' size='5' name='delay_expired' value=\"$delay_expired\"> ".$LANG['plugin_connections']['setup'][12]." ( >".Html::convDate($date_first).")<br>";
      echo $LANG['plugin_connections']['mailing'][5]." <input type='text' size='5' name='delay_whichexpire' value=\"$delay_whichexpire\"> ".$LANG['plugin_connections']['setup'][12]." ( <".Html::convDate($date_next).")";

      echo "<tr><th>";
      echo "<input type='hidden' name='id' value='".$ID."'>";
      echo "<div align='center'><input type='submit' name='update' value=\"".$LANG['buttons'][2]."\" class='submit' ></div></th></tr>";
      echo "</table>";
      echo "</form></div>";
   }
}

?>