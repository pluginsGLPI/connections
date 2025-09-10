<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
-------------------------------------------------------------------------
 connections plugin for GLPI
 Copyright (C) 2015-2022 by the connections Development Team.

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

namespace GlpiPlugin\Connections;

use Ajax;
use CommonDBTM;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Location;
use MassiveAction;
use Session;

/**
 * Class Connection
 */
class Connection extends CommonDBTM
{
    public static $rightname = 'plugin_connections_connection';
    public $dohistory = true;

    /**
     * @param int $nb
     *
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('Connections', 'connections');
    }

    public static function getIcon()
    {
        return "ti ti-wifi";
    }

    public function cleanDBonPurge()
    {

        $temp = new Connection_Item();
        $temp->deleteByCriteria(['plugin_connections_connections_id' => $this->fields['id']]);
    }


    /**
     * Get the form page URL for the current class and point to a specific ID
     * Backport for 0.85 compatibility
     *
     * @param int  $id (default 0)
     * @param bool $full path or relative one (true by default)
     *
     * @return string
     * @since version 0.90
     *
     */
    public static function getFormURLWithID($id = 0, $full = true)
    {

        $itemtype = get_called_class();
        $link     = $itemtype::getFormURL($full);
        $link     .= (strpos($link, '?') ? '&' : '?') . 'id=' . $id;
        return $link;
    }

