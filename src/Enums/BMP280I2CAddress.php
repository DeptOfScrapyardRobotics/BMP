<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Enums;

enum BMP280I2CAddress: int
{
    case SDO_GROUNDED = 0x76;
    case SDO_ENERGIZED = 0x77;
}
