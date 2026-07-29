<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP;

use Fabricate\Contracts\Circuits\CircuitException;

class BMPException extends CircuitException
{
    public static function transportMissingProtocol(): static
    {
        return new static('BMP devices require an SPI or an I2C capable connection.');
    }

    public static function invalidChipId(int $chip_id, int $expected_id): static
    {
        return new static(sprintf(
            'Invalid BMP Chip ID — expected 0x%02X, got 0x%02X',
            $expected_id,
            $chip_id,
        ));
    }

    public static function invalidCalibrationLength(int $length): static
    {
        return new static("Invalid BMP calibration length — {$length}");
    }

    public static function pressureDisabled(): static
    {
        return new static('BMP pressure overscan is disabled; enable overscan_pressure before measuring.');
    }

    public static function invalidCalibrationResult(): static
    {
        return new static('Invalid BMP pressure compensation result — check calibration registers.');
    }
}
