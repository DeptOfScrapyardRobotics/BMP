# Directory Update Log

## 2026-08-11

* **Fix (draft)**: Composer `require` uses leaf components (`gpio/*`, `waveforms/contracts` or `tubes/contracts`, `fabricate/nuts-and-bolts`) — no `scrapyard-io/gpio-framework` / `scrapyard-io/waveforms` / `scrapyard-io/tubes` kitchen sinks. Amended [package](orientation/package.md).

* **0.7 promotion**: Initial `.okf` for `dept-of-scrapyard-robotics/bmp` 0.7 — package orientation, BMP280 SensorIC (`MeasuresTemperature` + `MeasuresBarometricPressure`), Circuits catalog `bmp280`, Fabricate leftovers trap. Composer now requires `scrapyard-io/gpio-framework` + `scrapyard-io/waveforms` ^0.7; IC verbs `temperature` / `pressure` with Waveforms units.
