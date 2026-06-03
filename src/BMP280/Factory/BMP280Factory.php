<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Factory;

use BareMetal\CircuitFactory;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Adapters\BMP280I2CAdapter;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Adapters\BMP280SPIAdapter;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280IIRFilter;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpMode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280Overscan;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280StandbyTCS;
use Exception;
use Waveforms\Carriers\I2C\Factory\I2CConnectionBuilder;
use Waveforms\Carriers\I2C\I2CDevice;
use Waveforms\Carriers\SPI\Enums\SPIMode;
use Waveforms\Carriers\SPI\Factory\SPIConnectionBuilder;

class BMP280Factory extends CircuitFactory
{
    public BMP280IIRFilter $iir_filter = BMP280IIRFilter::DISABLED;

    public BMP280Overscan $overscan_temperature = BMP280Overscan::OVERSCAN_X2;

    public BMP280Overscan $overscan_pressure = BMP280Overscan::OVERSCAN_X16;

    public BMP280StandbyTCS $t_standby = BMP280StandbyTCS::STANDBY_TC_0_5;

    public BMP280OpMode $mode = BMP280OpMode::SLEEP;

    public null|I2CConnectionBuilder|SPIConnectionBuilder $connection = null;

    public function __construct(
        public I2CConnectionBuilder $i2c_connection,
        public SPIConnectionBuilder $spi_connection,

    ) {}

    public function i2c(string|int $chip_device, int $slave_address): static
    {
        $this->connection = $this->i2c_connection->firstly($chip_device)
            ->slaveAddress($slave_address);

        return $this;
    }

    public function spi(string|int $master, int $chip_select): static
    {
        $this->connection = $this->spi_connection->firstly($master)
            ->chip($chip_select)
            ->speed(100000)
            ->mode(SPIMode::MODE_0);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function create(): BMP280
    {
        $carrier = $this->connection?->boot();
        if (is_null($carrier)) {
            throw new Exception('A connection was not registered.');
        }

        if ($carrier instanceof I2CDevice) {
            $carrier = new BMP280I2CAdapter($carrier);
        } else {
            $carrier = new BMP280SPIAdapter($carrier);
        }

        return new BMP280(
            $carrier,
            $this->iir_filter,
            $this->overscan_temperature,
            $this->overscan_pressure,
            $this->t_standby,
            $this->mode
        );
    }
}