    /**
     * @return array
     */
    public function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id'   => 'common',
            'name' => self::getTypeName(2),
        ];

        $tab[] = [
            'id'            => '1',
            'table'         => $this->getTable(),
            'field'         => 'name',
            'name'          => __('Name'),
            'datatype'      => 'itemlink',
            'itemlink_type' => $this->getType(),
        ];

        $tab[] = [
            'id'       => '2',
            'table'    => 'glpi_plugin_connections_connectiontypes',
            'field'    => 'name',
            'name'     => ConnectionType::getTypeName(2),
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id'        => '8',
            'table'     => 'glpi_users',
            'field'     => 'name',
            'linkfield' => 'users_id_tech',
            'name'      => __('Technician in charge of the hardware'),
            'datatype'  => 'dropdown',
            'right'     => 'interface',
        ];

        $tab[] = [
            'id'       => '4',
            'table'    => 'glpi_suppliers',
            'field'    => 'name',
            'name'     => __('Supplier'),
            'datatype' => 'dropdown',
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
            'id'       => '5',
            'table'    => 'glpi_plugin_connections_connectionrates',
            'field'    => 'name',
            'name'     => ConnectionRate::getTypeName(1),
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id'       => '6',
            'table'    => 'glpi_plugin_connections_guaranteedconnectionrates',
            'field'    => 'name',
            'name'     => GuaranteedConnectionRate::getTypeName(1),
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id'       => '7',
            'table'    => $this->getTable(),
            'field'    => 'comment',
            'name'     => __('Comments'),
            'datatype' => 'text',
        ];

        //      $tab[8]['table']         = 'glpi_plugin_connections_connections_items';
        //      $tab[8]['field']         = 'items_id';
        //      $tab[8]['linkfield']     = '';
        //      $tab[8]['name']          = __('Associated element');
        //      $tab[8]['injectable']    = false;
        //      $tab[8]['massiveaction'] = false;

        $tab[] = [
            'id'       => '9',
            'table'    => $this->getTable(),
            'field'    => 'others',
            'name'     => __('Other'),
            'datatype' => 'text',
        ];

        $tab[] = [
            'id'        => '10',
            'table'     => 'glpi_groups',
            'field'     => 'name',
            'linkfield' => 'groups_id_tech',
            'name'      => __('Group in charge of the hardware'),
            'datatype'  => 'dropdown',
        ];


        $tab[] = [
            'id'       => '13',
            'table'    => $this->getTable(),
            'field'    => 'is_helpdesk_visible',
            'name'     => __('Associable to a ticket'),
            'datatype' => 'bool',
        ];

        $tab[] = [
            'id'            => '14',
            'table'         => $this->getTable(),
            'field'         => 'date_mod',
            'massiveaction' => false,
            'name'          => __('Last update'),
            'datatype'      => 'datetime',
        ];


        $tab[] = [
            'id'       => '80',
            'table'    => 'glpi_entities',
            'field'    => 'completename',
            'name'     => __('Entity'),
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id'    => '81',
            'table' => 'glpi_entities',
            'field' => 'entities_id',
            'name'  => __('Entity') . "-" . __('ID'),
        ];

        $tab[] = [
            'id'       => '86',
            'table'    => $this->getTable(),
            'field'    => 'is_recursive',
            'name'     => __('Child entities'),
            'datatype' => 'bool',
        ];

        return $tab;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab(Connection_Item::class, $ong, $options);
        $this->addStandardTab('Item_Ticket', $ong, $options);
        $this->addStandardTab('Item_Problem', $ong, $options);
        $this->addStandardTab('Change_Item', $ong, $options);
        $this->addStandardTab('Infocom', $ong, $options);
        $this->addStandardTab('Contract_Item', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Note', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    /**
     * @param null $checkitem
     *
     * @return array
     */
    public function getSpecificMassiveActions($checkitem = null)
    {

        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
            if ($isadmin) {
                $actions['GlpiPlugin\Connections\Connection' . MassiveAction::CLASS_ACTION_SEPARATOR . 'install']   = _x('button', 'Associate');
                $actions['GlpiPlugin\Connections\Connection' . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall'] = _x('button', 'Dissociate');

                if (Session::haveRight('transfer', READ)
                    && Session::isMultiEntitiesMode()
                ) {
                    $actions[Connection::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
                }
            }
        }
        return $actions;
    }

    /**
     * @param MassiveAction $ma
     *
     * @return bool
     */
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case "uninstall":
            case "install":
                Dropdown::showSelectItemFromItemtypes(['items_id_name' => 'item_item',
                    'itemtype_name' => 'typeitem',
                    'itemtypes'     => Connection_Item::getClasses(true),
                    'checkright'
                    => true,
                ]);
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-primary']);
                return true;
                break;
            case "transfer":
                Dropdown::show('Entity');
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-primary']);
                return true;
                break;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    /**
     * @param MassiveAction $ma
     * @param CommonDBTM    $item
     * @param array          $ids
     */
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        $connection_item = new Connection_Item();

        switch ($ma->getAction()) {
            case 'transfer':
                $input = $ma->getInput();
                if ($item->getType() == 'GlpiPlugin\Connections\Connection') {
                    foreach ($ids as $key) {
                        $item->getFromDB($key);
                        $type = ConnectionType::transfer($item->fields["plugin_connections_connections_id"], $input['entities_id']);
                        if ($type > 0) {
                            $values["id"]                                = $key;
                            $values["plugin_connections_connections_id"] = $type;
                            $item->update($values);
                        }
                        unset($values);

                        $rate = ConnectionRate::transfer($item->fields["plugin_connections_connections_id"], $input['entities_id']);
                        if ($rate > 0) {
                            $values["id"]                                = $key;
                            $values["plugin_connections_connections_id"] = $rate;
                            $item->update($values);
                        }
                        unset($values);

                        $grate = GuaranteedConnectionRate::transfer($item->fields["plugin_connections_connections_id"], $input['entities_id']);
                        if ($grate > 0) {
                            $values["id"]                                = $key;
                            $values["plugin_connections_connections_id"] = $grate;
                            $item->update($values);
                        }

                        unset($values);
                        $values["id"]          = $key;
                        $values["entities_id"] = $input['entities_id'];

                        if ($item->update($values)) {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                        }
                    }
                }
                return;
            case 'install':
                $input = $ma->getInput();
                foreach ($ids as $key) {
                    if ($item->can($key, UPDATE)) {
                        $values = ['plugin_connections_connections_id' => $key,
                            'items_id'                          => $input["item_item"],
                            'itemtype'                          => $input['typeitem']];
                        if ($connection_item->add($values)) {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                        }
                    } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                        $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                    }
                }
                return;

            case 'uninstall':
                $input = $ma->getInput();
                foreach ($ids as $key) {
                    if ($connection_item->deleteItemByConnectionsAndItem($key, $input['item_item'], $input['typeitem'])) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                    }
                }
                return;
        }

        return;
    }

    /*
     * Return the SQL command to retrieve linked object
     *
     * @return a SQL command which return a set of (itemtype, items_id)
     */
    /**
     * @return string
     */
    public function getSelectLinkedItem()
    {
        return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_connections_connections_items`
              WHERE `plugin_connections_connections_id` = '" . $this->fields['id'] . "'";
    }

    /**
     * @param       $ID
     * @param array $options
     *
     * @return bool
     */
    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        TemplateRenderer::getInstance()->display('@connections/connection_form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);
        return true;
    }

    /**
     * @param        $myname
     * @param string $entity_restrict
     * @param array  $used
     *
     * @return int
     * @throws \GlpitestSQLError
     */
    public function dropdownConnections($myname, $entity_restrict = '', $used = [])
    {
        global $DB;

        $dbu              = new DbUtils();
        $rand             = mt_rand();
        $table            = $this->getTable();
        $entitiesRestrict = $dbu->getEntitiesRestrictRequest(
            "AND",
            $this->getTable(),
            '',
            $entity_restrict,
            true
        );
        $whereUsed        = (count($used))
            ? " AND `id` NOT IN (0," . implode(',', $used) . ")"
            : '';

        $query  = "SELECT *
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
        $result = $DB->doQuery($query);

        echo "<select class='form-select' name='_type' id='plugin_connections_connectiontypes_id'>\n";
        echo "<option value='0'>" . Dropdown::EMPTY_VALUE . "</option>\n";
        while ($data = $DB->fetchAssoc($result)) {
            echo "<option value='" . $data['id'] . "'>" . $data['name'] . "</option>\n";
        }
        echo "</select>\n";

        $params = [
            'plugin_connections_connectiontypes_id' => '__VALUE__',
            'entity_restrict'                       => $entity_restrict,
            'rand'                                  => $rand,
            'myname'                                => $myname,
            'used'                                  => $used,
        ];

        Ajax::updateItemOnSelectEvent(
            "plugin_connections_connectiontypes_id",
            "show_$myname$rand",
            PLUGINCONNECTIONS_WEBDIR . "/ajax/dropdownTypeConnections.php",
            $params
        );

        echo "<span id='show_$myname$rand'>";
        $_POST["entity_restrict"]                       = $entity_restrict;
        $_POST["plugin_connections_connectiontypes_id"] = 0;
        $_POST["myname"]                                = $myname;
        $_POST["rand"]                                  = $rand;
        $_POST["used"]                                  = $used;
        include(PLUGINCONNECTIONS_DIR . "/ajax/dropdownTypeConnections.php");
        echo "</span>\n";

        return $rand;
    }

    /**
     * @return array
     */
    public static function getMenuContent()
    {

        $menu          = [];
        $menu['title'] = self::getMenuName();
        $menu['page']  = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);
        if (Session::haveRight(static::$rightname, CREATE)) {
            $menu['links']['add'] = self::getFormURL(false);
        }
        $menu['links']['lists']  = "";

        $menu['icon'] = self::getIcon();

        return $menu;
    }

    public static function removeRightsFromSession()
    {
        if (isset($_SESSION['glpimenu']['assets']['types'][Connection::class])) {
            unset($_SESSION['glpimenu']['assets']['types'][Connection::class]);
        }
        if (isset($_SESSION['glpimenu']['assets']['content'][Connection::class])) {
            unset($_SESSION['glpimenu']['assets']['content'][Connection::class]);
        }
    }
}
