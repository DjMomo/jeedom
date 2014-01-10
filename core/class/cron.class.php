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

class cron {
    /*     * *************************Attributs****************************** */

    private $id;
    private $server;
    private $enable = 1;
    private $class = '';
    private $function;
    private $lastRun = '0000-00-00 00:00:00';
    private $duration = '0';
    private $state = 'stop';
    private $pid = '';
    private $schedule = '';
    private $timeout;
    private $deamon = 0;

    /*     * ***********************Methode static*************************** */

    public static function all() {
        $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
                FROM cron';
        return DB::Prepare($sql, array(), DB::FETCH_TYPE_ALL, PDO::FETCH_CLASS, __CLASS__);
    }

    public static function byId($_id) {
        $value = array(
            'id' => $_id,
        );
        $sql = 'SELECT ' . DB::buildField(__CLASS__) . '
                FROM cron
                WHERE id=:id';
        return DB::Prepare($sql, $value, DB::FETCH_TYPE_ROW, PDO::FETCH_CLASS, __CLASS__);
    }

    public static function nbCronRun() {
        return exec('ps ax | grep jeeCron.php | grep -v "grep" | grep -v "sudo" | grep -v "shell=/bin/bash - " | grep -v "/bin/bash -c " | grep -v "/bin/sh -c " | grep -v ' . posix_getppid() . ' | grep -v ' . getmypid() . ' | wc -l');
    }

    public static function nbProcess() {
        $result = exec('ps ax | wc -l');
        return $result;
    }

    public static function loadAvg() {
        return sys_getloadavg();
    }

    public static function setPidFile() {
        $path = dirname(__FILE__) . '/../../jeeCron.pid';
        $fp = fopen($path, 'w');
        fwrite($fp, getmypid());
        fclose($fp);
    }

    public static function getPidFile() {
        $path = dirname(__FILE__) . '/../../jeeCron.pid';
        return file_get_contents($path);
    }

    public static function jeeCronRun() {
        $pid = self::getPidFile();
        if ($pid == '' || !is_numeric($pid)) {
            return false;
        }
        $result = exec('ps -p' . $pid . ' e | grep "jeeCron.php" | wc -l');
        if ($result == 0) {
            return false;
        }
        return true;
    }

    /*     * *********************Methode d'instance************************* */

    public function preSave() {
        if ($this->getFunction() == '') {
            throw new Exception('Function of cron task can not be empty');
        }
        if ($this->getSchedule() == '') {
            throw new Exception('Schedules of cron task can not be empty : ' . print_r($this, true));
        }
    }

    public function save() {
        return DB::save($this);
    }

    public function remove() {
        return DB::remove($this);
    }

    public function start() {
        if (!$this->running()) {
            $this->setState('starting');
            $this->save();
        }
    }

    public function running() {
        if ($this->getPID() > 0 && $this->getServer() == gethostname()) {
            exec('ps ' . $this->pid, $pState);
            return (count($pState) >= 2);
        }
        return false;
    }

    public function refresh() {
        $this->updateFromObject(self::byId($this->getId()));
        if ($this->getPID() > 0 && !$this->running()) {
            $this->updateFromObject(self::byId($this->getId()));
            if ($this->getPID() > 0 && !$this->running()) {
                $this->setState('stop');
                $this->setDuration(-1);
                $this->setPID();
                $this->setServer('');
                $this->save();
            }
        }
        return true;
    }

    public function stop() {
        if ($this->running()) {
            $this->setState('stoping');
            $this->save();
        }
    }

    private function updateFromObject($_cron) {
        $reflection = new ReflectionClass(__CLASS__);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            $property->setAccessible(true);
            $value = $property->getValue($_cron);
            $property->setAccessible(false);
            $this->$name = $value;
        }
    }

    public function isDue() {
        //if never sent
        if ($this->getLastRun() == '') {
            return true;
        }
        //check if already sent on that minute 
        $last = strtotime($this->getLastRun());
        $now = time();
        $now = ($now - $now % 60);
        $last = ($last - $last % 60);
        if ($now == $last) {
            return false;
        }
        try {
            $c = new Cron\CronExpression($this->getSchedule(), new Cron\FieldFactory);
            if ($c->isDue()) {
                return true;
            }

            $lastCheck = new DateTime($this->getLastRun());
            $prev = $c->getPreviousRunDate();
            if ($lastCheck < $prev) {
                if ($lastCheck->diff($c->getPreviousRunDate())->format('%i') > 5) {
                    log::add('cron', 'error', 'Retard ' . ( $lastCheck->diff($c->getPreviousRunDate())->format('%i min')) . ': ' . $this->getClass() . '::' . $this->getFunction() . '(). Rattrapage en cours...');
                }
                return true;
            }
        } catch (Exception $exc) {
            log::add('cron', 'error', 'Expression cron non valide : ' . $this->getSchedule());
            return false;
        }
        return false;
    }

    /*     * **********************Getteur Setteur*************************** */

    public function getId() {
        return $this->id;
    }

    public function getClass() {
        return $this->class;
    }

    public function getFunction() {
        return $this->function;
    }

    public function getLastRun() {
        return $this->lastRun;
    }

    public function getState() {
        return $this->state;
    }

    public function getEnable() {
        return $this->enable;
    }

    public function getDuration() {
        return $this->duration;
    }

    public function getPID() {
        return $this->pid;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setEnable($enable) {
        $this->enable = $enable;
    }

    public function setClass($class) {
        $this->class = $class;
    }

    public function setFunction($function) {
        $this->function = $function;
    }

    public function setLastRun($lastRun) {
        $this->lastRun = $lastRun;
    }

    public function setDuration($duration) {
        $this->duration = $duration;
    }

    public function setState($state) {
        $this->state = $state;
    }

    public function setPID($pid = '') {
        $this->pid = $pid;
    }

    public function getServer() {
        return $this->server;
    }

    public function setServer($server) {
        $this->server = $server;
    }

    public function getSchedule() {
        return $this->schedule;
    }

    public function setSchedule($schedule) {
        $this->schedule = $schedule;
    }

    public function getDeamon() {
        return $this->deamon;
    }

    public function setDeamon($deamons) {
        $this->deamon = $deamons;
    }

    public function getTimeout() {
        $timeout = $this->timeout;
        if ($timeout == 0) {
            $timeout = config::byKey('maxExecTimeCrontask', 'core', 60);
        }
        return $timeout;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

}

?>
