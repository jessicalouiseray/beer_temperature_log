<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use steinmb\EntityFactory;
use steinmb\Onewire\OneWire;
use steinmb\Onewire\Sensor;
use steinmb\SystemClock;

/**
 * Class SensorTest
 *
 * @covers \steinmb\Onewire\Sensor
 */
final class SensorTest extends TestCase
{
    private $sensor;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $oneWire = new OneWire(
          '/Users/steinmb/sites/beer_temperature_log/www/test',
          '/Users/steinmb/sites/beer_temperature_log/www/test/w1_master_slaves'
        );

        $this->sensor = new Sensor(
            $oneWire,
            new SystemClock(),
            new EntityFactory()
        );
    }

    public function testTemperatureSensor(): void
    {
        self::assertCount(4, $this->sensor->getTemperatureSensors());
    }

    public function testRawData(): void
    {
        self::assertStringContainsString('crc', $this->sensor->rawData());
    }


}
