<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\Enums;

enum BMPI2CAddress: int
{
    case SDO_GROUNDED = 0x76;
    case SDO_ENERGIZED = 0x77;
}
