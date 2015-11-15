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

class PluginConnectionsConnection extends CommonDBTM
{
   static $rightname = 'plugin_connections_connection';
   public $dohistory = true;

   public static function getTypeName($nb = 0)
   {
      return __('Connections', 'connections');
   }

   public function cleanDBonPurge()
   {

      $temp = new PluginConnectionsConnection_Item();
      $temp->clean(array('plugin_connections_connections_id' => $this->fields['id']));

   }

   public function getSearchOptions()
   {

      $tab = array();

      $tab['common'] = __('Connections', 'connections');

      $tab[1]['table']          = $this->getTable();
      $tab[1]['field']          = 'name';
      $tab[1]['linkfield']      = 'name';
      $tab[1]['name']           = __('Name');
      $tab[1]['datatype']       = 'itemlink';
      $tab[1]['itemlink_type']  = $this->getType();
      $tab[1]['displaytype']    = 'text';
      $tab[1]['checktype']      = 'text';
      $tab[1]['injectable']     = true;

      $tab[2]['table']          = 'glpi_plugin_connections_connectiontypes';
      $tab[2]['field']          = 'name';
      $tab[2]['linkfield']      = 'plugin_connections_connectiontypes_id';
      $tab[2]['name']           = __('Type of Connections', 'connections');
      $tab[2]['displaytype']    = 'dropdown';
      $tab[2]['checktype']      = 'text';
      $tab[2]['injectable']     = true;

      $tab[3]['table']          = 'glpi_users';
      $tab[3]['field']          = 'name';
      $tab[3]['linkfield']      = 'users_id';
      $tab[3]['name']           = __('Technician in charge of the hardware');
      $tab[3]['displaytype']    = 'user';
      $tab[3]['checktype']      = 'text';
      $tab[3]['injectable']     = true;

      $tab[4]['table']          = 'glpi_suppliers';
      $tab[4]['field']          = 'name';
      $tab[4]['linkfield']      = 'suppliers_id';
      $tab[4]['name']           = __('Supplier');
      $tab[4]['datatype']       = 'itemlink';
      $tab[4]['itemlink_type']  = 'Supplier';
      $tab[4]['forcegroupby']   = true;
      $tab[4]['displaytype']    = 'supplier';
      $tab[4]['checktype']      = 'text';
      $tab[4]['injectable']     = true;

      $tab[5]['table']          = 'glpi_plugin_connections_connectionrates';
      $tab[5]['field']          = 'name';
      $tab[5]['linkfield']      = 'plugin_connections_connectionrates_id';
      $tab[5]['name']           = __('Rates', 'connections');
      $tab[5]['displaytype']    = 'dropdown';
      $tab[5]['checktype']      = 'text';
      $tab[5]['injectable']     = true;

      $tab[6]['table']          = 'glpi_plugin_connections_guaranteedconnectionrates';
      $tab[6]['field']          = 'name';
      $tab[6]['linkfield']      = 'plugin_connections_guaranteedconnectionrates_id';
      $tab[6]['name']           = __('Guaranteed Rates', 'connections');
      $tab[6]['displaytype']    = 'dropdown';
      $tab[6]['checktype']      = 'text';
      $tab[6]['injectable']     = true;

      $tab[7]['table']          = $this->getTable();
      $tab[7]['field']          = 'comment';
      $tab[7]['linkfield']      = 'comment';
      $tab[7]['name']           = _('Comments');
      $tab[7]['datatype']       = 'text';
      $tab[7]['datatype']       = 'text';
      $tab[7]['displaytype']    = 'multiline_text';
      $tab[7]['injectable']     = true;

      $tab[8]['table']          = 'glpi_plugin_connections_connections_items';
      $tab[8]['field']          = 'items_id';
      $tab[8]['linkfield']      = '';
      $tab[8]['name']           = _('Associated element');
      $tab[8]['injectable']     = false;
      $tab[8]['massiveaction']  = false;

      $tab[9]['table']          = $this->getTable();
      $tab[9]['field']          = 'others';
      $tab[9]['linkfield']      = 'others';
      $tab[9]['name']           = _('Other');
      $tab[9]['displaytype']    = 'text';
      $tab[9]['checktype']      = 'text';
      $tab[9]['injectable']     = true;

      $tab[10]['table']         = 'glpi_groups';
      $tab[10]['field']         = 'name';
      $tab[10]['linkfield']     = 'groups_id';
      $tab[10]['name']          = __('Group');
      $tab[10]['displaytype']   = 'dropdown';
      $tab[10]['checktype']     = 'text';
      $tab[10]['injectable']    = true;

      $tab[11]['table']         = $this->getTable();
      $tab[11]['field']         = 'is_helpdesk_visible';
      $tab[11]['linkfield']     = 'is_helpdesk_visible';
      $tab[11]['name']          = __('Associable to a ticket');
      $tab[11]['datatype']      = 'bool';
      $tab[11]['displaytype']   = 'bool';
      $tab[11]['checktype']     = 'decimal';
      $tab[11]['injectable']    = true;

      $tab[12]['table']         = $this->getTable();
      $tab[12]['field']         = 'date_mod';
      $tab[12]['linkfield']     = 'date_mod';
      $tab[12]['name']          = __('Last update');
      $tab[12]['datatype']      = 'datetime';
      $tab[12]['displaytype']   = 'date';
      $tab[12]['checktype']     = 'date';
      $tab[12]['injectable']    = true;

      $tab[18]['table']         = $this->getTable();
      $tab[18]['field']         = 'is_recursive';
      $tab[18]['linkfield']     = 'is_recursive';
      $tab[18]['name']          = __('Child entities');
      $tab[18]['datatype']      = 'bool';
      $tab[18]['displaytype']   = 'bool';
      $tab[18]['checktype']     = 'decimal';
      $tab[18]['injectable']    = true;

      $tab[30]['table']         = $this->getTable();
      $tab[30]['field']         = 'id';
      $tab[30]['linkfield']     = '';
      $tab[30]['name']          = __('ID');
      $tab[30]['injectable']    = false;
      $tab[30]['massiveaction'] = false;

      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = __('Entity');
      $tab[80]['injectable']    = false;

      return $tab;
   }

