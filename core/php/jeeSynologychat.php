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
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (!jeedom::apiAccess(init('apikey'), 'synologychat')) {
	connection::failed();
	echo 'Clef API non valide, vous n\'etes pas autorisé à effectuer cette action (jeeApi)';
	die();
}

$eqLogic = synologychat::byLogicalId(init('token'), 'synologychat');
if (!is_object($eqLogic)) {
	echo json_encode(array('text' => __('Token inconnu : ', __FILE__) . init('token')));
	die();
}
$parameters = array('plugin' => 'synologychat');
$user = user::byLogin(init('username'));
if (is_object($user)) {
	$parameters['profile'] = init('username');
}
$cmd = $eqLogic->getCmd('action', 'send');
if ($cmd->getCache('storeVariable', 'none') != 'none') {
	$cmd->askResponse(init('text'));
	echo json_encode(array('text' => ''));
	die();
}

$cmd_text = $eqLogic->getCmd('info', 'lastmessage');
$cmd_text->event(trim(init('text')));
$cmd_sender = $eqLogic->getCmd('info', 'sender');
$cmd_sender->event(init('username'));
$reply = interactQuery::tryToReply(trim(init('text')), $parameters);
if (isset($reply['file']) && count($reply['file']) > 0) {
	if (!is_array($reply['file'])) {
		$reply['file'] = array($reply['file']);
	}
	$send = $eqLogic->getCmd(null, 'send');
	$send->execCmd(array('files' => $reply['file'], 'message' => $reply['reply'], 'title' => ''));
	die();
}
echo json_encode(array('text' => $reply['reply']));
?>