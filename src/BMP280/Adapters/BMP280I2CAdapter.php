<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Adapters;

use Waveforms\Carriers\I2C\I2CDevice;

class BMP280I2CAdapter extends BMP280DataCarrier
{
    public function __construct(
        I2CDevice $carrier
    ) {
        parent::__construct($carrier);
    }

    public function read(int $register_hex, int $length): array
    {
        /** @var I2CDevice $carrier */
        $carrier = &$this->carrier;

        return $carrier->readWrite([$register_hex & 0xFF], $length);
    }

    public function write(int $register_hex, array $command_data = []): int
    {
        /** @var I2CDevice $carrier */
        $carrier = &$this->carrier;

        return $carrier->write([$register_hex & 0xFF, ...$command_data]);
    }
}
