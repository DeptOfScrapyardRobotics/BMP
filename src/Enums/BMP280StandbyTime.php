<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Enums;

use ScrapyardIO\Support\DataManipulation\ByteRegister;

enum BMP280StandbyTime: int
{
    case STANDBY_HALF_MS = 0;    // 0.5ms
    case STANDBY_MS_62 = 1;      // 62.5ms
    case STANDBY_MS_125 = 2;     // 125ms
    case STANDBY_MS_250 = 3;     // 250ms
    case STANDBY_MS_500 = 4;     // 500ms
    case STANDBY_MS_1000 = 5;    // 1 second
    case STANDBY_MS_2000 = 6;    // 2 seconds
    case STANDBY_MS_4000 = 7;    // 4 seconds

    public function setBits(ByteRegister $bytes): ByteRegister
    {
        return match($this) {
            self::STANDBY_HALF_MS  => $bytes->update(7,false)->update(6, false)->update(5,false),
            self::STANDBY_MS_62  => $bytes->update(7,false)->update(6, false)->update(5),
            self::STANDBY_MS_125  => $bytes->update(7,false)->update(6)->update(5,false),
            self::STANDBY_MS_250  => $bytes->update(7,false)->update(6)->update(5),
            self::STANDBY_MS_500  => $bytes->update(7)->update(6, false)->update(5,false),
            self::STANDBY_MS_1000  => $bytes->update(7)->update(6, false)->update(5),
            self::STANDBY_MS_2000  => $bytes->update(7)->update(6)->update(5,false),
            self::STANDBY_MS_4000  => $bytes->update(7)->update(6)->update(5),
        };
    }
}
