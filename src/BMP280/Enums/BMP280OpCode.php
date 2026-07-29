<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums;

enum BMP280OpCode: int
{
    case SOFT_RESET = 0xB6;
    case SOFT_RESET_REGISTER = 0xE0;
    case CONTROL_MEASURE_REGISTER = 0xF4;
    case CONFIG_REGISTER = 0xF5;
}
