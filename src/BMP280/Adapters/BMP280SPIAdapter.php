<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Adapters;

use Waveforms\Carriers\SPI\SPIDevice;

class BMP280SPIAdapter extends BMP280DataCarrier
{
    public function __construct(
        SPIDevice $carrier
    ) {
        parent::__construct($carrier);
    }

    public function read(int $register_hex, int $length): array
    {
        /** @var SPIDevice $carrier */
        $carrier = &$this->carrier;
        $register_hex = ($register_hex | 0x80) & 0xFF;
        $raw = $carrier->transfer([
            $register_hex,
            ...array_fill(0, $length, 0x00),
        ]);

        return array_slice($raw, 1, $length);
    }

    public function write(int $register_hex, array $command_data = []): int
    {
        /** @var SPIDevice $carrier */
        $carrier = &$this->carrier;

        return $carrier->write([
            $register_hex & 0x7F,
            ...$command_data,
        ]);
    }
}