   public function defineTabs($options = array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginConnectionsConnection_Item', $ong, $options);
      if ($this->fields['id'] > 0) {
         $this->addStandardTab('Ticket', $ong, $options);
         $this->addStandardTab('Item_Problem', $ong, $options);
         $this->addStandardTab('Infocom', $ong, $options);
         $this->addStandardTab('Contract_Item', $ong, $options);
         $this->addStandardTab('Document_Item', $ong, $options);
         $this->addStandardTab('Note', $ong, $options);
         $this->addStandardTab('Log', $ong, $options);
      }
      return $ong;
   }

   function getSpecificMassiveActions($checkitem=NULL) {

      $actions = array();
      if (Session::haveRight("plugin_connections_connection", UPDATE)) {
         $actions[__CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR.'transfert'] = __('Transfer');
      }

      return $actions;
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'transfert' :
            Dropdown::show('Entity');
            echo "<br><br>".Html::submit(__('Post'),
                                         array('name' => 'massiveaction'));
            return true;

      }
      return parent::showMassiveActionsSubForm($ma);
   }

   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $pfAgent = new self();

      switch ($ma->getAction()) {

         case 'transfert' :
            foreach ($ids as $key) {
               if ($pfAgent->getFromDB($key)) {
                  $input = array();
                  $input['id'] = $key;
                  $input['entities_id'] = $_POST['entities_id'];
                  if ($pfAgent->update($input)) {
                     //set action massive ok for this item
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     // KO
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;
            break;
      }

      return;
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   public function getSelectLinkedItem ()
   {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_connections_connections_items`
              WHERE `plugin_connections_connections_id` = '" . $this->fields['id'] . "'";
   }

   public function showForm ($ID, $options = array())
   {
      global $CFG_GLPI;

      if (!$this->canView()) return false;

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $this->check(-1, UPDATE);
         $this->getEmpty();
      }

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Name') . " : </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";

      echo "<td>" . __('Other') . " : </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"others");
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Supplier') . " : </td>";
      echo "<td>";
      Supplier::dropdown(array(
         'name'   => "suppliers_id",
         'value'  => $this->fields["suppliers_id"],
         'entity' => $this->fields["entities_id"],
      ));
      echo "</td>";

      echo "<td>" . __('Rates', 'connections') . " : </td>";
      echo "<td>";
      PluginConnectionsConnectionRate::dropdown(array(
         'name'   => "plugin_connections_connectionrates_id",
         'value'  => $this->fields["plugin_connections_connectionrates_id"],
         'entity' => $this->fields["entities_id"],
      ));
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Type of Connections', 'connections') . " : </td><td>";
      PluginConnectionsConnectionType::dropdown(array(
         'name'   => "plugin_connections_connectiontypes_id",
         'value'  => $this->fields["plugin_connections_connectiontypes_id"],
         'entity' => $this->fields["entities_id"],
      ));
      echo "</td>";

      echo "<td>" . __('Guaranteed Rates', 'connections') . " : </td>";
      echo "<td>";
      PluginConnectionsGuaranteedConnectionRate::dropdown(array(
         'name'   => "plugin_connections_guaranteedconnectionrates_id",
         'value'  => $this->fields["plugin_connections_guaranteedconnectionrates_id"],
         'entity' => $this->fields["entities_id"],
      ));
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Technician in charge of the hardware') . " : </td><td>";
      User::dropdown(array(
         'value'  => $this->fields["users_id"],
         'entity' => $this->fields["entities_id"],
         'right'  => 'all'
      ));
      echo "</td>";

      echo "<td>" . __('Associable to a ticket') . " :</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Group') . " : </td><td>";
      Group::dropdown(array(
         'name'   => "groups_id",
         'value'  => $this->fields["groups_id"],
         'entity' => $this->fields["entities_id"],
      ));
      echo "</td>";

      echo "<td>" . __('Last update') . " : </td>";
      echo "<td>" . Html::convDateTime($this->fields["date_mod"]) . "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Comments') . " : </td>";
      echo "<td class='center' colspan='3'>";
      echo "<textarea cols='35' rows='4' name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   public function dropdownConnections($myname, $entity_restrict = '', $used = array())
   {
      global $DB, $CFG_GLPI;

      $rand             = mt_rand();
      $table            = $this->getTable();
      $entitiesRestrict = getEntitiesRestrictRequest(
         "AND",
         $this->getTable(),
         '',
         $entity_restrict,
         true
      );
      $whereUsed = (count($used))
                  ? " AND `id` NOT IN (0," . implode(',', $used) . ")"
                  : '';

      $query = "SELECT *
                FROM `glpi_plugin_connections_connectiontypes`
                WHERE `id` IN (
                   SELECT DISTINCT `plugin_connections_connectiontypes_id`
                   FROM `$table`
                   WHERE `is_deleted` = '0'
                   $entitiesRestrict
                   $whereUsed
                )
                GROUP BY `name`
                ORDER BY `name`";
      $result = $DB->query($query);

      echo "<select name='_type' id='plugin_connections_connectiontypes_id'>\n";
      echo "<option value='0'>" . Dropdown::EMPTY_VALUE . "</option>\n";
      while ($data = $DB->fetch_assoc($result)) {
         echo "<option value='" . $data['id'] . "'>" . $data['name'] . "</option>\n";
      }
      echo "</select>\n";

      $params = array(
         'plugin_connections_connectiontypes_id' => '__VALUE__',
         'entity_restrict'                       => $entity_restrict,
         'rand'                                  => $rand,
         'myname'                                => $myname,
         'used'                                  => $used,
      );

      Ajax::updateItemOnSelectEvent(
         "plugin_connections_connectiontypes_id",
         "show_$myname$rand",
         $CFG_GLPI["root_doc"] . "/plugins/connections/ajax/dropdownTypeConnections.php",
         $params
      );

      echo "<span id='show_$myname$rand'>";
      $_POST["entity_restrict"]                       = $entity_restrict;
      $_POST["plugin_connections_connectiontypes_id"] = 0;
      $_POST["myname"]                                = $myname;
      $_POST["rand"]                                  = $rand;
      $_POST["used"]                                  = $used;
      include (GLPI_ROOT . "/plugins/connections/ajax/dropdownTypeConnections.php");
      echo "</span>\n";

      return $rand;
   }
   
}
