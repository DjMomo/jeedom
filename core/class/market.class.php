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
require_once dirname(__FILE__) . '/../../core/php/core.inc.php';

class market {
    /*     * *************************Attributs****************************** */

    private $id;
    private $name;
    private $type;
    private $datetime;
    private $description;
    private $categorie;
    private $changelog;
    private $version;
    private $user_id;
    private $downloaded;
    private $status;
    private $author;
    private $logicalId;
    private $utilization;
    private $api_author;
    private $purchase = 0;
    private $cost = 0;
    private $realcost = 0;

    /*     * ***********************Methode static*************************** */

    private static function construct($_arrayMarket) {
        $market = new self();
        if (!isset($_arrayMarket['id'])) {
            return;
        }
        $market->setId($_arrayMarket['id']);
        $market->setName($_arrayMarket['name']);
        $market->setType($_arrayMarket['type']);
        $market->setDatetime($_arrayMarket['datetime']);
        $market->setDescription($_arrayMarket['description']);
        $market->setDownloaded($_arrayMarket['downloaded']);
        $market->setUser_id($_arrayMarket['user_id']);
        $market->setVersion($_arrayMarket['version']);
        $market->setCategorie($_arrayMarket['categorie']);
        $market->setStatus($_arrayMarket['status']);
        $market->setAuthor($_arrayMarket['author']);
        $market->setChangelog($_arrayMarket['changelog']);
        $market->setLogicalId($_arrayMarket['logicalId']);
        $market->setUtilization($_arrayMarket['utilization']);
        $market->setPurchase($_arrayMarket['purchase']);
        $market->setCost($_arrayMarket['cost']);
        $market->setRealcost($_arrayMarket['realCost']);
        if (!isset($_arrayMarket['api_author'])) {
            $_arrayMarket['api_author'] = null;
        }
        $market->setApi_author($_arrayMarket['api_author']);

        return $market;
    }

