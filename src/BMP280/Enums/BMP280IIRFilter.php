<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums;

enum BMP280IIRFilter: int
{
    case DISABLED = 0x00;
    case FILTER_X2 = 0x01;
    case FILTER_X4 = 0x02;
    case FILTER_X8 = 0x03;
    case FILTER_X16 = 0x04;
}
