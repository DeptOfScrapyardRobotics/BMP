<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP;

use DeptOfScrapyardRobotics\Sensors\BMP\BMP280\BMP280;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;
use Fabricate\NutsAndBolts\ServiceProvider;

class BMPServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Circuit::addCircuit('bmp280', BMP280::class);
    }
}
