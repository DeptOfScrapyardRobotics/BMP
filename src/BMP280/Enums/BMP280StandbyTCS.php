<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums;

enum BMP280StandbyTCS: int
{
    case STANDBY_TC_0_5 = 0x00;
    case STANDBY_TC_62_5 = 0x01;
    case STANDBY_TC_125 = 0x02;
    case STANDBY_TC_250 = 0x03;
    case STANDBY_TC_500 = 0x04;
    case STANDBY_TC_1000 = 0x05;
    case STANDBY_TC_2000 = 0x06;
    case STANDBY_TC_4000 = 0x07;
}
