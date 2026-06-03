<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280;

use BareMetal\IntegratedCircuit;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Adapters\BMP280DataCarrier;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Concerns\BMP280API;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280IIRFilter;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpMode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280Overscan;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280StandbyTCS;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Exceptions\BMP280Exception;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Factory\BMP280Factory;
use Exception;
use RealityInterface\Sensors\Attributes\MeasuresBarometricPressure;
use RealityInterface\Sensors\Attributes\MeasuresTemperature;
use RealityInterface\Sensors\Contracts\Applied\Environmental\PressureSensor;
use RealityInterface\Sensors\Contracts\Applied\Environmental\TemperatureSensor;
use RealityInterface\Sensors\Enums\SensorType;
use Waveforms\Carriers\I2C\I2C;
use Waveforms\Carriers\SPI\SPI;

/**
 * @property int $chip_id
 * @property int $_ctrl_meas
 * @property int $_config
 * @property float $altitude
 * @property BMP280IIRFilter $iir_filter
 * @property float $measurement_time_max
 * @property float $measurement_time_typical
 * @property int $mode
 * @property int $overscan_temperature
 * @property int $overscan_pressure
 * @property ?float $pressure
 * @property float $sea_level_pressure
 * @property int $standby_period
 * @property float $temperature
 */
#[MeasuresTemperature(SensorType::TEMPERATURE)]
#[MeasuresBarometricPressure(SensorType::RELATIVE_HUMIDITY)]
class BMP280 extends IntegratedCircuit implements PressureSensor, TemperatureSensor
{
    use BMP280API;

    protected bool $booted = false;

    protected float $sea_level_pressure = 1013.25;

    protected int $hardwired_chip_id = 0x58;

    protected int $hardwired_reset_command = 0xB6;

    /**
     * @throws BMP280Exception
     */
    public function __construct(
        protected readonly BMP280DataCarrier $carrier,
        protected BMP280IIRFilter $_iir_filter,
        protected BMP280Overscan $_overscan_temperature,
        protected BMP280Overscan $_overscan_pressure,
        protected BMP280StandbyTCS $_t_standby,
        protected BMP280OpMode $_mode
    ) {
        $this->boot();
    }

    public function getTemperature(): ?float
    {
        return $this->getTemp();
    }

    public function getPressure(): ?float
    {
        return $this->readPressure();
    }

    /**
     * @throws BMP280Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'chip_id' => $this->readDeviceId(),
            'status' => $this->readStatus(),
            '_ctrl_meas' => $this->ctrlMeas(),
            '_config' => $this->config(),
            'config' => $this->readConfig(),
            'altitude' => $this->getAltitude(),
            'iir_filter' => $this->getIIRFilter(),
            'measurement_time_max' => $this->getMeasurementTimeMax(),
            'measurement_time_typical' => $this->getMeasurementTimeTypical(),
            'mode' => $this->getMode(),
            'overscan_temperature' => $this->getOverscanTemperature(),
            'overscan_pressure' => $this->getOverscanPressure(),
            'pressure' => $this->readPressure(),
            'sea_level_pressure' => $this->getSeaLevelPressure(),
            'standby_period' => $this->getStandbyPeriod(),
            'temperature' => $this->getTemp(),
            default => throw BMP280Exception::invalidProperty($name)
        };
    }

    /**
     * @throws BMP280Exception
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'altitude' => $this->setAltitude($value),
            'iir_filter' => $this->setIIRFilter($value),
            'mode' => $this->setMode($value),
            'overscan_temperature' => $this->setOverscanTemperature($value),
            'overscan_pressure' => $this->setOverscanPressure($value),
            'sea_level_pressure' => $this->setSeaLevelPressure($value),
            'standby_period' => $this->setStandbyPeriod($value),
            default => throw BMP280Exception::invalidProperty($name)
        };
    }

    /**
     * @throws BMP280Exception
     */
    protected function boot(): void
    {
        if (! $this->booted) {
            if ($this->chip_id != $this->hardwired_chip_id) {
                throw BMP280Exception::invalidChipId($this->chip_id);
            }

            $this->reset();
            $this->readCoefficients();
            $this->writeControlMeasure();
            $this->writeConfig();

            $this->booted = true;
        }
    }

    /**
     * @throws Exception
     */
    public static function connection(string $driver): BMP280Factory
    {
        return new BMP280Factory(
            I2C::connection($driver),
            SPI::connection($driver)
        );
    }
}
