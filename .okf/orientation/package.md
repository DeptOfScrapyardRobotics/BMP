---
type: Module
title: Package (0.7)
description: dept-of-scrapyard-robotics/bmp Composer identity, namespace, and discovery.
resource: composer.json
tags: [orientation, package, 0.7, bmp, bmp280]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T20:05:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: composer
    resource: composer.json
    title: Package composer.json
  - id: provider
    resource: src/BMPServiceProvider.php
    title: BMPServiceProvider
  - id: gitattributes
    resource: .gitattributes
    title: Dist export-ignore
---

# Identity

| Field | Value |
|-------|-------|
| Composer | `dept-of-scrapyard-robotics/bmp` **0.7.0** |
| PHP | `^8.4\|^8.5\|^8.6` |
| Namespace | `DeptOfScrapyardRobotics\Sensors\BMP\` → `src/` |
| Provider | `DeptOfScrapyardRobotics\Sensors\BMP\BMPServiceProvider` (package root) |
| Catalog slug | `bmp280` |
| Branch alias | `dev-master` → `0.7.x-dev` |

# Requires

| Package | Constraint |
|---------|------------|
| `fabricate/nuts-and-bolts` | `^0.7.0` |
| `gpio/circuits` | `^0.7.0` |
| `gpio/contracts` | `^0.7.0` |
| `gpio/digital` | `^0.7.0` |
| `gpio/i2c` | `^0.7.0` |
| `gpio/spi` | `^0.7.0` |
| `waveforms/contracts` | `^0.7.0` |

**No** `scrapyard-io/tubes` — sensor package, not a display driver.[^composer]

Suggested (optional): `microscrap/i2c`, `microscrap/spi`, `microscrap/mpsse` at `^0.7.0`.[^composer]

# Discovery

`extra.scrapyard-io.providers` lists `BMPServiceProvider`. That provider registers the `bmp280` catalog IC on `boot()`.[^provider]

# Dist

`.okf/` and `AGENTS.md` are `export-ignore` — Composer dist tarballs omit them.[^gitattributes]

# Related

* [BMP280 IC](../core/bmp280.md)
* [Circuits integration](../core/circuits.md)

[^composer]: Package composer.json
[^provider]: BMPServiceProvider
[^gitattributes]: Dist export-ignore
