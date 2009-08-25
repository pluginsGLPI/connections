<?php
/*
   ---------------------------------------------------------------------- 
 GLPI - Gestionnaire Libre de Parc Informatique 
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org
   ----------------------------------------------------------------------

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
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function plugin_connections_getTypes () {
	static $types = NULL;
	
	if (is_null($types)) {
		$types = array(		
			NETWORKING_TYPE
		);
		/*$plugin = new Plugin();
		if ($plugin->isActivated("centreon")) {
			$types[]=PLUGIN_CENTREON_TYPE;
		}	*/	
	} 

	return $types;	
}

function plugin_connections_dropdownconnections($myname,$entity_restrict='',$used=array()) {


	global $DB,$LANG,$CFG_GLPI;

	$rand=mt_rand();

	$where=" WHERE glpi_plugin_connections.deleted='0' ";
	$where.=getEntitiesRestrictRequest("AND","glpi_plugin_connections",'',$entity_restrict,true);
	
	if (count($used)) {
		$where .= " AND ID NOT IN (0";
		foreach ($used as $ID)
			$where .= ",$ID";
		$where .= ")";
	}

	$query="SELECT * FROM glpi_dropdown_plugin_connections_type WHERE ID IN (SELECT DISTINCT type FROM glpi_plugin_connections $where) GROUP BY name ORDER BY name";
	$result=$DB->query($query);

	echo "<select name='_type' id='type_connections'>\n";
	echo "<option value='0'>------</option>\n";
	while ($data=$DB->fetch_assoc($result)){
		echo "<option value='".$data['ID']."'>".$data['name']."</option>\n";
	}
	echo "</select>\n";

	$params=array('type_connections'=>'__VALUE__',
			'entity_restrict'=>$entity_restrict,
			'rand'=>$rand,
			'myname'=>$myname,
			'used'=>$used
			);

	ajaxUpdateItemOnSelectEvent("type_connections","show_$myname$rand",$CFG_GLPI["root_doc"]."/plugins/connections/ajax/dropdownTypeConnections.php",$params);

	echo "<span id='show_$myname$rand'>";
	$_POST["entity_restrict"]=$entity_restrict;
	$_POST["type_connections"]=0;
	$_POST["myname"]=$myname;
	$_POST["rand"]=$rand;
	$_POST["used"]=$used;
	include (GLPI_ROOT."/plugins/connections/ajax/dropdownTypeConnections.php");
	echo "</span>\n";

	return $rand;
}

?>