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

if (php_sapi_name() != 'cli' || isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['argc'])) {
    header("Status: 404 Not Found");
    header('HTTP/1.0 404 Not Found');
    $_SERVER['REDIRECT_STATUS'] = 404;
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    exit();
}

require_once dirname(__FILE__) . "/core.inc.php";

if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_GET[$argList[0]] = $argList[1];
        }
    }
}

$scenario = scenario::byId(init('scenario_id'));
if (!is_object($scenario)) {
    log::add('scenario', 'info', __('Scenario non trouvé verifier id : ', __FILE__) . init('scenario_id'));
    die(__('Scenario non trouvé verifier id : ', __FILE__) . init('scenario_id'));
}
set_time_limit($scenario->getTimeout(config::byKey('maxExecTimeScript', 1) * 60));

try {
    if (($scenario->getIsActive() == 1 || init('force') == 1)) {
        if ($scenario->getState() == 'in progress') {
            sleep(1);
        }
        if ($scenario->getState() == 'in progress') {
            sleep(1);
        }
        if ($scenario->getState() == 'in progress') {
            sleep(1);
        }
        if ($scenario->getState() == 'in progress') {
            sleep(1);
        }
        if ($scenario->getState() == 'in progress') {
            $scenario->setLog(__('Impossible de lancer le scenario car déja en cours', __FILE__));
            die(__('Impossible de lancer le scenario car deja en cours', __FILE__));
        }
        $scenario->setPID(getmypid());
        $scenario->save();
        log::add('scenario', 'info', __('Verification du scenario ', __FILE__) . $scenario->getHumanName() . __(' avec le PID : ', __FILE__) . getmypid());
        $scenario->execute();
        $scenario->setState('stop');
    } else {
        $scenario->setLog(__('Impossible de lancer le scenario car désactivé', __FILE__));
        die(__('Impossible de lancer le scenario car desactivé', __FILE__));
    }
} catch (Exception $e) {
    log::add('scenario', 'error', __('Scenario  : ', __FILE__) . $scenario->getName() . '. ' . __('Erreur : ', __FILE__) . $e->getMessage());
    $scenario->setState('error');
    $scenario->setPID('');
    $scenario->save();
    die();
}

$scenario->setPID('');
$scenario->save();
?>
