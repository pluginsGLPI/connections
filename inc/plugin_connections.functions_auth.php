<?php
/*
 * @version $Id: auth.function.php 3576 2006-06-12 08:40:44Z moyo $
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


function plugin_connections_createfirstaccess($ID){

	$plugin_connections_Profile=new plugin_connections_Profile();
	if (!$plugin_connections_Profile->GetfromDB($ID)){
		
		$Profile=new Profile();
		$Profile->GetfromDB($ID);
		$name=$Profile->fields["name"];

		$plugin_connections_Profile->add(array(
			'ID' => $ID,
			'name' => $name,
			'connections' => 'w'));
	}
	
}

function plugin_connections_createaccess($ID){

	$plugin_connections_Profile=new plugin_connections_Profile();
	$Profile=new Profile();
	$Profile->GetfromDB($ID);
	$name=$Profile->fields["name"];
	
	$plugin_connections_Profile->add(array(
		'ID' => $ID,
		'name' => $name));
}

function plugin_connections_changeprofile()
{
	$prof=new plugin_connections_Profile();
	if($prof->getFromDB($_SESSION['glpiactiveprofile']['ID']))
		$_SESSION["glpi_plugin_connections_profile"]=$prof->fields;
	else
		unset($_SESSION["glpi_plugin_connections_profile"]);
}

function plugin_connections_haveRight($module,$right){
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_connections_profile"][$module])&&in_array($_SESSION["glpi_plugin_connections_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

function plugin_connections_checkRight($module, $right) {
	global $CFG_GLPI;

	if (!plugin_connections_haveRight($module, $right)) {
		// Gestion timeout session
		if (!isset ($_SESSION["glpiID"])) {
			glpi_header($CFG_GLPI["root_doc"] . "/index.php");
			exit ();
		}

		displayRightError();
	}
}

?>