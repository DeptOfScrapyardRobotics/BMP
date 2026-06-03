<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums;

enum BMP280OpMode: int
{
    case SLEEP = 0x00;
    case FORCE = 0x01;
    case NORMAL = 0x03;

}
