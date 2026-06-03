# Dept of Scrapyard Robotics Barometric Pressure Sensor Library

<p>Contains implementations for:</p>
<ul>
<li>BMP280</li>
</ul>

## Prerequisites
<ul>
<li>Single Board Computer with an exposed GPIO pinout.</li>
or
<li>Single Board Computer with exposed I2C panel.</li>
or
<li>Any Linux or MacOS PC with USB and a compatible USB-to-Serial device with I2C capability.</li>
<li>PHP 8.3+</li>
<li>Posi Extension v0.4.0+</li>
or 
<li>the FTDI Extension v0.4.0+</li>
<li>ScrapyardIO Framework v0.4.0+</li>
</ul>

## Installation

Require with Composer
```shell
composer require dept-of-scrapyard-robotics/bmp
```

## Preconfiguration

If you want to autoload sensor configuration details for quick fluent bootstrapping
within the ScrapyardIO framework, add this snippet to your project's config/scrapyard-io.php
file in the 'boards' key

### I2C
```php

use DeptOfScrapyardRobotics\Sensors\BMP\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress

return [
    'boards' => [
        // For Native Configurations 
        'bmp280' => [
            'class_name' => BMP280::class,
            'connection' => ['driver' => 'native'],
            'startup' => [
                'i2c' => [
                    'chip_device' => 1,
                    'slave_address' => BMP280I2CAddress::SDO_GROUNDED->value,
                ],
            ],
        ],
        // For USB Configurations
        'adxl345' => [
            'class_name' => BMP280::class,
            'connection' => ['driver' => 'usb'],
            'startup' => [
                'i2c' => [
                    'chip_device' => 'ft232h',
                    'slave_address' => BMP280I2CAddress::SDO_GROUNDED->value,
                ],
            ],
        ],        
    ]
];

```

## Basic Usage

### Native (POSIX) I2C driver. (Single Board Computers)
```php
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress

$native_i2c_sensor = BMP280::connection('native')
    ->i2c(1, BMP280I2CAddress::SDO_GROUNDED->value)
    ->create()
    
$temp_c = $native_i2c_sensor->temperature();
$rh = $native_i2c_sensor->pressure();

```

### Native (POSIX) SPI driver. (Single Board Computers)
```php
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress

$native_spi_sensor = BMP280::connection('native')
    ->spi(0, 0)
    ->create()
    
$temp_c = $native_spi_sensor->temperature();
$rh = $native_spi_sensor->pressure();

```

### USB (MPSSE) driver using I2C. (Linux and MacOS)
```php
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress

$usb_i2c_sensor = BMP280::connection('usb')
    ->i2c('ft232h', BMP280I2CAddress::SDO_GROUNDED->value)
    ->create()
    
$temp_c = $usb_i2c_sensor->temperature();
$rh = $usb_i2c_sensor->pressure();
```

### USB (MPSSE) driver using SPI. (Linux and MacOS)
```php
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress

$usb_i2c_sensor = BMP280::connection('usb')
    ->spi('ft232h', 0)
    ->create()
    
$temp_c = $usb_i2c_sensor->temperature();
$rh = $usb_i2c_sensor->pressure();
```

## Advanced Usage
You can tune measurement behavior at runtime using BMP280's configuration properties:
* Mode controls whether reads are forced or normal.
* Overscan controls precision and conversion cost for temperature/pressure.
* IIR filter controls smoothing.
* Standby period controls inactive time in normal mode.

```php
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280IIRFilter;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280OpMode;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280Overscan;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280StandbyTCS;

$sensor = BMP280::connection('native')
    ->i2c(1, BMP280I2CAddress::SDO_GROUNDED->value)
    ->create();

$sensor->mode = BMP280OpMode::NORMAL;
$sensor->overscan_temperature = BMP280Overscan::OVERSCAN_X2;
$sensor->overscan_pressure = BMP280Overscan::OVERSCAN_X16;
$sensor->iir_filter = BMP280IIRFilter::FILTER_X4;
$sensor->standby_period = BMP280StandbyTCS::STANDBY_TC_250->value;

$typical_ms = $sensor->measurement_time_typical;
$max_ms = $sensor->measurement_time_max;
```

## Calibration
The BMP280 exposes altitude as a computed value based on local pressure and your configured sea-level pressure.

If you know the local sea-level pressure from a trusted source:
```php
$sensor->sea_level_pressure = 1020.00;
$altitude_m = $sensor->altitude;
```

