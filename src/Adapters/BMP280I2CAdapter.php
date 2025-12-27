<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Adapters;

use ScrapyardIO\Sensors\Enums\SensorType;
use ScrapyardIO\Support\Attributes\Sensor;
use ScrapyardIO\Sensors\Environmental\BMP280\Enums\BMP280Command;
use ScrapyardIO\Sensors\Environmental\BMP280\Concerns\BMP280I2CChip;
use ScrapyardIO\Sensors\Environmental\BMP280\Enums\BMP280I2CAddress;
use ScrapyardIO\Sensors\Environmental\BMP280\Exceptions\BMP280Exception;
use ScrapyardIO\Sensors\Environmental\BMP280\Concerns\BMP280BootSequence;
use ScrapyardIO\Sensors\Environmental\Adapters\TempPressureSensorAdapter;

#[Sensor('BMP280', BMP280I2CAddress::SDO_GROUNDED->value, SensorType::ENVIRONMENTAL)]
class BMP280I2CAdapter extends TempPressureSensorAdapter
{
    use BMP280I2CChip;
    use BMP280BootSequence;

    public function bus(int $bus):static
    {
        $this->i2c_bmp280_bus($bus);
        return $this;
    }

    public function address(BMP280I2CAddress $address):static
    {
        $this->i2c_bmp280_address($address->value);
        return $this;
    }

    protected int $t_fine = 0;

    protected function rawTemp(): int
    {
        [$low, $mid, $high] = $this->readData(BMP280Command::TEMPERATURE_DATA->value, 3);
        return (($low << 16) | ($mid << 8) | $high) >> 4;
    }

    protected function rawPressure(): int
    {
        [$low, $mid, $high] = $this->readData(BMP280Command::PRESSURE_DATA->value, 3);
        return (($low << 16) | ($mid << 8) | $high) >> 4;
    }

    /**
     * Returns pressure reading in Pascals (Pa)
     * Note: Must call readTemp() first to set t_fine
     * @return float Pressure in Pascals
     */
    public function getPressure(): float
    {
        // Must read temperature first to calculate t_fine
        if ($this->t_fine === 0) {
            $this->getTemp();
        }

        $adc_pressure = $this->rawPressure();

        // Get pressure calibration coefficients
        $p1 = $this->pressure_calibration['P1'];
        $p2 = $this->pressure_calibration['P2'];
        $p3 = $this->pressure_calibration['P3'];
        $p4 = $this->pressure_calibration['P4'];
        $p5 = $this->pressure_calibration['P5'];
        $p6 = $this->pressure_calibration['P6'];
        $p7 = $this->pressure_calibration['P7'];
        $p8 = $this->pressure_calibration['P8'];
        $p9 = $this->pressure_calibration['P9'];

        // Apply BMP280 64-bit pressure compensation formula
        $var1 = intval($this->t_fine) - 128000;
        $var2 = $var1 * $var1 * intval($p6);
        $var2 = $var2 + (($var1 * intval($p5)) << 17);
        $var2 = $var2 + (intval($p4) << 35);
        $var1 = (($var1 * $var1 * intval($p3)) >> 8) + (($var1 * intval($p2)) << 12);
        $var1 = ((((1 << 47) + $var1)) * intval($p1)) >> 33;

        if ($var1 == 0) {
            return 0.0; // Avoid division by zero
        }

        $pressure = 1048576 - $adc_pressure;
        $pressure = ((($pressure << 31) - $var2) * 3125) / $var1;
        $var1 = (intval($p9) * ($pressure >> 13) * ($pressure >> 13)) >> 25;
        $var2 = (intval($p8) * $pressure) >> 19;

        $pressure = (($pressure + $var1 + $var2) >> 8) + (intval($p7) << 4);

        return floatval($pressure) / 256.0;
    }

    /**
     * Returns the temp reading in Celsius
     * @return float
     */
    public function getTemp(): float
    {
        $adc_temp = $this->rawTemp();

        $t1 = $this->temp_calibration['T1'];
        $t2 = $this->temp_calibration['T2'];
        $t3 = $this->temp_calibration['T3'];

        $linear_term = (((intval($adc_temp >> 3) - (intval($t1) << 1))) * intval($t2)) >> 11;
        $quadratic_term = ((((intval($adc_temp >> 4) - intval($t1)) *
                           (intval($adc_temp >> 4) - intval($t1))) >> 12) *
                           intval($t3)) >> 14;

        $this->t_fine = $linear_term + $quadratic_term;

        return (($this->t_fine * 5 + 128) >> 8) / 100.0;
    }


    /**
     * @return $this
     * @throws BMP280Exception
     */
    public function boot(): static
    {
        $this->bmp280_i2c();

        $this->readDeviceId();
        usleep(9000);
        $this->tempCoefficients();
        $this->pressureCoefficients();

        $this->setConfig();
        $this->setControl();
        $this->wait(100);  // 100ms stabilization delay

        return $this;
    }
}
