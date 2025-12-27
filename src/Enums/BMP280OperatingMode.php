<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Enums;

use ScrapyardIO\Support\DataManipulation\ByteRegister;

enum BMP280OperatingMode: int
{
    case SLEEP = 0;        // Sleep mode
    case FORCED = 1;       // Single measurement
    case NORMAL = 3;       // Continuous measurement

    public function setBits(ByteRegister $bytes): ByteRegister
    {
        return match($this) {
            self::SLEEP  => $bytes->update(1,false)->update(0, false),
            self::FORCED => $bytes->update(1,false)->update(0),
            self::NORMAL => $bytes->update(1)->update(0),
        };
    }
}
