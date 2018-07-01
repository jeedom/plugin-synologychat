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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class synologychat extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Méthodes d'instance************************* */

	public function preSave() {
		$this->setLogicalId($this->getConfiguration('token'));
	}

	public function postSave() {
		$cmd = $this->getCmd(null, 'lastmessage');
		if (!is_object($cmd)) {
			$cmd = new synologychatCmd();
			$cmd->setLogicalId('lastmessage');
			$cmd->setIsVisible(0);
			$cmd->setName(__('Message', __FILE__));
		}
		$cmd->setType('info');
		$cmd->setSubType('string');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'sender');
		if (!is_object($cmd)) {
			$cmd = new synologychatCmd();
			$cmd->setLogicalId('sender');
			$cmd->setIsVisible(0);
			$cmd->setName(__('Expediteur', __FILE__));
		}
		$cmd->setType('info');
		$cmd->setSubType('string');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'send');
		if (!is_object($cmd)) {
			$cmd = new synologychatCmd();
			$cmd->setLogicalId('send');
			$cmd->setIsVisible(0);
			$cmd->setName(__('Envoyer message', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
	}

	/*     * **********************Getteur Setteur*************************** */
}

class synologychatCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = array()) {
		if ($this->getType() == 'info') {
			return;
		}
		$eqLogic = $this->getEqLogic();
		if ($this->getLogicalId() == 'send') {
			$request_http = new com_http(trim($eqLogic->getConfiguration('webhook')));
			if (isset($_options['answer'])) {
				foreach ($_options['answer'] as $answer) {
					$_options['message'] .= '<' . $this->generateAskResponseLink($answer) . '&count=4|' . $answer . '> ';
				}
			}
			$post = array('text' => trim($_options['title'] . ' ' . $_options['message']));
			$payload = urlencode(json_encode($post));
			$request_http->setPost('payload=' . $payload);
			$retry = true;
			$count = 0;
			while ($retry) {
				$result = $request_http->exec(5, 3);
				$retry = false;
				if (!is_json($result)) {
					throw new Exception(__('Erreur : ', __FILE__) . $result);
				}
				$decode_result = json_decode($result, true);
				if (isset($decode_result['error']) && isset($decode_result['error']['code']) && $decode_result['error']['code'] == 411) {
					sleep(1);
					$retry = true;
				} else if (!isset($decode_result['success']) || !$decode_result['success']) {
					throw new Exception(__('Erreur : ', __FILE__) . $result);
				}
				$count++;
				if ($count > 10) {
					throw new Exception(__('Erreur trop d\'essai sans succès : ', __FILE__) . $result);
				}
			}
			if (isset($_options['files']) && count($_options['files']) > 0) {
				foreach ($_options['files'] as $file) {
					$post = array('file_url' => network::getNetworkAccess($eqLogic->getConfiguration('networkmode')) . '/plugins/synologychat/core/php/jeeFile.php?apikey=' . jeedom::getApiKey('synologychat') . '&file=' . urlencode($file));
					$payload = urlencode(json_encode($post));
					$request_http->setPost('payload=' . $payload);
					$retry = true;
					$count = 0;
					while ($retry) {
						$result = $request_http->exec(15, 3);
						$retry = false;
						if (!is_json($result)) {
							throw new Exception(__('Erreur : ', __FILE__) . $result);
						}
						$decode_result = json_decode($result, true);
						if (isset($decode_result['error']) && isset($decode_result['error']['code']) && $decode_result['error']['code'] == 411) {
							sleep(1);
							$retry = true;
						} else if (!isset($decode_result['success']) || !$decode_result['success']) {
							throw new Exception(__('Erreur : ', __FILE__) . $result);
						}
						$count++;
						if ($count > 10) {
							throw new Exception(__('Erreur trop d\'essai sans succès : ', __FILE__) . $result);
						}
					}
				}
			}
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
