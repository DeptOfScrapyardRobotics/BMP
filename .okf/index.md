---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/bmp Knowledge Bundle

Package knowledge for `dept-of-scrapyard-robotics/bmp` (Bosch BMP280 barometer + thermometer, v0.7.x).
Read this index first; open only the concepts needed for the task.

**Trust rule:** Prefer `status: stable`. Treat `deprecated` as historical only. New agent-written concepts stay `status: draft` until a human verifies them.
**Placement:** Package-root `.okf/` only — never under `src/`.
**Links:** Concept cross-links use paths relative to each file.
**Scope:** This package’s IC surface, Circuits catalog registration, and waveforms Measures* capabilities. Registry semantics live in `scrapyard-io/gpio-framework` — do not duplicate that bundle here. No tubes dependency.
**Dist note:** `.okf/` and root `AGENTS.md` are `export-ignore` in `.gitattributes`.

# Orientation

* [Package (0.7)](orientation/package.md) - Composer identity, namespace, provider, dependencies.

# Core

* [BMP280 IC](core/bmp280.md) - SensorIC class, attributes, I2C/SPI factories, temperature/pressure API.
* [Circuits integration](core/circuits.md) - Catalog slug, profiles.

# Traps

* [Fabricate leftovers](traps/fabricate-leftovers.md) - GeneralPurposeIO Circuits + waveforms Measures*; not Fabricate Circuits/sensor contracts.

# Log

* [Directory update log](log.md)
