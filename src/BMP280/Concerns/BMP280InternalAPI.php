<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Concerns;

use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpCode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpMode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280ReadRegister;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Exceptions\BMP280Exception;

trait BMP280InternalAPI
{
    protected array $calibration = [];

    protected ?int $t_fine = null;

    protected function read(BMP280ReadRegister|BMP280OpCode $register_hex, int $length): array
    {
        return $this->carrier->read($register_hex->value, $length);
    }

    protected function write(BMP280OpCode $register_hex, array $command_data = []): int
    {
        return $this->carrier->write($register_hex->value, $command_data);
    }

    protected function reset(): void
    {
        $this->softReset();
        usleep(4000);
    }

    /**
     * @throws BMP280Exception
     */
    protected function readCoefficients(): void
    {
        $block = $this->readCalibrationBlock();
        if (count($block) !== 24) {
            throw BMP280Exception::invalidCalibrationLength(count($block));
        }

        $this->calibration = $this->decodeCalibration($block);
    }

    protected function ctrlMeas(): int
    {
        $results = $this->overscan_temperature << 5;
        $results += $this->overscan_pressure << 2;
        $results += $this->mode;

        return $results;
    }

    protected function config(): int
    {
        $config = 0;

        if ($this->mode == BMP280OpMode::NORMAL->value) {
            $config += $this->_t_standby->value << 5;
        }

        $config += $this->iir_filter->value << 2;

        return $config;
    }

    protected function u16le(int $lsb, int $msb): int
    {
        return (($msb & 0xFF) << 8) | ($lsb & 0xFF);
    }

    protected function s16le(int $lsb, int $msb): int
    {
        $value = $this->u16le($lsb, $msb);

        return ($value & 0x8000) ? $value - 0x10000 : $value;
    }

    protected function decodeCalibration(array $cal): array
    {
        return [
            'T1' => $this->u16le($cal[0], $cal[1]),
            'T2' => $this->s16le($cal[2], $cal[3]),
            'T3' => $this->s16le($cal[4], $cal[5]),
            'P1' => $this->u16le($cal[6], $cal[7]),
            'P2' => $this->s16le($cal[8], $cal[9]),
            'P3' => $this->s16le($cal[10], $cal[11]),
            'P4' => $this->s16le($cal[12], $cal[13]),
            'P5' => $this->s16le($cal[14], $cal[15]),
            'P6' => $this->s16le($cal[16], $cal[17]),
            'P7' => $this->s16le($cal[18], $cal[19]),
            'P8' => $this->s16le($cal[20], $cal[21]),
            'P9' => $this->s16le($cal[22], $cal[23]),
        ];
    }

    protected function readTemperature(): void
    {
        if ($this->mode !== BMP280OpMode::NORMAL->value) {
            $this->mode = BMP280OpMode::FORCE;

            while (($this->readStatus() & 0x08) !== 0) {
                usleep(2_000);
            }
        }

        $raw_temperature = $this->read24(BMP280ReadRegister::TEMP_DATA_REGISTER) / 16.0;
        $var1 = ($raw_temperature / 16384.0 - $this->calibration['T1'] / 1024.0) * $this->calibration['T2'];
        $var2 = (
            ($raw_temperature / 131072.0 - $this->calibration['T1'] / 8192.0)
            * ($raw_temperature / 131072.0 - $this->calibration['T1'] / 8192.0)
        ) * $this->calibration['T3'];

        $this->t_fine = (int) ($var1 + $var2);
    }

    protected function read24(BMP280ReadRegister $register): float
    {
        $results = 0.0;
        foreach ($this->read($register, 3) as $byte) {
            $results *= 256.0;
            $results += (float) ($byte & 0xFF);
        }

        return $results;
    }
}
