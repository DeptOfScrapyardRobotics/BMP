<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Enums;

enum BMP280Command: int
{
    case DIG_T1 = 0x88;
    case DIG_T2 = 0x8A;
    case DIG_T3 = 0x8C;
    case DIG_P1 = 0x8E;
    case DIG_P2 = 0x90;
    case DIG_P3 = 0x92;
    case DIG_P4 = 0x94;
    case DIG_P5 = 0x96;
    case DIG_P6 = 0x98;
    case DIG_P7 = 0x9A;
    case DIG_P8 = 0x9C;
    case DIG_P9 = 0x9E;
    case CHIP_ID_REGISTER = 0xD0;
    case VERSION = 0xD1;
    case SOFTRESET = 0xE0;
    case CAL26 = 0xE1; /**< R calibration = 0xE1-0xF0 */
    case STATUS = 0xF3;
    case CONTROL_REGISTER = 0xF4;
    case CONFIG_REGISTER = 0xF5;
    case PRESSURE_DATA = 0xF7;
    case TEMPERATURE_DATA = 0xFA;
}
