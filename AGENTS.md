# Agent guidelines — dept-of-scrapyard-robotics/bmp

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from Composer dist via `.gitattributes` `export-ignore`).

Before changing this package or advising on BMP architecture:

1. Read [`.okf/index.md`](.okf/index.md) first (progressive disclosure).
2. Open only the linked concepts needed for the task.
3. Prefer `status: stable` concepts; treat `deprecated` as historical only. New/changed concepts stay `status: draft` until a human verifies them.
4. When you learn something durable about **this package**, update the affected `.okf` concept(s) and append `.okf/log.md`.
5. Keep the `.okf` bundle at the **package root** only — do not nest extra `.okf` folders under `src/`.
6. Circuits registry semantics belong in `scrapyard-io/gpio-framework`’s `.okf`. Do not pull tubes/display knowledge into this sensor package.

## Package rules (quick) — 0.7.x

- Composer: `dept-of-scrapyard-robotics/bmp` **0.7.0**. Namespace `DeptOfScrapyardRobotics\Sensors\BMP\`.
- Provider: `BMPServiceProvider` at package root. Catalog slug `bmp280`.
- Requires leaf components (not kitchen-sink frameworks): `fabricate/nuts-and-bolts`, `gpio/circuits`, `gpio/contracts`, `gpio/digital`, `gpio/i2c`, `gpio/spi`, `waveforms/contracts`.
- IC extends `GeneralPurposeIO\Circuits\Types\SensorIC`, implements `BootSequence` + `MeasuresTemperature` + `MeasuresBarometricPressure`.
- Attributes: `#[IntegratedCircuit('I2C', 'SPI')]` + `#[Pinout(I2C…, SPI…)]`.
- Verbs: `temperature(TemperatureUnit)` / `pressure(PressureUnit)`; deprecated `measureTemp` / `measurePressure` aliases OK for smoke.
- Units: Waveforms `TemperatureUnit` / `PressureUnit` — never Fabricate sensor enums.
- Boot uses `BootScaffolding`; bit helpers use Nab `Splices16Bits` on the carrier transport.
- Never import `Fabricate\Contracts\Circuits\*` or `Fabricate\Contracts\Sensors\*`.
