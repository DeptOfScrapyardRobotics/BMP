---
type: Trap
title: Fabricate leftovers
description: BMP 0.7 uses GeneralPurposeIO Circuits + waveforms MeasuresTemperature / MeasuresBarometricPressure — not Fabricate Circuits or sensor contracts.
tags: [traps, fabricate, circuits, sensors, waveforms, bmp280]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T20:05:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: bmp280
    resource: src/BMP280/BMP280.php
    title: BMP280 imports
  - id: internal-api
    resource: src/BMP280/Concerns/BMP280InternalAPI.php
    title: BootScaffolding + Waveforms units
  - id: provider
    resource: src/BMPServiceProvider.php
    title: Circuit MagicAlias import
  - id: exception
    resource: src/BMPException.php
    title: CircuitException import
---

# Trap

Do **not** import or revive:

- `Fabricate\Contracts\Circuits\*`
- `Fabricate\Circuits\*`
- `Fabricate\Contracts\Sensors\*` (Thermometer / Barometer / unit enums)
- `Fabricate\Contracts\NutsAndBolts\BootSequence` / `BootScaffolding` (use GeneralPurposeIO Circuits contracts)

# Use instead

| Concern | Correct FQCN |
|---------|----------------|
| Taxonomy base | `GeneralPurposeIO\Circuits\Types\SensorIC` |
| Attributes / BootSequence / BootScaffolding | `GeneralPurposeIO\Contracts\Circuits\Attributes\*`, `BootSequence`, `BootScaffolding` |
| Circuit alias | `GeneralPurposeIO\Core\MagicAliases\Circuit` |
| Environment qualify | `Waveforms\Contracts\Environment\MeasuresTemperature`, `MeasuresBarometricPressure` |
| Units | `Waveforms\Contracts\Sensors\Enums\{TemperatureUnit,PressureUnit}` |
| Exceptions | `BMPException` extending `GeneralPurposeIO\Contracts\Circuits\CircuitException` |
| Bit helpers | `Fabricate\NutsAndBolts\Concerns\Splices16Bits` (Nab — OK on transport) |

IC verbs are `temperature()` / `pressure()` (distance pattern). Deprecated `measureTemp` / `measurePressure` remain for smoke callers.[^bmp280][^internal-api]

This package depends on `scrapyard-io/gpio-framework` + `scrapyard-io/waveforms` (not tubes).[^provider][^exception]

# Related

* [BMP280 IC](../core/bmp280.md)
* [Circuits integration](../core/circuits.md)

[^bmp280]: BMP280 imports
[^internal-api]: BootScaffolding + Waveforms units
[^provider]: Circuit MagicAlias import
[^exception]: CircuitException import
