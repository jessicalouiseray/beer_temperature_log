<?php
declare(strict_types=1);

/**
 * @file index.php
 *
 * Create web interface interface.
 */

use steinmb\EntityFactory;
use steinmb\RuntimeEnvironment;
use steinmb\Formatters\Block;
use steinmb\Formatters\HTMLFormatter;
use steinmb\Utils\Calculate;
use steinmb\Logger\Logger;
use steinmb\Logger\FileStorage;
use steinmb\Onewire\OneWire;
use steinmb\Onewire\Sensor;
use steinmb\SystemClock;
use steinmb\Onewire\Temperature;

include_once __DIR__ . '/vendor/autoload.php';

RuntimeEnvironment::setSetting('BREW_ROOT', __DIR__);
$logger = new Logger('temperature');
$handle = new FileStorage();
$logger->pushHandler($handle);
$lastReading = $logger->lastEntry();
$sensor = new Sensor(new OneWire(), new SystemClock(), new EntityFactory());
$probes = (!$sensor->getTemperatureSensors()) ? exit('No probes found.'): $sensor->getTemperatureSensors();
$calculate = new Calculate($logger);

foreach ($probes as $probe) {
    $entity = $sensor->createEntity($probe);
    $temperature = new Temperature($entity);
    $formatter = new Block($temperature, new HTMLFormatter($entity));
    $blocks[] = $formatter->unorderedlist();

    if ($lastReading) {
        $blocks[] = $formatter->trendList($calculate, 10, $lastReading);
    }
}

if (file_exists(RuntimeEnvironment::getSetting('BREW_ROOT') . '/temperatur.png')) {
    $graph = RuntimeEnvironment::getSetting('BREW_ROOT') . '/temperatur.png';
}
include 'page.php';

$logger->close();
