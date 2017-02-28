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

	/*
		     * Fonction exécutée automatiquement toutes les minutes par Jeedom
		      public static function cron() {

		      }
	*/

	/*
		     * Fonction exécutée automatiquement toutes les heures par Jeedom
		      public static function cronHourly() {

		      }
	*/

	/*
		     * Fonction exécutée automatiquement tous les jours par Jeedom
		      public static function cronDayly() {

		      }
	*/

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
				$_options['message'] .= ' (' . implode(';', $_options['answer']) . ')';
			}
			$post = array('text' => trim($_options['title'] . ' ' . $_options['message']));
			$request_http->setPost('payload=' . json_encode($post));
			$result = $request_http->exec(5, 3);
			if (!is_json($result)) {
				throw new Exception(__('Erreur : ', __FILE__) . $result);
			}
			$decode_result = json_decode($result, true);
			if (!isset($decode_result['success']) || !$decode_result['success']) {
				throw new Exception(__('Erreur : ', __FILE__) . $result);
			}
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
