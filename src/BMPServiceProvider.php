<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP;

use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\BMP280;
use Fabricate\NutsAndBolts\ServiceProvider;
use GeneralPurposeIO\Core\MagicAliases\Circuit;

class BMPServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Circuit::addCircuit('bmp280', BMP280::class);
    }
}
