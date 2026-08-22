<?php

/**
 * -------------------------------------------------------------------------
 * connections plugin for GLPI
 * Copyright (C) 2015-2026 by the connections Development Team.
 *
 * https://github.com/pluginsGLPI/connections
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of connections.
 *
 * connections is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * connections is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with connections. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Connections\Connection;

$Connection = new Connection();

// Gate the listing on the plugin's own READ right only. The former `config UPDATE`
// fallback let a configuration manager without any plugin_connections right list every
// connection, bypassing the plugin permission model; drop it for consistency with the
// menu (setup.php) and the form controller's display branch.
if (!$Connection->canView()) {
    throw new AccessDeniedHttpException();
}

Html::header(Connection::getTypeName(2), '', "assets", Connection::class);

Search::show(Connection::class);

Html::footer();
