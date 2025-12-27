<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Concerns;

use ScrapyardIO\Support\DataManipulation\ByteRegister;
use ScrapyardIO\Sensors\Environmental\BMP280\Enums\BMP280Command;
use ScrapyardIO\Sensors\Environmental\BMP280\Enums\BMP280IIRFilter;
use ScrapyardIO\Sensors\Environmental\BMP280\Enums\BMP280StandbyTime;
use ScrapyardIO\Sensors\Environmental\BMP280\Enums\BMP280Oversampling;
use ScrapyardIO\Sensors\Environmental\BMP280\Enums\BMP280OperatingMode;
use ScrapyardIO\Sensors\Environmental\BMP280\Exceptions\BMP280Exception;

trait BMP280BootSequence
{
    protected BMP280Oversampling $temp_os = BMP280Oversampling::X16;
    protected BMP280Oversampling $pressure_os = BMP280Oversampling::X16;
    protected BMP280OperatingMode $ops_mode = BMP280OperatingMode::NORMAL;

    protected BMP280StandbyTime $standby_time = BMP280StandbyTime::STANDBY_HALF_MS;
    protected BMP280IIRFilter $iir_filter = BMP280IIRFilter::FILTER_OFF;
    protected int $spi_wires = 4;

    protected array $temp_calibration = [
        'T1' => 0, 'T2' => 0, 'T3' => 0
    ];
    protected array $pressure_calibration = [
        'P1' => 0, 'P2' => 0, 'P3' => 0,
        'P4' => 0, 'P5' => 0, 'P6' => 0,
        'P7' => 0, 'P8' => 0, 'P9' => 0,
    ];
    /**
     * @return void
     * @throws BMP280Exception
     */
    public function readDeviceId(): void
    {
        [$device_id] = $this->readData(BMP280Command::CHIP_ID_REGISTER->value, 1);
        if($device_id != 0x58) throw BMP280Exception::invalidDeviceID($device_id, 0x58);
    }

    public function tempCoefficients() : void
    {
        [$low, $high] = $this->readData(BMP280Command::DIG_T1->value, 2);
        $this->temp_calibration['T1'] = $this->convertTo16Bit($low, $high, false);

        [$low, $high] = $this->readData(BMP280Command::DIG_T2->value, 2);
        $this->temp_calibration['T2'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_T3->value, 2);
        $this->temp_calibration['T3'] = $this->convertTo16Bit($low, $high);
    }

    public function pressureCoefficients() : void
    {
        [$low, $high] = $this->readData(BMP280Command::DIG_P1->value, 2);
        $this->pressure_calibration['P1'] = $this->convertTo16Bit($low, $high, false);

        [$low, $high] = $this->readData(BMP280Command::DIG_P2->value, 2);
        $this->pressure_calibration['P2'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_P3->value, 2);
        $this->pressure_calibration['P3'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_P4->value, 2);
        $this->pressure_calibration['P4'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_P5->value, 2);
        $this->pressure_calibration['P5'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_P6->value, 2);
        $this->pressure_calibration['P6'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_P7->value, 2);
        $this->pressure_calibration['P7'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_P8->value, 2);
        $this->pressure_calibration['P8'] = $this->convertTo16Bit($low, $high);
        [$low, $high] = $this->readData(BMP280Command::DIG_P9->value, 2);
        $this->pressure_calibration['P9'] = $this->convertTo16Bit($low, $high);


    }

    public function setConfig(): void
    {
        $this->sendCommand([BMP280Command::CONFIG_REGISTER->value, $this->getConfig()]);
    }

    public function setControl(): void
    {
        $this->sendCommand([BMP280Command::CONTROL_REGISTER->value, $this->getControl()]);
    }

    public function getConfig(): int
    {
        $results = new ByteRegister(0);
        $results = $this->standby_time->setBits($results);
        $results = $this->iir_filter->setBits($results);
        return $results->update(1, false)
            ->update(0, $this->spi_wires != 4)
            ->byte;
    }

    public function getControl(): int
    {
        $results = new ByteRegister(0);
        $results = $this->temp_os->setTemperatureBits($results);
        $results = $this->pressure_os->setPressureBits($results);
        $results = $this->ops_mode->setBits($results);

        return $results->byte;
    }

    /**
     * Convert two bytes to 16-bit integer (little-endian)
     * @param int $low LSB from lower register address
     * @param int $high MSB from higher register address
     * @param bool $signed If true, interpret as signed two's complement
     * @return int The merged 16-bit value
     */
    protected function convertTo16Bit(int $low, int $high, bool $signed = true): int
    {
        $value = ($high << 8) | $low;

        // Convert to signed using two's complement if needed
        if ($signed && ($value & 0x8000)) {
            $value = $value - 0x10000;
        }

        return $value;
    }
}
