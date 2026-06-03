<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Exceptions;

use Exception;

class BMP280Exception extends Exception
{
    public static function invalidProperty(string $name): static
    {
        return new static("Invalid property $name");
    }

    public static function invalidChipId(int $chip_id): static
    {
        return new static(sprintf('Invalid BMP280 Chip ID — expected 0x58, got 0x%02X', $chip_id));
    }

    public static function invalidCalibrationLength(int $length): static
    {
        return new static("Invalid BMP280 Calibration Length - {$length}");
    }
}
