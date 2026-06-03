<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums;

enum BMP280ReadRegister: int
{
    case DIG_T1_REGISTER = 0x88;
    case CHIP_ID_REGISTER = 0xD0;
    case STATUS_REGISTER = 0xF3;
    case PRESSURE_DATA_REGISTER = 0xF7;
    case TEMP_DATA_REGISTER = 0xFA;
}
