<?php
/**
 * Create web interface interface.
 */

define('BREW_ROOT', getcwd());
require_once BREW_ROOT . '/includes/LogFile.php';
require_once BREW_ROOT . '/includes/Sensor.php';
require_once BREW_ROOT . '/includes/DataEntity.php';
require_once BREW_ROOT . '/includes/Block.php';

$data = new LogFile();
$sensors = new Sensor($data);
$entities = $sensors->getEntities();

foreach ($entities as $entity) {
  $blocks[] = new Block($entity);
}

include 'page.php';
