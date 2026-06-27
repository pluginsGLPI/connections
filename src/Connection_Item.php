<?php

/*
 -------------------------------------------------------------------------
 connections plugin for GLPI
 Copyright (C) 2015-2026 by the connections Development Team.

 https://github.com/pluginsGLPI/connections
 -------------------------------------------------------------------------

 LICENSE

 This file is part of connections.

 connections is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
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

use CommonDBRelation;
use CommonDBTM;
use CommonGLPI;
use DbUtils;
use Dropdown;
use Entity;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Log;
use Session;
use Supplier;
use Toolbox;

/**
 * Class Connection_Item
 */
final class Connection_Item extends CommonDBRelation
{

    public static $rightname = 'plugin_connections_connection';

    public static $itemtype_1 = Connection::class;
    public static $items_id_1 = 'plugin_connections_connections_id';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;


    /**
     * Get the standard massive actions which are forbidden
     *
     * @return array of massive actions
     **@since version 0.84
     *
     */
    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        $forbidden[] = 'add_note';
        return $forbidden;
    }

    public static function getIcon()
    {
        return Connection::getIcon();
    }

    /**
     * @param CommonDBTM $item
     */
    public static function cleanForItem(CommonDBTM $item)
    {
        $temp = new self();
        $temp->deleteByCriteria(
            [
                'itemtype' => $item->getType(),
                'items_id' => $item->getField('id')
            ]
        );
    }

    /**
     * @param bool $all
     *
     * @return array
     */
    public static function getClasses($all = false)
    {
        static $types = [
            'NetworkEquipment',
            'Appliance',
            'Computer',
            'Certificate',
        ];

        if ($all) {
            return $types;
        }

        foreach ($types as $key => $type) {
            if (!class_exists($type)) {
                continue;
            }
            $item = new $type();
            if (!$item->canView()) {
                unset($types[$key]);
            }
        }
        return $types;
    }


    /**
     * @param $connections_id
     * @param $items_id
     * @param $itemtype
     */
    public function addItem($connections_id, $items_id, $itemtype)
    {
        $input = [
            'plugin_connections_connections_id' => $connections_id,
            'items_id' => $items_id,
            'itemtype' => $itemtype,
        ];

        if ($this->add($input)) {
            // History Log into Connection
            $item = new $itemtype();
            $item->getFromDB($items_id);

            $changes[0] = 0;
            $changes[1] = '';
            $changes[2] = $item->getNameID(['forceid' => true]);
            Log::history($connections_id, Connection::class, $changes, $item->getType(), 15);

            // History Log into Item
            $item = new Connection();
            $item->getFromDB($connections_id);

            $changes[0] = 0;
            $changes[1] = '';
            $changes[2] = $item->getNameID(['forceid' => true]);
            Log::history($items_id, $item->getType(), $changes, Connection::class, 15);
        }
    }

    /**
     * @param $input
     */
    public function deleteItem($input)
    {
        $this->check($input['id'], UPDATE);

        $connections_id = $this->getField('plugin_connections_connections_id');
        $itemtype = $this->getField('itemtype');
        $items_id = $this->getField('items_id');
        if ($this->delete($input)) {
            // History Log into Connection
            $item = new $itemtype();
            $item->getFromDB($items_id);

            $changes[0] = 0;
            $changes[1] = $item->getNameID(['forceid' => true]);
            $changes[2] = '';
            Log::history($connections_id, Connection::class, $changes, $item->getType(), 16);

            // History Log into item
            $item = new Connection();
            $item->getFromDB($connections_id);

            $changes[0] = 0;
            $changes[1] = $item->getNameID(['forceid' => true]);
            $changes[2] = '';
            Log::history($items_id, $item->getType(), $changes, Connection::class, 16);
        }
    }

    /**
     * @param $connections_id
     * @param $items_id
     * @param $itemtype
     */
    public function deleteItemByConnectionsAndItem($connections_id, $items_id, $itemtype)
    {
        if ($this->getFromDBByCrit([
            'plugin_connections_connections_id' => $connections_id,
            'items_id' => $items_id,
            'itemtype' => $itemtype
        ])) {
            $this->delete([
                'id' => $this->fields["id"],
            ]);
        }
    }


    /**
     * @param Connection $item
     *
     * @return int
     */
    public static function countForConnection(Connection $item)
    {
        $types = self::getClasses();
        if (count($types) == 0) {
            return 0;
        }
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(
            'glpi_plugin_connections_connections_items',
            [
                "plugin_connections_connections_id" => $item->getID(),
                "itemtype" => $types,
            ]
        );
    }

    /**
     * @param CommonGLPI $item
     * @param int $withtemplate
     *
     * @return array|string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == Connection::class && count(self::getClasses(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                return self::createTabEntry(
                    _n('Associated item', 'Associated items', 2),
                    self::countForConnection($item)
                );
            }
            return _n('Associated item', 'Associated items', 2);
        } elseif (in_array($item->getType(), self::getClasses(true))
            && Session::haveRight('plugin_connections_connection', READ)) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                return self::createTabEntry(Connection::getTypeName(2), self::countForItem($item));
            }

            return self::getTypeName(2);
        } elseif ($item->getType() == 'Supplier'
            && Session::haveRight('plugin_connections_connection', READ)) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                return self::createTabEntry(Connection::getTypeName(2), self::countSupplierForItem($item));
            }

            return self::createTabEntry(self::getTypeName(2));
        }

        return '';
    }

    /**
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if (!$item instanceof CommonDBTM) {
            return false;
        }

        if ($item instanceof Connection) {
            return self::showForConnection($item, $withtemplate);
        }
        if (Connection::canView()
            && in_array($item->getType(), self::getClasses(true))) {
            return self::showForAsset($item);
        }

        if (Connection::canView()
            && $item->getType() == 'Supplier') {
            return self::showForSupplier($item);
        }

        return false;
    }


    /**
     * @param CommonDBTM $item
     *
     * @return int
     */
    public static function countForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(
            'glpi_plugin_connections_connections_items',
            [
                "itemtype" => $item->getType(),
                "items_id" => $item->getID()
            ]
        );
    }

    /**
     * @param CommonDBTM $item
     *
     * @return int
     */
    public static function countSupplierForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(
            'glpi_plugin_connections_connections',
            ["suppliers_id" => $item->getID()]
        );
    }


    /**
     * Print the HTML array for Items linked to a connection
     *
     * @param Connection $connection
     * @param int $withtemplate
     *
     * @return bool
     **/
    public static function showForConnection(Connection $connection, int $withtemplate = 0): bool
    {
        $instID = $connection->getID();

        if (!$connection->can($instID, READ)) {
            return false;
        }
        $canedit = $connection->canEdit($instID);
        $rand = mt_rand();

        $types_iterator = self::getDistinctTypes($instID);

        $totalnb = 0;
        $entity_names_cache = [];
        $entries = [];
        $used = [];

        foreach ($types_iterator as $row) {
            $itemtype = $row['itemtype'];
            if (!($item = getItemForItemtype($itemtype)) || !$item::canView()) {
                continue;
            }

            $itemtype_name = $item::getTypeName(1);
            $iterator = self::getTypeItems($instID, $itemtype);
            $nb = count($iterator);

            foreach ($iterator as $data) {
                $name = $data[$itemtype::getNameField()];
                if (
                    $_SESSION["glpiis_ids_visible"]
                    || empty($data[$itemtype::getNameField()])
                ) {
                    $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
                }
                $link = $item::getFormURLWithID($data['id']);
                $namelink = "<a href=\"" . htmlescape($link) . "\">" . htmlescape($name) . "</a>";

                if (!isset($entity_names_cache[$data['entity']])) {
                    $entity_names_cache[$data['entity']] = Dropdown::getDropdownName("glpi_entities", $data['entity']);
                }

                $entries[] = [
                    'itemtype' => self::class,
                    'id' => $data['linkid'],
                    'row_class' => (isset($data['is_deleted']) && $data['is_deleted']) ? 'table-deleted' : '',
                    'type' => $itemtype_name,
                    'name' => $namelink,
                    'entity' => $entity_names_cache[$data['entity']],
                    'serial' => $data["serial"] ?? '-',
                    'otherserial' => $data["otherserial"] ?? '-',
                ];
                $used[$itemtype][$data['id']] = $data['id'];
            }
            $totalnb += $nb;
        }

        $columns = [
            'type' => _n('Type', 'Types', 1),
        ];
        if (Session::isMultiEntitiesMode()) {
            $columns['entity'] = Entity::getTypeName(1);
        }
        $columns += [
            'name' => __('Name'),
            'serial' => __('Serial number'),
            'otherserial' => __('Inventory number'),
        ];
        $formatters = [
            'name' => 'raw_html',
        ];
        $footers = [];
        if ($totalnb > 0) {
            $footers = [
                [sprintf(__('%1$s = %2$s'), __('Total'), $totalnb)],
            ];
        }

        TemplateRenderer::getInstance()->display('@connections/item_connection.html.twig', [
            'item' => $connection,
            'can_edit' => $canedit && $withtemplate != 2,
            'withtemplate' => $withtemplate,
            'used' => $used,
            'types' => self::getClasses(true),
            'datatable_params' => [
                'is_tab' => true,
                'nofilter' => true,
                'nosort' => true,
                'columns' => $columns,
                'formatters' => $formatters,
                'entries' => $entries,
                'footers' => $footers,
                'total_number' => count($entries),
                'filtered_number' => count($entries),
                'showmassiveactions' => $canedit,
                'massiveactionparams' => [
                    'container' => 'massiveactioncontainer' . $rand,
                    'itemtype' => self::class,
                ],
            ],
        ]);

        return true;
    }

    //from items
    private static function showForAsset(CommonDBTM $item): bool
    {
        global $DB;

        $used = $entries = [];


        $criteria = [
            'SELECT' => [
                'glpi_plugin_connections_connections_items.id AS assocID',
                'glpi_entities.id AS entity',
                'glpi_plugin_connections_connections.name AS assocName',
                'glpi_plugin_connections_connections.*'
            ],
            'FROM' => 'glpi_plugin_connections_connections_items',
            'LEFT JOIN' => [
                'glpi_plugin_connections_connections' => [
                    'ON' => [
                        'glpi_plugin_connections_connections_items' => 'plugin_connections_connections_id',
                        'glpi_plugin_connections_connections' => 'id',
                    ],
                ],
                'glpi_entities' => [
                    'ON' => [
                        'glpi_plugin_connections_connections' => 'entities_id',
                        'glpi_entities' => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                'glpi_plugin_connections_connections_items.items_id' => $item->getID(),
                'glpi_plugin_connections_connections_items.itemtype' => $item->getType(),
            ],
            'ORDERBY' => 'assocName',
        ];
        $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
                'glpi_plugin_connections_connections',
                '',
                '',
                true
            );

        $iterator_list = $DB->request($criteria);

        foreach ($iterator_list as $value) {
            $used[] = $value['id'];
            $connection = new Connection();

            $connectionID = $value['id'];
            $result = $connection->getFromDB($value['id']);

            if ($result === false || !$connection->can($connection->getID(), READ)) {
                continue;
            }

            $rand = mt_rand();
            $entries[] = [
                'itemtype' => self::class,
                'id' => $value['assocID'],
                'name' => $connection->getLink(),
                'entities_id' => Dropdown::getDropdownName("glpi_entities", $connection->fields['entities_id']),
                'plugin_connections_connectiontypes_id' => Dropdown::getDropdownName(
                    "glpi_plugin_connections_connectiontypes",
                    $connection->fields["plugin_connections_connectiontypes_id"]
                ),
                'plugin_connections_connectionrates_id' => Dropdown::getDropdownName(
                    "glpi_plugin_connections_connectionrates",
                    $connection->fields["plugin_connections_connectionrates_id"]
                ),
                'plugin_connections_guaranteedconnectionrates_id' => Dropdown::getDropdownName(
                    "glpi_plugin_connections_guaranteedconnectionrates",
                    $connection->fields["plugin_connections_guaranteedconnectionrates_id"]
                ),
            ];
        }

        $cols = [
            'columns' => [
                "name" => __('Name'),
                "entities_id" => __s('Entity'),
                "plugin_connections_connectiontypes_id" => __s('Type of Connections', 'connections'),
                "plugin_connections_connectionrates_id" => __s('Rates', 'connections'),
                "plugin_connections_guaranteedconnectionrates_id" => __s('Guaranteed Rates', 'connections'),
            ],
            'formatters' => [
                'name' => 'raw_html',
                'entities_id' => 'raw_html',
                'plugin_connections_connectiontypes_id' => 'raw_html',
                'plugin_connections_connectionrates_id' => 'raw_html',
                'plugin_connections_guaranteedconnectionrates_id' => 'raw_html',
            ],
        ];


        $footers = [];

        TemplateRenderer::getInstance()->display('@connections/item_connection.html.twig', [
            'item' => $item,
            'can_edit' => $item->canEdit($item->getID()),
            'used' => $used,
            'datatable_params' => [
                'is_tab' => true,
                'nofilter' => true,
                'nosort' => true,
                'columns' => $cols['columns'],
                'formatters' => $cols['formatters'],
                'entries' => $entries,
                'footers' => $footers,
                'total_number' => count($entries),
                'filtered_number' => count($entries),
                'showmassiveactions' => $item->canEdit($item->getID()),
                'massiveactionparams' => [
                    'container' => 'massiveactioncontainer' . $rand,
                    'itemtype' => self::class,
                ],
            ],
        ]);

        return true;
    }


    private static function showForSupplier(Supplier $item): bool
    {
        global $DB;

        $used = $entries = [];


        $criteria = [
            'SELECT' => ['glpi_plugin_connections_connections.*'],
            'FROM' => 'glpi_plugin_connections_connections',
            'LEFT JOIN' => [
                'glpi_entities' => [
                    'ON' => [
                        'glpi_plugin_connections_connections' => 'entities_id',
                        'glpi_entities' => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                'suppliers_id' => $item->getID(),
            ],
            'ORDERBY' => 'glpi_plugin_connections_connections.name',
        ];
        $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
                'glpi_plugin_connections_connections',
                '',
                '',
                true
            );

        $iterator_list = $DB->request($criteria);

        foreach ($iterator_list as $value) {
            $used[] = $value['id'];
            $connection = new Connection();

            $connectionID = $value['id'];
            $result = $connection->getFromDB($value['id']);

            if ($result === false || !$connection->can($connection->getID(), READ)) {
                continue;
            }

            $entries[] = [
                'itemtype' => self::class,
                'id' => $value['id'],
                'name' => $connection->getLink(),
                'entities_id' => Dropdown::getDropdownName("glpi_entities", $connection->fields['entities_id']),
                'plugin_connections_connectiontypes_id' => Dropdown::getDropdownName(
                    "glpi_plugin_connections_connectiontypes",
                    $connection->fields["plugin_connections_connectiontypes_id"]
                ),
                'plugin_connections_connectionrates_id' => Dropdown::getDropdownName(
                    "glpi_plugin_connections_connectionrates",
                    $connection->fields["plugin_connections_connectionrates_id"]
                ),
                'plugin_connections_guaranteedconnectionrates_id' => Dropdown::getDropdownName(
                    "glpi_plugin_connections_guaranteedconnectionrates",
                    $connection->fields["plugin_connections_guaranteedconnectionrates_id"]
                ),
            ];
        }

        $cols = [
            'columns' => [
                "name" => __('Name'),
                "entities_id" => __s('Entity'),
                "plugin_connections_connectiontypes_id" => __s('Type of Connections', 'connections'),
                "plugin_connections_connectionrates_id" => __s('Rates', 'connections'),
                "plugin_connections_guaranteedconnectionrates_id" => __s('Guaranteed Rates', 'connections'),
            ],
            'formatters' => [
                'name' => 'raw_html',
                'entities_id' => 'raw_html',
                'plugin_connections_connectiontypes_id' => 'raw_html',
                'plugin_connections_connectionrates_id' => 'raw_html',
                'plugin_connections_guaranteedconnectionrates_id' => 'raw_html',
            ],
        ];


        $footers = [];

        TemplateRenderer::getInstance()->display('@connections/item_connection.html.twig', [
            'item' => $item,
            'can_edit' => false,
            'used' => $used,
            'datatable_params' => [
                'is_tab' => true,
                'nofilter' => true,
                'nosort' => true,
                'columns' => $cols['columns'],
                'formatters' => $cols['formatters'],
                'entries' => $entries,
                'footers' => $footers,
                'total_number' => count($entries),
                'filtered_number' => count($entries),
            ],
        ]);

        return true;
    }
}
