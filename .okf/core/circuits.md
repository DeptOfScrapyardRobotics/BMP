---
type: Module
title: Circuits integration
description: Catalog registration for bmp280 via BMPServiceProvider.
resource: src/BMPServiceProvider.php
tags: [circuits, catalog, provider, bmp280]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T20:05:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: provider
    resource: src/BMPServiceProvider.php
    title: BMPServiceProvider
---

# Role

This package **owns the BMP280 chip driver** and registers it with gpio-framework Circuits. Registry / fluent / profile **semantics** live in `scrapyard-io/gpio-framework` — open that package’s `.okf` for `CircuitRegistry`, `PendingCircuit`, and `circuit:make-profile` behavior.

# Catalog

On `boot()`:[^provider]

```php
Circuit::addCircuit('bmp280', BMP280::class);
```

Provider lives at package root (`BMPServiceProvider`). No package-local make-profile command or smoke sketch today — use gpio-framework `circuit:make-profile` / `Circuit::ic(…)` / `Circuit::profile(…)` as needed.

```php
Circuit::profile('weather_lab'); // recipe ic => bmp280
```

Example profile recipe shape (I2C):

```php
'weather_lab' => [
    'ic' => 'bmp280',
    'connection' => 'I2C',
    'params' => [
        'driver' => 'posix', // or mpsse, …
        'device' => '/dev/i2c-1',
        'slave' => 0x76,
    ],
    'boot_now' => true,
],
```

SPI profile uses `connection => 'SPI'` with `chip_select` instead of `slave`.

# Related

* [BMP280 IC](bmp280.md)
* [Package (0.7)](../orientation/package.md)

[^provider]: BMPServiceProvider
