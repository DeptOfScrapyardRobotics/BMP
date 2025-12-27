<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Enums;

use ScrapyardIO\Support\DataManipulation\ByteRegister;

enum BMP280IIRFilter: int
{
    case FILTER_OFF = 0x00;
    case FILTER_2 = 1;     // 2 samples
    case FILTER_4 = 2;     // 4 samples
    case FILTER_8 = 3;     // 8 samples
    case FILTER_16 = 4;    // 16 samples

    public function setBits(ByteRegister $bytes): ByteRegister
    {
        return match($this) {
            self::FILTER_OFF  => $bytes->update(4,false)->update(3, false)->update(2,false),
            self::FILTER_2  => $bytes->update(4,false)->update(3, false)->update(2),
            self::FILTER_4  => $bytes->update(4,false)->update(3)->update(2,false),
            self::FILTER_8  => $bytes->update(4,false)->update(3)->update(2),
            self::FILTER_16  => $bytes->update(4)->update(3, false)->update(2,false),
        };
    }
}