    public static function byId($_id) {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byId', array('id' => $_id))) {
            return self::construct($market->getResult());
        } else {
            throw new Exception($market->getError());
        }
    }

    public static function byLogicalId($_logicalId) {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byLogicalId', array('logicalId' => $_logicalId))) {
            return self::construct($market->getResult());
        } else {
            throw new Exception($market->getError());
        }
    }

    public static function byMe() {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byAuthor', array())) {
            $return = array();
            foreach ($market->getResult() as $result) {
                $return[] = self::construct($result);
            }
            return $return;
        } else {
            throw new Exception($market->getError());
        }
    }

    public static function byStatusAndType($_status, $_type) {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byStatusAndType', array('status' => $_status, 'type' => $_type))) {
            $return = array();
            foreach ($market->getResult() as $result) {
                $return[] = self::construct($result);
            }
            return $return;
        } else {
            throw new Exception($market->getError());
        }
    }

    public static function byStatus($_status) {
        $market = self::getJsonRpc();
        if ($market->sendRequest('market::byStatus', array('status' => $_status))) {
            $return = array();
            foreach ($market->getResult() as $result) {
                $return[] = self::construct($result);
            }
            return $return;
        } else {
            throw new Exception($market->getError());
        }
    }

    public static function getPurchaseInfo() {
        $market = self::getJsonRpc();
        if ($market->sendRequest('purchase::getInfo')) {
            return $market->getResult();
        }
    }

    public static function saveTicket($_ticket) {
        $jsonrpc = self::getJsonRpc();
        $_ticket['user_plugin'] = '';
        foreach (plugin::listPlugin() as $plugin) {
            $_ticket['user_plugin'] .= $plugin->getId() . ',';
        }
        $cibDir = realpath(dirname(__FILE__) . '/../../log');
        $tmp = dirname(__FILE__) . '/../../tmp/log.zip';
        if (file_exists($tmp)) {
            if (!unlink($tmp)) {
                throw new Exception(__('Impossible de supprimer : ', __FILE__) . $tmp . __('. Vérifiez les droits',__FILE__));
            }
        }
        if (!create_zip($cibDir, $tmp)) {
            throw new Exception(__('Echec de création du zip', __FILE__));
        }
        if (isset($_ticket['options']['page'])) {
            $_ticket['options']['page'] = substr($_ticket['options']['page'], strpos($_ticket['options']['page'], 'index.php'));
        }


        $file = array(
            'file' => '@' . realpath($tmp)
        );
        $_ticket['options']['jeedom_version'] = getVersion('jeedom');
        if (!$jsonrpc->sendRequest('ticket::save', array('ticket' => $_ticket), 600, $file)) {
            throw new Exception($jsonrpc->getErrorMessage());
        }
        return $jsonrpc->getResult();
    }

    public static function getJeedomCurrentVersion($_refresh = false) {
        try {
            $cache = cache::byKey('jeedom::lastVersion');
            if (!$_refresh && $cache->getValue('') != '') {
                return $cache->getValue();
            }
            $market = self::getJsonRpc();
            if ($market->sendRequest('jeedom::getCurrentVersion')) {
                $version = trim($market->getResult());
                cache::set('jeedom::lastVersion', $version, 86400);
                return $version;
            }
        } catch (Exception $e) {
            
        }
        return null;
    }

    public static function getJsonRpc() {
        if (config::byKey('market::address') == '') {
            throw new Exception(__('Aucune addresse pour le market de renseignée',__FILE__));
        }
        if (config::byKey('market::registerkey') == '') {
            $register = new jsonrpcClient(config::byKey('market::address') . '/core/api/api.php', config::byKey('market::apikey'), array(
                'jeedomversion' => getVersion('jeedom'),
                'hwkey' => jeedom::getHardwareKey()
            ));
            if (!$register->sendRequest('register', array())) {
                throw new Exception($register->getError());
            }
            config::save('market::registerkey', $register->getResult());
        }
        return new jsonrpcClient(config::byKey('market::address') . '/core/api/api.php', config::byKey('market::apikey'), array(
            'jeedomversion' => getVersion('jeedom'),
            'jeedomkey' => config::byKey('market::registerkey'),
            'hwkey' => jeedom::getHardwareKey()
        ));
    }

    public static function getInfo($_logicalId) {
        $cache = cache::byKey('market::info::' . $_logicalId);
        if ($cache->getValue('') != '') {
            return json_decode($cache->getValue(), true);
        }
        $return = array();
        if ($_logicalId == '' || config::byKey('market::address') == '') {
            $return['market'] = 0;
            $return['market_owner'] = 0;
            $return['status'] = 'ok';
            return $return;
        }

        if (config::byKey('market::apikey') != '') {
            $return['market_owner'] = 1;
        } else {
            $return['market_owner'] = 0;
        }
        $return['market'] = 0;

        try {
            $market = market::byLogicalId($_logicalId);

            if (!is_object($market)) {
                $return['status'] = 'depreciated';
            } else {
                $return['market'] = 1;
                if ($market->getApi_author() == config::byKey('market::apikey') && $market->getApi_author() != '') {
                    $return['market_owner'] = 1;
                } else {
                    $return['market_owner'] = 0;
                }
            }

            if ($market->getType() == 'plugin') {
                $updateDateTime = config::byKey('installVersionDate', $market->getLogicalId());
            } else {
                $updateDateTime = config::byKey($market->getLogicalId() . '::installVersionDate', $market->getType());
            }

            if ($market->getStatus() == 'Refusé') {
                $return['status'] = 'depreciated';
            }
            if ($market->getStatus() == 'A valider') {
                if ($updateDateTime < $market->getDatetime()) {
                    $return['status'] = 'update';
                } else {
                    $return['status'] = 'ok';
                }
            }
            if ($market->getStatus() == 'Validé') {
                if ($updateDateTime < $market->getDatetime()) {
                    $return['status'] = 'update';
                } else {
                    $return['status'] = 'ok';
                }
            }
        } catch (Exception $e) {
            log::add('market', 'debug', __('Erreur market::getinfo : ',__FILE__) . $e->getMessage());
            cache::set('market::info::' . $_logicalId, json_encode($return), 3600);
            $return['status'] = 'ok';
        }
        cache::set('market::info::' . $_logicalId, json_encode($return), 3600);
        return $return;
    }

    public static function sendBackup($_path) {
        $market = self::getJsonRpc();
        $file = array(
            'file' => '@' . realpath($_path)
        );
        if (!$market->sendRequest('backup::upload', array(), 3600, $file)) {
            throw new Exception($market->getError());
        }
    }

    public static function listeBackup() {
        $market = self::getJsonRpc();
        if (!$market->sendRequest('backup::liste', array())) {
            throw new Exception($market->getError());
        }
        return $market->getResult();
    }

    public static function retoreBackup($_backup) {
        $url = config::byKey('market::address') . "/core/php/downloadBackup.php?backup=" . $_backup . '&hwkey=' . jeedom::getHardwareKey() . '&apikey=' . config::byKey('market::apikey');
        $tmp_dir = dirname(__FILE__) . '/../../tmp';
        $tmp = $tmp_dir . '/' . $_backup;
        file_put_contents($tmp, fopen($url, 'r'));
        if (!file_exists($tmp)) {
            throw new Exception(__('Impossible de télécharger le backup : ',__FILE__) . $url . '.');
        }
        $backup_path = dirname(__FILE__) . '/../../backup/' . $_backup;
        copy($tmp, $backup_path);
        if (!file_exists($backup_path)) {
            throw new Exception(__('Impossible de trouver le fichier : ',__FILE__) . $backup_path . '.');
        }
        jeedom::restore('backup/' . $_backup, true);
    }

    /*     * *********************Methode d'instance************************* */

    public function getComment() {
        $market = self::getJsonRpc();
        if (!$market->sendRequest('market::getComment', array('id' => $this->getId()))) {
            throw new Exception($market->getError());
        }
        return $market->getResult();
    }

    public function setComment($_comment = null, $_order = null) {
        $market = self::getJsonRpc();
        if (!$market->sendRequest('market::setComment', array('id' => $this->getId(), 'comment' => $_comment, 'order' => $_order))) {
            throw new Exception($market->getError());
        }
    }

    public function setRating($_rating) {
        $market = self::getJsonRpc();
        if (!$market->sendRequest('market::setRating', array('rating' => $_rating, 'id' => $this->getId()))) {
            throw new Exception($market->getError());
        }
    }

    public function getRating() {
        $market = self::getJsonRpc();
        if (!$market->sendRequest('market::getRating', array('id' => $this->getId()))) {
            throw new Exception($market->getError());
        }
        return $market->getResult();
    }

    public function install() {
        $cache = cache::byKey('market::info::' . $this->getLogicalId());
        if (is_object($cache)) {
            $cache->remove();
        }
        $tmp_dir = dirname(__FILE__) . '/../../tmp';
        $tmp = $tmp_dir . '/' . $this->getLogicalId() . '.zip';
        if (!is_writable($tmp_dir)) {
            throw new Exception(__('Impossible d\'écrire dans le repertoire : ',__FILE__) . $tmp . __('. Exécuter la commande suivante en SSH : chmod 777 -R ',__FILE__) . $tmp_dir);
        }
        $url = config::byKey('market::address') . "/core/php/downloadFile.php?id=" . $this->getId() . '&hwkey=' . jeedom::getHardwareKey() . '&apikey=' . config::byKey('market::apikey');
        file_put_contents($tmp, fopen($url, 'r'));
        if (!file_exists($tmp)) {
            throw new Exception(__('Impossible de télécharger le fichier depuis : ' . $url . '. Si l\'application est payante, l\'avez vous achetée ?',__FILE__));
        }
        switch ($this->getType()) {
            case 'plugin' :
                $cibDir = dirname(__FILE__) . '/../../plugins/' . $this->getLogicalId();
                if (!file_exists($cibDir) && !mkdir($cibDir, 0775, true)) {
                    throw new Exception(__('Impossible de créer le dossier  : ' . $cibDir . '. Problème de droits ?',__FILE__));
                }
                $zip = new ZipArchive;
                if ($zip->open($tmp) === TRUE) {
                    if (!$zip->extractTo($cibDir . '/')) {
                        throw new Exception(__('Impossible d\'installer le plugin. Les fichiers n\'ont pu etre décompressés',__FILE__));
                    }
                    $zip->close();
                    try {
                        $plugin = new plugin($this->getLogicalId());
                    } catch (Exception $e) {
                        $this->remove();
                        throw new Exception(__('Impossible d\'installer le plugin. Le nom du plugin est différent de l\'ID ou le plugin n\'est pas correctement formé. Veuillez contacter l\'auteur.',__FILE__));
                    }
                    if (config::byKey('installVersionDate', $this->getLogicalId()) != '') {
                        if (is_object($plugin) && $plugin->isActive()) {
                            $plugin->setIsEnable(1);
                        }
                    }
                } else {
                    throw new Exception(__('Impossible de décompresser le zip : ',__FILE__) . $tmp);
                }

                config::save('installVersionDate', $this->getDatetime(), $this->getLogicalId());
                break;
            default :
                $type = $this->getType();
                if (class_exists($type) && method_exists($type, 'getFromMarket')) {
                    $type::getFromMarket($this, $tmp);
                }
                config::save($this->getLogicalId() . '::installVersionDate', $this->getDatetime(), $type);
                break;
        }
    }

    public function remove() {
        $cache = cache::byKey('market::info::' . $this->getLogicalId());
        if (is_object($cache)) {
            $cache->remove();
        }
        switch ($this->getType()) {
            case 'plugin' :
                $cibDir = dirname(__FILE__) . '/../../plugins/' . $this->getLogicalId();
                if (file_exists($cibDir)) {
                    rrmdir($cibDir);
                }
                config::remove('installVersionDate', $this->getLogicalId());
                break;
            default :
                $type = $this->getType();
                if (class_exists($type) && method_exists($type, 'getFromMarket')) {
                    $type::removeFromMarket($this);
                }
                config::save($this->getLogicalId() . '::installVersionDate', $this->getDatetime(), $type);
                break;
        }
    }

    public function save() {
        $cache = cache::byKey('market::info::' . $this->getLogicalId());
        if (is_object($cache)) {
            $cache->remove();
        }
        $market = self::getJsonRpc();
        $params = utils::o2a($this);
        switch ($this->getType()) {
            case 'plugin' :
                $cibDir = realpath(dirname(__FILE__) . '/../../plugins/' . $this->getLogicalId());
                $tmp = dirname(__FILE__) . '/../../tmp/' . $this->getLogicalId() . '.zip';
                if (file_exists($tmp)) {
                    if (!unlink($tmp)) {
                        throw new Exception(__('Impossible de supprimer : ',__FILE__) . $tmp . __('. Vérifiez les droits',__FILE__));
                    }
                }
                if (!create_zip($cibDir, $tmp)) {
                    throw new Exception(__('Echec de création du zip',__FILE__));
                }
                break;
            default :
                $type = $this->getType();
                if (class_exists($type) && method_exists(${type}, 'shareOnMarket')) {
                    $tmp = $type::shareOnMarket($this);
                }
                break;
        }
        if (!file_exists($tmp)) {
            throw new Exception(__('Impossible de trouver le fichier à envoyer : ',__FILE__) . $tmp);
        }
        $file = array(
            'file' => '@' . realpath($tmp)
        );
        if (!$market->sendRequest('market::save', $params, 30, $file)) {
            throw new Exception($market->getError());
        }
        if ($this->getType() == 'plugin') {
            config::save('installVersionDate', date('Y-m-d H:i:s'), $this->getLogicalId());
        } else {
            config::save($this->getLogicalId() . '::installVersionDate', date('Y-m-d H:i:s'), $this->getType());
        }
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getDatetime() {
        return $this->datetime;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getCategorie() {
        return $this->categorie;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getUser_id() {
        return $this->user_id;
    }

    public function getDownloaded() {
        return $this->downloaded;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setDatetime($datetime) {
        $this->datetime = $datetime;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setCategorie($categorie) {
        $this->categorie = $categorie;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function setUser_id($user_id) {
        $this->user_id = $user_id;
    }

    public function setDownloaded($downloaded) {
        $this->downloaded = $downloaded;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function getChangelog() {
        return $this->changelog;
    }

    public function setChangelog($changelog) {
        $this->changelog = $changelog;
    }

    public function getLogicalId() {
        return $this->logicalId;
    }

    public function setLogicalId($logicalId) {
        $this->logicalId = $logicalId;
    }

    public function getApi_author() {
        return $this->api_author;
    }

    public function setApi_author($api_author) {
        $this->api_author = $api_author;
    }

    public function getUtilization() {
        return $this->utilization;
    }

    public function setUtilization($utilization) {
        $this->utilization = $utilization;
    }

    public function getPurchase() {
        return $this->purchase;
    }

    public function setPurchase($purchase) {
        $this->purchase = $purchase;
    }

    public function getCost() {
        return $this->cost;
    }

    public function setCost($cost) {
        $this->cost = $cost;
    }

    public function getRealcost() {
        return $this->realcost;
    }

    public function setRealcost($realcost) {
        $this->realcost = $realcost;
    }

}

?>
