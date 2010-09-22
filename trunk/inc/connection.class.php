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
// Original Author of file: CAILLAUD Xavier
// Purpose of file: plugin connections v1.3.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginConnectionsConnection extends CommonDBTM {
	
   public $dohistory=true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_connections']['title'][1];
   }
   
   function canCreate() {
      return plugin_connections_haveRight('connections', 'w');
   }

   function canView() {
      return plugin_connections_haveRight('connections', 'r');
   }
	
	function cleanDBonPurge() {

		$temp = new PluginConnectionsConnection_Item();
      $temp->clean(array('plugin_connections_connections_id' => $this->fields['id']));

	}
	
	function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_connections']['title'][1];

      $tab[1]['table']=$this->getTable();
      $tab[1]['field']='name';
      $tab[1]['linkfield']='name';
      $tab[1]['name']=$LANG['plugin_connections'][7];
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      //datainjection
      $tab[1]['displaytype']  = 'text';
      $tab[1]['checktype']  = 'text';
      $tab[1]['injectable']  = true;
      
      $tab[2]['table']='glpi_plugin_connections_connectiontypes';
      $tab[2]['field']='name';
      $tab[2]['linkfield']='plugin_connections_connectiontypes_id';
      $tab[2]['name']=$LANG['plugin_connections'][12];
      //datainjection
      $tab[2]['displaytype']  = 'dropdown';
      $tab[2]['checktype']  = 'text';
      $tab[2]['injectable']  = true;
      
      $tab[3]['table']='glpi_users';
      $tab[3]['field']='name';
      $tab[3]['linkfield']='users_id';
      $tab[3]['name']=$LANG['plugin_connections'][18];
      //datainjection
      $tab[3]['displaytype']  = 'user';
      $tab[3]['checktype']  = 'text';
      $tab[3]['injectable']  = true;
      
      $tab[4]['table']='glpi_suppliers';
      $tab[4]['field']='name';
      $tab[4]['linkfield']='suppliers_id';
      $tab[4]['name']=$LANG['plugin_connections'][2];
      $tab[4]['datatype']='itemlink';
      $tab[4]['itemlink_type']='Supplier';
      $tab[4]['forcegroupby']=true;
      //datainjection
      $tab[4]['displaytype']  = 'supplier';
      $tab[4]['checktype']  = 'text';
      $tab[4]['injectable']  = true;
      
      $tab[5]['table']='glpi_plugin_connections_connectionrates';
      $tab[5]['field']='name';
      $tab[5]['linkfield']='plugin_connections_connectionrates_id';
      $tab[5]['name']=$LANG['plugin_connections']['setup'][3];
      //datainjection
      $tab[5]['displaytype']  = 'dropdown';
      $tab[5]['checktype']  = 'text';
      $tab[5]['injectable']  = true;
      
      $tab[6]['table']='glpi_plugin_connections_connectionratesguaranteed';
      $tab[6]['field']='name';
      $tab[6]['linkfield']='plugin_connections_connectionratesguaranteed_id';
      $tab[6]['name']=$LANG['plugin_connections']['setup'][4];
      //datainjection
      $tab[6]['displaytype']  = 'dropdown';
      $tab[6]['checktype']  = 'text';
      $tab[6]['injectable']  = true;
      
      $tab[7]['table']=$this->getTable();
      $tab[7]['field']='comment';
      $tab[7]['linkfield']='comment';
      $tab[7]['name']=$LANG['plugin_connections'][10];
      $tab[7]['datatype']='text';
      //datainjection
      $tab[7]['datatype']  =  'text';
      $tab[7]['displaytype']  = 'multiline_text';
      $tab[7]['injectable']  = true;
      
      $tab[8]['table']='glpi_plugin_connections_connections_items';
      $tab[8]['field']='items_id';
      $tab[8]['linkfield']='';
      $tab[8]['name']=$LANG['plugin_connections'][6];
      //datainjection
      $tab[8]['injectable']  = false;
      
      $tab[9]['table']=$this->getTable();
      $tab[9]['field']='others';
      $tab[9]['linkfield']='others';
      $tab[9]['name']=$LANG['plugin_connections'][16];
      //datainjection
      $tab[9]['displaytype']  = 'text';
      $tab[9]['checktype']  = 'text';
      $tab[9]['injectable']  = true;
      
      $tab[10]['table']='glpi_groups';
      $tab[10]['field']='name';
      $tab[10]['linkfield']='groups_id';
      $tab[10]['name']=$LANG['common'][35];
      //datainjection
      $tab[10]['displaytype']  = 'dropdown';
      $tab[10]['checktype']  = 'text';
      $tab[10]['injectable']  = true;
      
      $tab[11]['table']=$this->getTable();
      $tab[11]['field']='is_helpdesk_visible';
      $tab[11]['linkfield']='is_helpdesk_visible';
      $tab[11]['name']=$LANG['software'][46];
      $tab[11]['datatype']='bool';
      //datainjection
      $tab[11]['displaytype']  = 'bool';
      $tab[11]['checktype']  = 'decimal';
      $tab[11]['injectable']  = true;
      
      $tab[12]['table']=$this->getTable();
      $tab[12]['field']='date_mod';
      $tab[12]['linkfield']='date_mod';
      $tab[12]['name']=$LANG['common'][26];
      $tab[12]['datatype']='datetime';
      //datainjection
      $tab[12]['displaytype']  = 'date';
      $tab[12]['checktype']  = 'date';
      $tab[12]['injectable']  = true;
      
      $tab[18]['table']=$this->getTable();
      $tab[18]['field']='is_recursive';
      $tab[18]['linkfield']='is_recursive';
      $tab[18]['name']=$LANG['entity'][9];
      $tab[18]['datatype']='bool';
      //datainjection
      $tab[18]['displaytype']  = 'bool';
      $tab[18]['checktype']  = 'decimal';
      $tab[18]['injectable']  = true;
      
      $tab[30]['table']=$this->getTable();
      $tab[30]['field']='id';
      $tab[30]['linkfield']='';
      $tab[30]['name']=$LANG['common'][2];
      //datainjection
      $tab[30]['injectable']  = false;
      
      $tab[80]['table']='glpi_entities';
      $tab[80]['field']='completename';
      $tab[80]['linkfield']='entities_id';
      $tab[80]['name']=$LANG['entity'][0];
      //datainjection
      $tab[80]['injectable']  = false;
		
		return $tab;
   }
   
	function defineTabs($options=array()) {
		global $LANG;
		
		$ong[1]=$LANG['title'][26];
		if ($this->fields['id'] > 0) {
			if (haveRight("show_all_ticket","1")) {
				$ong[6]=$LANG['title'][28];
			}
			if (haveRight("contract","r")) {
				$ong[8]=$LANG['Menu'][26];
			}
			if (haveRight("document","r"))
				$ong[9]=$LANG['Menu'][27];
			if (haveRight("notes","r"))
				$ong[10]=$LANG['title'][37];
			$ong[12]=$LANG['title'][38];
		}
		return $ong;
	}
	
	function prepareInputForAdd($input) {
		
		if (isset($input['date_creation'])&&empty($input['date_creation'])) 
         $input['date_creation']='NULL';
		if (isset($input['date_expiration'])&&empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';
		
		return $input;
	}
	
	function prepareInputForUpdate($input) {
		
		if (isset($input['date_creation'])&&empty($input['date_creation'])) 
         $input['date_creation']='NULL';
		if (isset($input['date_expiration'])&&empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';
		
		return $input;
	}
	
	/*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_connections_connections_items`
              WHERE `plugin_connections_connections_id`='" . $this->fields['id']."'";
   }
   
	function showForm ($ID, $options=array()) {
      global $CFG_GLPI,$LANG;
		
		if (!$this->canView()) return false;
		
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }
    
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_connections'][7].": </td>";
      echo "<td>";
      autocompletionTextField($this,"name");
      echo "</td>";
      
      echo "<td>".$LANG['plugin_connections'][16].":	</td>";
      echo "<td>";
      autocompletionTextField($this,"others");	
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_connections'][2].": </td>";
      echo "<td>";
      Dropdown::show('Supplier', array('name' => "suppliers_id",'value' => $this->fields["suppliers_id"], 'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".$LANG['plugin_connections']['setup'][3].": </td>";
      echo "<td>";
      Dropdown::show('PluginConnectionsConnectionRates', array('name' => "plugin_connections_connectionrates_id",'value' => $this->fields["plugin_connections_connectionrates_id"], 'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_connections'][12].": </td><td>";
      Dropdown::show('PluginConnectionsConnectionType', array('name' => "plugin_connections_connectiontypes_id",'value' => $this->fields["plugin_connections_connectiontypes_id"], 'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".$LANG['plugin_connections']['setup'][4].": </td>";
      echo "<td>";
      Dropdown::show('PluginConnectionsConnectionRatesGuaranteed', array('name' => "plugin_connections_connectionratesguaranteed_id",'value' => $this->fields["plugin_connections_connectionratesguaranteed_id"], 'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_connections'][18].": </td><td>";
      User::dropdown(array('value' => $this->fields["users_id"],'entity' => $this->fields["entities_id"],'right' => 'all'));
      echo "</td>";
      
      echo "<td>" . $LANG['software'][46] . ":</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['common'][35].": </td><td>";
      Dropdown::show('Group', array('name' => "groups_id",'value' => $this->fields["groups_id"], 'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".$LANG['common'][26].": </td>";
      $date = convDateTime($this->fields["date_mod"]);
      echo "<td>".$date;
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>";
      echo "</td>";
      echo "<td>";
      echo "</td>";
      
      echo "<td>";
      echo $LANG['plugin_connections'][10].":	</td>";
      echo "<td class='center'><textarea cols='35' rows='4' name='comment' >".$this->fields["comment"]."</textarea></td>";
      
      echo "</tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

		return true;
	}
	
	function dropdownConnections($myname,$entity_restrict='',$used=array()) {
      global $DB,$LANG,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `".$this->getTable()."`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND",$this->getTable(),'',$entity_restrict,true);

      if (count($used)) {
         $where .= " AND `id` NOT IN (0";
         foreach ($used as $ID)
            $where .= ",$ID";
         $where .= ")";
      }

      $query="SELECT *
        FROM `glpi_plugin_connections_connectiontypes`
        WHERE `id` IN (
          SELECT DISTINCT `plugin_connections_connectiontypes_id`
          FROM `".$this->getTable()."`
          $where)
        GROUP BY `name`
        ORDER BY `name`";
      $result=$DB->query($query);

      echo "<select name='_type' id='plugin_connections_connectiontypes_id'>\n";
      echo "<option value='0'>".DROPDOWN_EMPTY_VALUE."</option>\n";
      while ($data=$DB->fetch_assoc($result)) {
         echo "<option value='".$data['id']."'>".$data['name']."</option>\n";
      }
      echo "</select>\n";

      $params=array('plugin_connections_connectiontypes_id'=>'__VALUE__',
        'entity_restrict'=>$entity_restrict,
        'rand'=>$rand,
        'myname'=>$myname,
        'used'=>$used
        );

      ajaxUpdateItemOnSelectEvent("plugin_connections_connectiontypes_id","show_$myname$rand",$CFG_GLPI["root_doc"]."/plugins/connections/ajax/dropdownTypeConnections.php",$params);

      echo "<span id='show_$myname$rand'>";
      $_POST["entity_restrict"]=$entity_restrict;
      $_POST["plugin_connections_connectiontypes_id"]=0;
      $_POST["myname"]=$myname;
      $_POST["rand"]=$rand;
      $_POST["used"]=$used;
      include (GLPI_ROOT."/plugins/connections/ajax/dropdownTypeConnections.php");
      echo "</span>\n";

      return $rand;
   }
  
  // Cron action
   static function cronInfo($name) {
      global $LANG;
       
      switch ($name) {
         case 'ConnectionsAlert':
            return array (
               'description' => $LANG['plugin_connections']['mailing'][3]);   // Optional
            break;
      }
      return array();
   }

   function queryExpiredConnections() {
      global $DB,$CFG_GLPI,$LANG;
      
      $PluginConnectionsConfig=new PluginConnectionsConfig();
      $PluginConnectionsConfig->getFromDB('1');
      $delay=$PluginConnectionsConfig->fields["delay_expired"];

      $query = "SELECT * 
         FROM `".$this->getTable()."`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > $delay AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";

      return $query;
   }
   
   function queryConnectionsWhichExpire() {
      global $DB,$CFG_GLPI,$LANG;
      
      $PluginConnectionsConfig=new PluginConnectionsConfig();
      $PluginConnectionsConfig->getFromDB('1');
      $delay=$PluginConnectionsConfig->fields["delay_whichexpire"];
      
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
      global $DB,$CFG_GLPI,$LANG;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $Domain = new self();
      $query_expired = $Domain->queryExpiredConnections();
      $query_whichexpire = $Domain->queryConnectionsWhichExpire();
      
      $querys = array(Alert::NOTICE=>$query_whichexpire, Alert::END=>$query_expired);
      
      $domain_infos = array();
      $domain_messages = array();

      foreach ($querys as $type => $query) {
         $domain_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        convdate($data["date_expiration"])."<br>\n";
            $domain_infos[$type][$entity][] = $data;

            if (!isset($connections_infos[$type][$entity])) {
               $domain_messages[$type][$entity] = $LANG['plugin_connections']['mailing'][0]."<br />";
            }
            $domain_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($domain_infos[$type] as $entity => $connections) {
            Plugin::loadLang('connections');

            if (NotificationEvent::raiseEvent(($type==Alert::NOTICE?"ConnectionsWhichExpire":"ExpiredConnections"),
                                              new PluginConnectionsConnection(),
                                              array('entities_id'=>$entity,
                                                    'connections'=>$connections))) {
               $message = $domain_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity).":  $message\n");
                  //$task->addVolume(1);
               } else {
                  addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                    $entity).":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",$entity).
                             ":  Send connections alert failed\n");
               } else {
                  addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send connections alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   static function configCron($target) {

      $PluginConnectionsConfig=new PluginConnectionsConfig();
      $PluginConnectionsConfig->showForm($target,1);
    
   }
   
}

?>