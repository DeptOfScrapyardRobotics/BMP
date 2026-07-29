<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280;

use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Concerns\BMP280API;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280IIRFilter;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpMode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280Overscan;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280StandbyTCS;
use DeptOfScrapyardRobotics\Sensors\BMP\BMPCarrierTransport;
use DeptOfScrapyardRobotics\Sensors\BMP\BMPException;
use DeptOfScrapyardRobotics\Sensors\BMP\Enums\BMPI2CAddress;
use Exception;
use Fabricate\Contracts\Circuits\Attributes\IntegratedCircuit;
use Fabricate\Contracts\Circuits\IntegratedCircuit as CircuitContract;
use Fabricate\Contracts\NutsAndBolts\BootSequence;
use Fabricate\Contracts\Sensors\Interfaces\Barometer;
use Fabricate\Contracts\Sensors\Interfaces\Thermometer;
use GeneralPurposeIO\I2C\I2C;
use GeneralPurposeIO\I2C\I2CSlave;
use GeneralPurposeIO\SPI\SPI;
use GeneralPurposeIO\SPI\SPIDevice;

/**
 * @property int $chip_id
 * @property int $status
 * @property int $_ctrl_meas
 * @property int $_config
 * @property int $config
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
#[IntegratedCircuit('I2C', 'SPI')]
class BMP280 implements CircuitContract, BootSequence, Thermometer, Barometer
{
    use BMP280API;

    /**
     * @throws Exception
     */
    public function __construct(
        protected readonly BMPCarrierTransport $transport,
        protected BMP280IIRFilter $_iir_filter,
        protected BMP280Overscan $_overscan_temperature,
        protected BMP280Overscan $_overscan_pressure,
        protected BMP280StandbyTCS $_t_standby,
        protected BMP280OpMode $_mode,
        bool $boot_now = false,
    ) {
        if ($boot_now) {
            $this->boot();
        }
    }

    /**
     * @throws BMPException
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
            default => throw BMPException::invalidProperty($name, static::class),
        };
    }

    /**
     * @throws BMPException
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
            default => throw BMPException::invalidProperty($name, static::class),
        };
    }

    public function close(): void
    {
        $this->transport->close();
    }

    /**
     * @throws Exception
     */
    public static function i2c(
        string|int $device,
        ?string $adapter = null,
        int $slave = BMPI2CAddress::SDO_GROUNDED->value,
        BMP280IIRFilter $iir_filter = BMP280IIRFilter::DISABLED,
        BMP280Overscan $overscan_temperature = BMP280Overscan::OVERSCAN_X2,
        BMP280Overscan $overscan_pressure = BMP280Overscan::OVERSCAN_X16,
        BMP280StandbyTCS $t_standby = BMP280StandbyTCS::STANDBY_TC_0_5,
        BMP280OpMode $mode = BMP280OpMode::SLEEP,
        bool $boot_now = true,
    ): static {
        $i2c = I2C::adapter($adapter)
            ->device($device)
            ->bus()
            ->slave($slave);

        return static::fromI2CBus(
            $i2c,
            $iir_filter,
            $overscan_temperature,
            $overscan_pressure,
            $t_standby,
            $mode,
            $boot_now,
        );
    }

    /**
     * @throws Exception
     */
    public static function fromI2CBus(
        I2CSlave $i2c,
        BMP280IIRFilter $iir_filter = BMP280IIRFilter::DISABLED,
        BMP280Overscan $overscan_temperature = BMP280Overscan::OVERSCAN_X2,
        BMP280Overscan $overscan_pressure = BMP280Overscan::OVERSCAN_X16,
        BMP280StandbyTCS $t_standby = BMP280StandbyTCS::STANDBY_TC_0_5,
        BMP280OpMode $mode = BMP280OpMode::SLEEP,
        bool $boot_now = true,
    ): static {
        $transport = new BMPCarrierTransport(i2c: $i2c);

        return new static(
            $transport,
            $iir_filter,
            $overscan_temperature,
            $overscan_pressure,
            $t_standby,
            $mode,
            $boot_now,
        );
    }

    /**
     * @throws Exception
     */
    public static function spi(
        string|int $device,
        string|int $chip_select,
        ?string $adapter = null,
        BMP280IIRFilter $iir_filter = BMP280IIRFilter::DISABLED,
        BMP280Overscan $overscan_temperature = BMP280Overscan::OVERSCAN_X2,
        BMP280Overscan $overscan_pressure = BMP280Overscan::OVERSCAN_X16,
        BMP280StandbyTCS $t_standby = BMP280StandbyTCS::STANDBY_TC_0_5,
        BMP280OpMode $mode = BMP280OpMode::SLEEP,
        bool $boot_now = true,
    ): static {
        $spi = SPI::adapter($adapter)->device($device)
            ->mode(0)->speed(100000)->bus()
            ->select($chip_select);

        return static::fromSPIBus(
            $spi,
            $iir_filter,
            $overscan_temperature,
            $overscan_pressure,
            $t_standby,
            $mode,
            $boot_now,
        );
    }

    /**
     * @throws BMPException
     * @throws Exception
     */
    public static function fromSPIBus(
        SPIDevice $spi,
        BMP280IIRFilter $iir_filter = BMP280IIRFilter::DISABLED,
        BMP280Overscan $overscan_temperature = BMP280Overscan::OVERSCAN_X2,
        BMP280Overscan $overscan_pressure = BMP280Overscan::OVERSCAN_X16,
        BMP280StandbyTCS $t_standby = BMP280StandbyTCS::STANDBY_TC_0_5,
        BMP280OpMode $mode = BMP280OpMode::SLEEP,
        bool $boot_now = true,
    ): static {
        $transport = new BMPCarrierTransport(spi: $spi);

        return new static(
            $transport,
            $iir_filter,
            $overscan_temperature,
            $overscan_pressure,
            $t_standby,
            $mode,
            $boot_now,
        );
    }
}