If you know your installation altitude and want to calibrate from it:
```php
$sensor->altitude = 15.0;
$sea_level_pressure = $sensor->sea_level_pressure;
```

Notes:
* `pressure` is reported in hPa.
* `altitude` is reported in meters.
* Readings can vary slightly because each property access may trigger a fresh conversion.

## Alternative Usage

### Using Through the Sensor Library (as a Temperature Sensor)
```php
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280;
use RealityInterface\Sensors\Applied\Environmental\TemperatureSensor;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress;

$bmp280 = BMP280::connection('usb')
    ->i2c('ft232h', BMP280I2CAddress::SDO_GROUNDED->value)
    ->create()
    
$sensor = TemperatureSensor::as($bmp280);

$temp_c = $sensor->temperature();

$event = $sensor->measure();

```

### Using Through the Sensor Library (as a Barometric Pressure Sensor)
```php
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280;
use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\Enums\BMP280I2CAddress
use RealityInterface\Sensors\Applied\Environmental\BarometricPressureSensor;

$bmp280 = BMP280::connection('usb')
    ->i2c('ft232h', BMP280I2CAddress::SDO_GROUNDED->value)
    ->create()

$sensor = BarometricPressureSensor::as($bmp280);

$temp_c = $sensor->pressure();

$event = $sensor->measure();

```

### Using Through the Sensor Framework (with an autoloaded config) (as a Temperature Sensor)
```php

use RealityInterface\Sensors\Applied\Environmental\TemperatureSensor;

$sensor = TemperatureSensor::using('bmp280');

$temp_c = $sensor->temperature();

$event = $sensor->measure();

```

### Using Through the Sensor Framework (with an autoloaded config) (as a Barometric Pressure Sensor)
```php

use RealityInterface\Sensors\Applied\Environmental\BarometricPressureSensor;

$sensor = BarometricPressureSensor::using('bmp280');

$temp_c = $sensor->pressure();

$event = $sensor->measure();

```

## Sensor API
The getters and setters in this API interface with the device directly (register reads/writes),
so you can use property access while still working against the sensor itself.

Readable Properties (Getters)
-----------------------------
* `$sensor->chip_id`  
  Reads and returns the BMP280 chip ID.

* `$sensor->status`  
  Reads and returns the status register.

* `$sensor->_ctrl_meas`  
  Returns the computed control-measure register value from active settings.

* `$sensor->_config`  
  Returns the computed config register value from active settings.

* `$sensor->config`  
  Reads and returns the config register value from hardware.

* `$sensor->temperature`  
  Reads and returns compensated temperature in Celsius.

* `$sensor->pressure`  
  Reads and returns compensated pressure in hPa. Returns `null` if pressure overscan is disabled.

* `$sensor->altitude`  
  Returns computed altitude in meters using current `sea_level_pressure`.

* `$sensor->sea_level_pressure`  
  Returns the configured sea-level pressure in hPa.

* `$sensor->mode`  
  Returns current operating mode value.

* `$sensor->overscan_temperature`  
  Returns current temperature overscan value.

* `$sensor->overscan_pressure`  
  Returns current pressure overscan value.

* `$sensor->iir_filter` (`BMP280IIRFilter`)  
  Returns current IIR filter setting.

* `$sensor->standby_period`  
  Returns current standby period value.

* `$sensor->measurement_time_typical`  
  Returns typical conversion time in milliseconds based on active overscan settings.

* `$sensor->measurement_time_max`  
  Returns maximum conversion time in milliseconds based on active overscan settings.

Writable Properties (Setters)
-----------------------------
* `$sensor->altitude = 7.0;`  
  Calibrates sea-level pressure from current pressure and the provided altitude in meters.

* `$sensor->sea_level_pressure = 1019.40;`  
  Sets sea-level pressure (hPa) used by altitude calculations.

* `$sensor->mode = BMP280OpMode::NORMAL;`  
  Sets operation mode (`SLEEP`, `FORCE`, `NORMAL`).

* `$sensor->overscan_temperature = BMP280Overscan::OVERSCAN_X2;`  
  Sets temperature oversampling.

* `$sensor->overscan_pressure = BMP280Overscan::OVERSCAN_X16;`  
  Sets pressure oversampling.

* `$sensor->iir_filter = BMP280IIRFilter::FILTER_X4;`  
  Sets IIR filter strength.

* `$sensor->standby_period = BMP280StandbyTCS::STANDBY_TC_250->value;`  
  Sets standby period in normal mode.
