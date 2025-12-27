<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Enums;

use ScrapyardIO\Support\DataManipulation\ByteRegister;

enum BMP280Oversampling: int
{
    case SKIP = 0x00;         // Disabled
    case X1 = 0x01;           // 1x
    case X2 = 0x02;           // 2x
    case X4 = 0x03;           // 4x
    case X8 = 0x04;           // 8x
    case X16 = 0x05;          // 16x (highest accuracy)

    public function setTemperatureBits(ByteRegister $bytes): ByteRegister
    {
        return match($this) {
            self::SKIP  => $bytes->update(7,false)->update(6, false)->update(5,false),
            self::X1    => $bytes->update(7,false)->update(6, false)->update(5),
            self::X2    => $bytes->update(7,false)->update(6)->update(5,false),
            self::X4    => $bytes->update(7,false)->update(6)->update(5),
            self::X8    => $bytes->update(7)->update(6, false)->update(5,false),
            self::X16   => $bytes->update(7)->update(6, false)->update(5),
        };
    }

    public function setPressureBits(ByteRegister $bytes): ByteRegister
    {
        return match($this) {
            self::SKIP  => $bytes->update(4,false)->update(3, false)->update(2,false),
            self::X1    => $bytes->update(4,false)->update(3, false)->update(2),
            self::X2    => $bytes->update(4,false)->update(3)->update(2,false),
            self::X4    => $bytes->update(4,false)->update(3)->update(2),
            self::X8    => $bytes->update(4)->update(3, false)->update(2,false),
            self::X16   => $bytes->update(4)->update(3, false)->update(2),
        };
    }
}
