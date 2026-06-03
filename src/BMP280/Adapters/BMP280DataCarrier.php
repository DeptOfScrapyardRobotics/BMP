<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Adapters;

use Waveforms\Carriers\I2C\I2CDevice;
use Waveforms\Carriers\SPI\SPIDevice;

abstract class BMP280DataCarrier
{
    public function __construct(
        protected I2CDevice|SPIDevice $carrier
    ) {}

    abstract public function read(int $register_hex, int $length): array;

    abstract public function write(int $register_hex, array $command_data = []): int;
}
