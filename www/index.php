<?php
declare(strict_types=1);

/**
 * @file index.php
 *
 * Create web interface interface.
 */

use steinmb\Formatters\Block;
use steinmb\Utils\Calculate;
use steinmb\Logger\Logger;
use steinmb\Logger\FileStorage;
use steinmb\onewire\OneWire;
use steinmb\onewire\Sensor;
use steinmb\onewire\SystemClock;
use steinmb\onewire\Temperature;

include_once __DIR__ . '/vendor/autoload.php';

define('BREW_ROOT', getcwd());
//define('SENSOR_DIRECTORY', '/sys/bus/w1/devices');
define('SENSOR_DIRECTORY', BREW_ROOT . '/test');
define('LOG_DIRECTORY', BREW_ROOT . '/../../brewlogs/');
define('LOG_FILENAME', 'temperature.log');

if (file_exists(BREW_ROOT . '/' . 'temperatur.png')) {
    $graph = BREW_ROOT . '/' . 'temperatur.png';
}

$microLAN = new OneWire(SENSOR_DIRECTORY);
$probes = $microLAN->getSensors();

if (!$probes) {
    return;
}

$sensor = new Sensor(
  $microLAN,
  new SystemClock()
);

foreach ($probes as $probe) {
    $entity = $sensor->createEntity($probe);
    $temperature = new Temperature($entity);
    $block = new Block($entity, $temperature);
    $blocks[] = $block->listCurrent();
}

$logger = new Logger('temperature');
$handle = new FileStorage('/Users/steinmb/sites/brewlogs/temperature.log');
$logger->pushHandler($handle);
$logger->close();

$lastReading = $logger->lastEntry();

if ($lastReading) {
    $block->listHistoric(10, $lastReading, new Calculate($logger), $logger);
}

include 'page.php';
