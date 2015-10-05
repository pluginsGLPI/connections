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

class PluginConnectionsConnection extends CommonDBTM {
	
   // From CommonDBTM
   public $dohistory = true;

   static $rightname = 'budget'; //TODO : A adapter (pompé sur budget.class.php du coeur)

   protected $usenotepad = true; //For 0.90 (and 0.85)
   
   static function getTypeName($nb = 0) {
      return __('Connections', 'connections');
   }
   
   function defineTabs($options = array()) {
      $ong = array();

      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginConnectionsConnection_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Infocom', $ong, $options);
      $this->addStandardTab('Contract_Item', $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   static function canCreate() {
      return plugin_connections_haveRight('connections', 'w');
   }

   static function canView() {
      return plugin_connections_haveRight('connections', 'r');
   }
	
	function cleanDBonPurge() {
		$temp = new PluginConnectionsConnection_Item();
      $temp->clean(array('plugin_connections_connections_id' => $this->fields['id']));
	}
	
	function getSearchOptions() {

      $tab = array();
      $tab['common'] = self::getTypeName(2);

      $tab[1]['table']=$this->getTable();
      $tab[1]['field']='name';
      $tab[1]['linkfield']='name';
      $tab[1]['name']=__('Name');
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      //datainjection
      $tab[1]['displaytype']  = 'text';
      $tab[1]['checktype']  = 'text';
      $tab[1]['injectable']  = true;
      
      $tab[2]['table']='glpi_plugin_connections_connectiontypes'; //PluginConnectionsConnectionType
      $tab[2]['field']='name';
      $tab[2]['linkfield']='plugin_connections_connectiontypes_id';
      $tab[2]['name']= _n('Type', 'Types', 1);
      $tab[2]['datatype']='itemlink';
      $tab[2]['itemlink_type'] = $this->getType();
      //datainjection
      $tab[2]['displaytype']  = 'dropdown';
      $tab[2]['checktype']  = 'text';
      $tab[2]['injectable']  = true;
      
      $tab[3]['table']='glpi_users';
      $tab[3]['field']='name';
      $tab[3]['linkfield']='users_id';
      $tab[3]['name']=__("Technical reference", 'connections');
      $tab[3]['datatype']='itemlink';
      $tab[3]['itemlink_type'] = $this->getType();
      //datainjection
      $tab[3]['displaytype']  = 'user';
      $tab[3]['checktype']  = 'text';
      $tab[3]['injectable']  = true;
      
      $tab[4]['table']='glpi_suppliers';
      $tab[4]['field']='name';
      $tab[4]['linkfield']='suppliers_id';
      $tab[4]['name']= Supplier::getTypeName(1);
      $tab[4]['datatype']='itemlink';
      $tab[4]['itemlink_type']='Supplier';
      $tab[4]['forcegroupby']=true;
      //datainjection
      $tab[4]['displaytype']  = 'supplier';
      $tab[4]['checktype']  = 'text';
      $tab[4]['injectable']  = true;
      
      //Note : très très moyen (en 0.85 au moins)
      $tab[5]['table']        = 'glpi_plugin_connections_connectionrates'; //PluginConnectionsConnectionRate
      $tab[5]['field']        = 'name';
      $tab[5]['linkfield']    = 'plugin_connections_connectionrates_id';
      $tab[5]['name']         = PluginConnectionsConnectionRate::getTypeName(2);
      //datainjection
      $tab[5]['displaytype']  = 'dropdown';
      $tab[5]['checktype']    = 'text';
      $tab[5]['injectable']   = true;
      
      $tab[6]['table']        = 'glpi_plugin_connections_guaranteedconnectionrates'; //PluginConnectionsGuaranteedConnectionRate
      $tab[6]['field']        = 'name';
      $tab[6]['linkfield']    = 'plugin_connections_guaranteedconnectionrates_id';
      $tab[6]['name']         = PluginConnectionsGuaranteedConnectionRate::getTypeName(2);
      //datainjection
      $tab[6]['displaytype']  = 'dropdown';
      $tab[6]['checktype']    = 'text';
      $tab[6]['injectable']   = true;
      
      $tab[7]['table']=$this->getTable();
      $tab[7]['field']='comment';
      $tab[7]['linkfield']='comment';
      $tab[7]['name']=__('Comments');
      $tab[7]['datatype']='text';
      //datainjection
      $tab[7]['datatype']  =  'text';
      $tab[7]['displaytype']  = 'multiline_text';
      $tab[7]['injectable']  = true;
      
      $tab[8]['table']='glpi_plugin_connections_connections_items'; //PluginConnectionsConnection_Item
      $tab[8]['field']='items_id';
      $tab[8]['linkfield']='';
      $tab[8]['name']         = __("Linked elements", 'connections');
      //datainjection
      $tab[8]['injectable']  = false;
      $tab[8]['massiveaction'] = false;
      
      $tab[9]['table']=$this->getTable();
      $tab[9]['field']='others';
      $tab[9]['linkfield']='others';
      $tab[9]['name']=__('Others');
      //datainjection
      $tab[9]['displaytype']  = 'text';
      $tab[9]['checktype']  = 'text';
      $tab[9]['injectable']  = true;
      
      $tab[10]['table']='glpi_groups';
      $tab[10]['field']='name';
      $tab[10]['linkfield']='groups_id';
      $tab[10]['name']= Group::getTypeName();
      //datainjection
      $tab[10]['displaytype']  = 'dropdown';
      $tab[10]['checktype']  = 'text';
      $tab[10]['injectable']  = true;
      
      $tab[11]['table']=$this->getTable();
      $tab[11]['field']='is_helpdesk_visible';
      $tab[11]['linkfield']='is_helpdesk_visible';
      $tab[11]['name']=__('Associable to a ticket');
      $tab[11]['datatype']='bool';
      //datainjection
      $tab[11]['displaytype']  = 'bool';
      $tab[11]['checktype']  = 'decimal';
      $tab[11]['injectable']  = true;
      
      $tab[12]['table']=$this->getTable();
      $tab[12]['field']='date_mod';
      $tab[12]['linkfield']='date_mod';
      $tab[12]['name']=__('Last update');
      $tab[12]['datatype']='datetime';
      //datainjection
      $tab[12]['displaytype']  = 'date';
      $tab[12]['checktype']  = 'date';
      $tab[12]['injectable']  = true;
      
      $tab[18]['table']=$this->getTable();
      $tab[18]['field']='is_recursive';
      $tab[18]['linkfield']='is_recursive';
      $tab[18]['name']=__('Child entities');
      $tab[18]['datatype']='bool';
      //datainjection
      $tab[18]['displaytype']  = 'bool';
      $tab[18]['checktype']  = 'decimal';
      $tab[18]['injectable']  = true;
      
      $tab[30]['table']=$this->getTable();
      $tab[30]['field']='id';
      $tab[30]['linkfield']='';
      $tab[30]['name']=__('ID');
      //datainjection
      $tab[30]['injectable']  = false;
      $tab[30]['massiveaction'] = false;
      
      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['linkfield']      = 'entities_id';
      $tab[80]['name']           = Entity::getTypeName();
      $tab[80]['datatype']       =  'itemlink';
      $tab[80]['itemlink_type']  = $this->getType();
      //datainjection
      $tab[80]['injectable']     = false;
		
		return $tab;
   }

   // Note : can factorize this two functions
	
	function prepareInputForAdd($input) {
		
      // For date fields
      foreach (array('date_creation', 'date_expiration') as $field) {
         if (isset($input[$field]) && empty($input[$field])) {
            $input[$field] = 'NULL';
         }
      }
		
		return $input;
	}
	
	function prepareInputForUpdate($input) {
		
      // For date fields
      foreach (array('date_creation', 'date_expiration') as $field) {
         if (isset($input[$field]) && empty($input[$field])) {
            $input[$field] = 'NULL';
         }
      }
		
		return $input;
	}
	
	/*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem() {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_connections_connections_items`
              WHERE `plugin_connections_connections_id`='" . $this->fields['id']."'";
   }
   
	function showForm($ID, $options=array()) {
		
		if (!$this->canView()) return false;
		
      //TODO : Old, need to be ported in 0.85

      /*
		if ($ID > 0) {
         $this->check($ID, 'r');
      } else {
         // Create item
         $this->check(-1, 'w'); //ARF
      }
      */

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      
      echo "<td><label>".__('Name')."</label></td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      
      echo "<td><label>".__('Others')."</label></td>";
      echo "<td>";
      Html::autocompletionTextField($this, "others");	
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td><label>".Supplier::getTypeName(1)."</label></td>";
      echo "<td>";
      Supplier::dropdown(array('name' => "suppliers_id",
                              'value' => $this->fields["suppliers_id"],
                              'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td><label>".PluginConnectionsConnectionRate::getTypeName(2)."</label></td>";
      echo "<td>";
      PluginConnectionsConnectionRate::dropdown(array(
         'name' => "plugin_connections_connectionrates_id",
         'value' => $this->fields["plugin_connections_connectionrates_id"],
         'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td><label>"._n('Type', 'Types', 1)."</label></td>";
      echo "<td>";
      PluginConnectionsConnectionType::dropdown(array(
         'name' => "plugin_connections_connectiontypes_id",
         'value' => $this->fields["plugin_connections_connectiontypes_id"],
         'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td><label>".__("Guaranteed Rates", 'connections')."</label></td>";
      echo "<td>";
      PluginConnectionsGuaranteedConnectionRate::dropdown(array(
         'name' => "plugin_connections_guaranteedconnectionrates_id",
         'value' => $this->fields["plugin_connections_guaranteedconnectionrates_id"],
         'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td><label>" . __("Technical reference", 'connections') . "</label></td><td>";
      User::dropdown(array('value' => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right' => 'all'));
      echo "</td>";
      
      //Note : Ce champs n'est pas respecté dans Ticket !
      echo "<td><label>" . __('Associable to a ticket') . "</label></td><td>";
      Dropdown::showYesNo('is_helpdesk_visible', $this->fields['is_helpdesk_visible']);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td><label>".__('Group')."</label></td><td>";
      Group::dropdown(array('name' => "groups_id",
                           'value' => $this->fields["groups_id"],
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td><label>".__('Last update')."</label></td>";
      echo "<td>".Html::convDateTime($this->fields["date_mod"])."</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td colspan='2'></td>";
      
      echo "<td><label>" . __('Comments') . "</label></td>";
      echo "<td class='center'>";
      echo "<textarea cols='35' rows='4' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td>";
      
      echo "</tr>";

      $this->showFormButtons($options);
      Html::closeForm();

      return true;
	}
	
   //Note : Fonction utilisée à deux endroits (?)
	function dropdownConnections($myname, $entity_restrict = '', $used = array()) {
      global $DB, $CFG_GLPI;

      $where  = " WHERE `".$this->getTable()."`.`is_deleted` = '0' ";
      $where .= getEntitiesRestrictRequest("AND",$this->getTable(),'',$entity_restrict,true);

      if (count($used)) {
         $where .= " AND `id` NOT IN (0";
         foreach ($used as $ID) //Note : simplification possible
            $where .= ",$ID";
         $where .= ")";
      }
      var_dump($where);

      $query="SELECT *
        FROM `glpi_plugin_connections_connectiontypes`
        WHERE `id` IN (
          SELECT DISTINCT `plugin_connections_connectiontypes_id`
          FROM `".$this->getTable()."`
          $where)
        GROUP BY `name`
        ORDER BY `name`";
      $result = $DB->query($query);

      echo "<select name='_type' id='plugin_connections_connectiontypes_id'>";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";

      while ($data = $DB->fetch_assoc($result)) {
         echo "<option value='".$data['id']."'>".$data['name']."</option>";
      }
      echo "</select>";

      $rand = mt_rand();

      $params = array('plugin_connections_connectiontypes_id'=>'__VALUE__',
                    'entity_restrict'=>$entity_restrict,
                    'rand'=>$rand,
                    'myname'=>$myname,
                    'used'=>$used);

      Ajax::updateItemOnSelectEvent("plugin_connections_connectiontypes_id",
         "show_$myname$rand",
         $CFG_GLPI["root_doc"]."/plugins/connections/ajax/dropdownTypeConnections.php",
         $params);

      $_POST["entity_restrict"]=$entity_restrict;
      $_POST["plugin_connections_connectiontypes_id"]=0;
      $_POST["myname"]=$myname;
      $_POST["rand"]=$rand;
      $_POST["used"]=$used;

      echo "<span id='show_$myname$rand'>";
      include (GLPI_ROOT."/plugins/connections/ajax/dropdownTypeConnections.php");

      //TODO : PHP Notice: Undefined index: searchText (quand vide)
      //include (GLPI_ROOT."/plugins/connections/ajax/dropdownConnections.php"); //Ligne ajoutée pour DEBUG
      echo "</span>\n";

      return $rand;
   }
  
  // Cron action
   static function cronInfo($name) {
      global $LANG;
       
      switch ($name) {
         case 'ConnectionsAlert':
            return array('description' => $LANG['plugin_connections']['mailing'][3]);
            break;
      }
      return array();
   }

   static function queryExpiredConnections() {
      
      $PluginConnectionsConfig = new PluginConnectionsConfig();
      $PluginConnectionsConfig->getFromDB(1);
      
      $delay = $PluginConnectionsConfig->fields["delay_expired"];

      $query = "SELECT * 
         FROM `".$this->getTable()."`
         WHERE `date_expiration` IS NOT NULL
            AND `is_deleted` = '0'
            AND DATEDIFF(CURDATE(),`date_expiration`) > $delay AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";

      return $query;
   }
   
   static function queryConnectionsWhichExpire() {
      
      $PluginConnectionsConfig = new PluginConnectionsConfig();
      $PluginConnectionsConfig->getFromDB(1);

      $delay = $PluginConnectionsConfig->fields["delay_whichexpire"];
      
      $query = "SELECT *
         FROM `".$this->getTable()."`
         WHERE `date_expiration` IS NOT NULL
            AND `is_deleted` = '0'
            AND DATEDIFF(CURDATE(),`date_expiration`) > -$delay AND DATEDIFF(CURDATE(),`date_expiration`) < 0 ";

      return $query;
   }

   /**
    * Cron action on connections : ExpiredConnections or ConnectionsWhichExpire
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronConnectionsAlert($task=NULL) {
      global $DB, $CFG_GLPI, $LANG;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $domain_infos = array();
      $domain_messages = array();

      $querys = array(Alert::NOTICE => self::queryConnectionsWhichExpire(),
                     Alert::END => self::queryExpiredConnections());

      $PluginConnectionsConnection = new self();

      foreach ($querys as $type => $query) {
         $domain_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        Html::convDate($data["date_expiration"])."<br>\n";
            $domain_infos[$type][$entity][] = $data;

            if (!isset($connections_infos[$type][$entity])) {
               $domain_messages[$type][$entity] = $LANG['plugin_connections']['mailing'][0]."<br />";
            }
            $domain_messages[$type][$entity] .= $message;
         }
      
         Plugin::loadLang('connections'); // Note : old, à porter en 0.85 ?

         $event_name = ($type == Alert::NOTICE) ? "ConnectionsWhichExpire" : "ExpiredConnections";

         foreach ($domain_infos[$type] as $entity => $connections) {

            if (NotificationEvent::raiseEvent($event_name,
                                              $PluginConnectionsConnection,
                                              array('entities_id'=>$entity,
                                                    'connections'=>$connections))) {
               $message = $domain_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities", $entity).": $message\n");
                  //$task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                    $entity).":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",$entity).
                             ":  Send connections alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send connections alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   static function configCron($target) {
      $PluginConnectionsConfig = new PluginConnectionsConfig();
      $PluginConnectionsConfig->showForm($target, 1); //ID 1
   }

   /**
    * Get the specific massive actions
    *
    * @since version 0.84
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions 
    * */
   function getSpecificMassiveActions($checkitem = NULL) { //Note : Used !

      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      $prefix = $this->getType().MassiveAction::CLASS_ACTION_SEPARATOR;
      
      if ($isadmin) {
         $actions[$prefix.'install']      = __('Link', 'connections');
         $actions[$prefix."desinstall"]   = _x('button', 'Dissociate');
         $actions[$prefix."transfert"]    = __('Transfer');
      }
      //var_dump($actions);
      return $actions;
   }

   /**
    * Massive actions display
    * 
    * @param $input array of input datas
    *
    * @return array of results (nbok, nbko, nbnoright counts)
    * */
   static function showMassiveActionsSubForm(MassiveAction $ma) { //Note : Used too
      
      $itemtype = $ma->getItemtype(false);
      switch ($itemtype) {
         case self::getType():
            switch ($ma->getAction()) {
               case "install":
               case "desinstall":
                  Dropdown::showAllItems("item_item",0,0,-1,PluginConnectionsConnection_Item::getClasses(true));
                  break;
               case "transfert":
                  //Note : can don't show '+' button with this dropdown
                  Entity::dropdown(array('width' => '50%'));
                  echo "&nbsp;";
                  break;
            }
            return parent::showMassiveActionsSubForm($ma);
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      if (! count($ids)) {
         return ;
      }

      $PluginConnectionsConnection      = new self();
      $PluginConnectionsConnection_Item = new PluginConnectionsConnection_Item();

      $itemtype   = $ma->getItemtype(false); //useless
      $input      = $ma->getInput();

      switch ($ma->getAction()) {
         //TODO : Tester l'itemtype (par cohérence)
         case "add_item": //Not used now (et pas forcément au bon endroit)
            foreach ($data["item"] as $key => $val) {
                  $input = array('plugin_connections_connections_id' => $input['plugin_connections_connections_id'],
                                 'items_id'      => $key,
                                 'itemtype'      => $input['itemtype']);
                  if ($PluginConnectionsConnection_Item->can(-1, 'w', $input)) {
                     if ($PluginConnectionsConnection_Item->add($input)) {
                        $ma->itemDone($PluginConnectionsConnection_Item->getType(), $key, MassiveAction::ACTION_OK);
                     } else {
                        //Note : could be add a (string) error message for user
                        $ma->itemDone($PluginConnectionsConnection_Item->getType(), $key, MassiveAction::ACTION_KO);
                     }
                  } else {
                     $ma->itemDone($PluginConnectionsConnection_Item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                     $ma->addMessage($PluginConnectionsConnection_Item->getErrorMessage(ERROR_RIGHT)); //PluginConnectionsConnection_Item ou PluginConnectionsConnection ?
                  }
            }
            break;

         case "install": //Works
            foreach ($ids as $key => $val) {
               $input = array('plugin_connections_connections_id' => $key,
                              'items_id'      => $input["item_item"],
                              'itemtype'      => $input['itemtype']);
               if ($PluginConnectionsConnection_Item->can(-1, 'w', $input)) {
                  if ($PluginConnectionsConnection_Item->add($input)) {
                     $ma->itemDone($PluginConnectionsConnection->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     //Note : could be add a (string) error message for user
                     $ma->itemDone($PluginConnectionsConnection->getType(), $key, MassiveAction::ACTION_KO);
                  }
               } else {
                  $ma->itemDone($PluginConnectionsConnection->getType(), $key, MassiveAction::ACTION_NORIGHT);
                  $ma->addMessage($PluginConnectionsConnection->getErrorMessage(ERROR_RIGHT));
               }
            }
            break;

         case "desinstall": //Works
            foreach ($ids as $key => $val) {
               if ($PluginConnectionsConnection_Item->deleteItemByConnectionsAndItem($key, $input['item_item'], $input['itemtype'])) {
                  $ma->itemDone($PluginConnectionsConnection->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  //Note : could be add a (string) error message for user
                  $ma->itemDone($PluginConnectionsConnection->getType(), $key, MassiveAction::ACTION_KO);
               }
            }
            break;

         case "transfert": //For transfert Entity of a Connection
            foreach ($ids as $key => $val) {
               if ($PluginConnectionsConnection->getFromDB($key)) { //"Security"
                  // TODO : Faire fonctionner 'Transfert du Type' alors que non fonctionnel en 0.84
                  //$type = PluginConnectionsConnectionType::transfer($PluginConnectionsConnection->fields["plugin_connections_connectiontypes_id"],
                  //                                                $input['entities_id']);

                  //Note : can use/call that :
                  //$rate = PluginConnectionsConnectionRate::transfert($ID, $entity);
                  //$connectionrate = PluginConnectionsGuaranteedConnectionRate::transfert($ID, $entity);

                  $values = array('id' => $key,
                                // 'plugin_connections_connections_id' => $type,
                                 'entities_id' => $input['entities_id']);

                  if ($PluginConnectionsConnection->update($values)) {
                     $ma->itemDone($PluginConnectionsConnection->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     //Note : could be add a (string) error message for user, like 'Saved failed'
                     $ma->itemDone($PluginConnectionsConnection->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            break;
      }
   }
   
}
