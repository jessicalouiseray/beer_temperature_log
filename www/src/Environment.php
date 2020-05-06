<?php declare(strict_types = 1);

namespace steinmb;

class Environment
{
    private static $settings = [
        'BREW_ROOT' => '',
        'DEMO_MODE' => FALSE,
        'LOG_DIRECTORY' => '../../brewlogs',
        'LOG_FILENAME' => 'temperature.csv',
        'LOG_INFO' => 'info.log',
        'SENSOR_DIRECTORY' => '/sys/bus/w1/devices',
        'SENSORS' => '/sys/bus/w1/devices/w1_bus_master1/w1_master_slaves',
        'TEST_DATA' => '/test',
    ];

    public static function getSetting($setting)
    {

        if (!self::$settings['BREW_ROOT']) {
            self::setSetting('BREW_ROOT', dirname(__DIR__));
        }

        if (self::$settings['DEMO_MODE'] === TRUE) {
            self::demoMode();
        }

        return self::$settings[$setting];
    }

    static private function demoMode()
    {
        self::$settings['SENSOR_DIRECTORY'] = self::$settings['BREW_ROOT'] . self::$settings['TEST_DATA'];
        self::$settings['SENSORS'] = self::$settings['SENSOR_DIRECTORY'] . '/w1_master_slaves';
    }

    public static function setSetting(string $setting, $value): void
    {
        self::$settings[$setting] = $value;
    }

}
