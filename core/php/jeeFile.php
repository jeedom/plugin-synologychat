<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (!jeedom::apiAccess(init('apikey'), 'synologychat')) {
	connection::failed();
	echo 'Clef API non valide, vous n\'etes pas autorisé à effectuer cette action (jeeApi)';
	die();
}

if (init('file') == '') {
	echo 'Aucun fichier demandé';
	die();
}
$path_parts = pathinfo(init('file'));
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $path_parts['basename']);
readfile(init('file'));
die();