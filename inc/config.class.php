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
// Purpose of file: plugin connections v1.6.4 - GLPI 0.84
// ----------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

//Note : En 0.85, Possibilité pour les plugins d'intégrer leur propre configuration dans la table glpi_configs

class PluginConnectionsConfig extends CommonDBTM {

   function showForm($target, $ID) {
      global $LANG; //LOL, CF plus bas

      $this->getFromDB($ID);

      // Note : Possibilité pour les plugin d'intégrer leur propre configuration dans la table glpi_configs (en 0.85)
      //$config = new Config();
      //$config->find("'contect' = 'plugin_connection'");

      echo "<div align='center'>";

      echo "<form method='post' action=\"$target\">";
      echo "<table class='tab_cadre' cellpadding='5'>";

      echo "<tr>";
      echo "<th>" . $LANG['plugin_connections']['setup'][11]." : " . "</th>"; //LOL
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>";
      echo "<div align='center'>";

      $delay_stamp_first = mktime(0, 0, 0, date("m"), date("d") - $this->fields["delay_expired"], date("y"));
      $delay_stamp_next  = mktime(0, 0, 0, date("m"), date("d") + $this->fields["delay_whichexpire"], date("y"));

      $date_first = date("Y-m-d", $delay_stamp_first);
      $date_next  = date("Y-m-d", $delay_stamp_next);
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "<div align='left'>"; //Note : manque div de fin
      echo $LANG['plugin_connections']['mailing'][4]; //LOL, ce n'est pas déclaré
      echo " <input type='text' size='5' name='delay_expired' value=\"".$this->fields["delay_expired"]."\"> ";
      echo __("days", 'connections')." ( >".Html::convDate($date_first).")<br>"; //Note : can use GLPI gettext core with minusucle

      echo $LANG['plugin_connections']['mailing'][5]; //LOL, ce n'est pas déclaré
      echo " <input type='text' size='5' name='delay_whichexpire' value=\"".$this->fields["delay_whichexpire"]."\"> ";
      echo __("days", 'connections')." ( <".Html::convDate($date_next).")";

      echo "<tr><th>";
      echo "<input type='hidden' name='id' value='".$ID."'>";
      echo "<div align='center'>";
      echo "<input type='submit' name='update' value=\"".__('Post')."\" class='submit'/>";
      echo "</div>";
      echo "</th></tr>";
      echo "</table>";
      Html::closeForm(true);

      echo "</div>";
   }
}
