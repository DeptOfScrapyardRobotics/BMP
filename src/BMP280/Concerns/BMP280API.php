<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Concerns;

use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280IIRFilter;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpCode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpMode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280Overscan;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280ReadRegister;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280StandbyTCS;
use DeptOfScrapyardRobotics\Sensors\BMP\BMPException;
use ValueError;

trait BMP280API
{
    use BMP280InternalAPI;

    public function readDeviceId(): int
    {
        return $this->read(BMP280ReadRegister::CHIP_ID_REGISTER, 1)[0];
    }

    public function readStatus(): int
    {
        $results = $this->read(BMP280ReadRegister::STATUS_REGISTER, 1)[0];
        usleep(2_000);

        return $results;
    }

    public function softReset(): void
    {
        $this->write(BMP280OpCode::SOFT_RESET_REGISTER, [BMP280OpCode::SOFT_RESET->value]);
    }

    public function readCalibrationBlock(): array
    {
        return $this->read(BMP280ReadRegister::DIG_T1_REGISTER, 24);
    }

    public function writeControlMeasure(): void
    {
        $this->write(BMP280OpCode::CONTROL_MEASURE_REGISTER, [$this->_ctrl_meas]);
    }

    public function readConfig(): int
    {
        return $this->read(BMP280OpCode::CONFIG_REGISTER, 1)[0];
    }

    public function writeConfig(): void
    {
        $normal_flag = false;

        if ($this->mode == BMP280OpMode::NORMAL->value) {
            $normal_flag = true;
            $this->mode = BMP280OpMode::SLEEP;
        }

        $this->write(BMP280OpCode::CONFIG_REGISTER, [$this->_config]);

        if ($normal_flag) {
            $this->mode = BMP280OpMode::NORMAL;
        }
    }

    public function getAltitude(): float
    {
        $pressure = $this->readPressure();

        if (is_null($pressure)) {
            return 0.0;
        }

        return 44330 * (1.0 - (($pressure / $this->sea_level_pressure) ** 0.1903));
    }

    public function setAltitude(float $value): void
    {
        $pressure = $this->readPressure();
        if (is_null($pressure)) {
            return;
        }

        $this->sea_level_pressure = $pressure / ((1.0 - $value / 44330.0) ** 5.255);
    }

    public function getIIRFilter(): BMP280IIRFilter
    {
        return $this->_iir_filter;
    }

    public function setIIRFilter(BMP280IIRFilter $value): void
    {
        $this->_iir_filter = $value;
        $this->writeConfig();
    }

    public function getMeasurementTimeMax(): float
    {
        $overscans = [
            BMP280Overscan::DISABLED->value => 0,
            BMP280Overscan::OVERSCAN_X1->value => 1,
            BMP280Overscan::OVERSCAN_X2->value => 2,
            BMP280Overscan::OVERSCAN_X4->value => 4,
            BMP280Overscan::OVERSCAN_X8->value => 8,
            BMP280Overscan::OVERSCAN_X16->value => 16,
        ];

        $time_ms = 1.25;
        if ($this->overscan_temperature !== BMP280Overscan::DISABLED->value) {
            $time_ms += 2.3 * $overscans[$this->overscan_temperature];
        }

        if ($this->overscan_pressure !== BMP280Overscan::DISABLED->value) {
            $time_ms += 2.3 * $overscans[$this->overscan_pressure] + 0.575;
        }

        return $time_ms;
    }

    public function getMeasurementTimeTypical(): float
    {
        $overscans = [
            BMP280Overscan::DISABLED->value => 0,
            BMP280Overscan::OVERSCAN_X1->value => 1,
            BMP280Overscan::OVERSCAN_X2->value => 2,
            BMP280Overscan::OVERSCAN_X4->value => 4,
            BMP280Overscan::OVERSCAN_X8->value => 8,
            BMP280Overscan::OVERSCAN_X16->value => 16,
        ];

        $time_ms = 1.0;
        if ($this->overscan_temperature !== BMP280Overscan::DISABLED->value) {
            $time_ms += 2 * $overscans[$this->overscan_temperature];
        }

        if ($this->overscan_pressure !== BMP280Overscan::DISABLED->value) {
            $time_ms += 2 * $overscans[$this->overscan_pressure] + 0.5;
        }

        return $time_ms;
    }

    public function getMode(): int
    {
        return $this->_mode->value;
    }

    public function setMode(BMP280OpMode $value): void
    {
        $this->_mode = $value;
        $this->writeControlMeasure();
    }

    public function getOverscanTemperature(): int
    {
        return $this->_overscan_temperature->value;
    }

    public function setOverscanTemperature(BMP280Overscan $value): void
    {
        $this->_overscan_temperature = $value;
        $this->writeControlMeasure();
    }

    public function getOverscanPressure(): int
    {
        return $this->_overscan_pressure->value;
    }

    public function setOverscanPressure(BMP280Overscan $value): void
    {
        $this->_overscan_pressure = $value;
        $this->writeControlMeasure();
    }

    public function getSeaLevelPressure(): float
    {
        return $this->sea_level_pressure;
    }

    public function setSeaLevelPressure(float $value): void
    {
        $this->sea_level_pressure = $value;
    }

    /**
     * @throws BMPException
     */
    public function readPressure(): ?float
    {
        if ($this->overscan_pressure === BMP280Overscan::DISABLED->value) {
            return null;
        }

        $this->readTemperature();

        $adc = $this->read24(BMP280ReadRegister::PRESSURE_DATA_REGISTER) / 16.0;
        $var1 = ((float) $this->t_fine) / 2.0 - 64000.0;
        $var2 = $var1 * $var1 * $this->calibration['P6'] / 32768.0;
        $var2 += $var1 * $this->calibration['P5'] * 2.0;
        $var2 = $var2 / 4.0 + $this->calibration['P4'] * 65536.0;
        $var3 = $this->calibration['P3'] * $var1 * $var1 / 524288.0;
        $var1 = ($var3 + $this->calibration['P2'] * $var1) / 524288.0;
        $var1 = (1.0 + $var1 / 32768.0) * $this->calibration['P1'];

        if (! $var1) {
            throw BMPException::invalidCalibrationResult();
        }

        $pressure = 1048576.0 - $adc;
        $pressure = (($pressure - $var2 / 4096.0) * 6250.0) / $var1;
        $var1 = $this->calibration['P9'] * $pressure * $pressure / 2147483648.0;
        $var2 = $pressure * $this->calibration['P8'] / 32768.0;
        $pressure += ($var1 + $var2 + $this->calibration['P7']) / 16.0;

        return $pressure / 100.0;
    }

    public function getStandbyPeriod(): int
    {
        return $this->_t_standby->value;
    }

    public function setStandbyPeriod(int $value): void
    {
        $standby_period = BMP280StandbyTCS::tryFrom($value);
        if (is_null($standby_period)) {
            throw new ValueError("Standby Period '{$value}' not supported");
        }

        if ($this->_t_standby === $standby_period) {
            return;
        }

        $this->_t_standby = $standby_period;
        $this->writeConfig();
    }

    public function getTemp(): float
    {
        $this->readTemperature();

        return $this->t_fine / 5120.0;
    }
}
