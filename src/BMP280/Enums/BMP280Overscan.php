<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums;

enum BMP280Overscan: int
{
    case DISABLED = 0x00;
    case OVERSCAN_X1 = 0x01;
    case OVERSCAN_X2 = 0x02;
    case OVERSCAN_X4 = 0x03;
    case OVERSCAN_X8 = 0x04;
    case OVERSCAN_X16 = 0x05;
}
