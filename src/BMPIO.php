<?php

namespace DeptOfScrapyardRobotics\Sensors\BMP;

trait BMPIO
{
    /**
     * @throws BMPException
     */
    protected function spiRead(int $register, int $length): array
    {
        if (! is_null($this->spi)) {
            $register_hex = ($register | 0x80) & 0xFF;
            $rx = $this->spi->transfer([
                $register_hex,
                ...array_fill(0, $length, 0x00),
            ]);

            return array_slice($rx, 1, $length);
        }

        throw BMPException::transportMissingProtocol();
    }

    /**
     * @throws BMPException
     */
    protected function spiWrite(int $register, array $data = []): int
    {
        if (! is_null($this->spi)) {
            return $this->spi->write([
                $register & 0x7F,
                ...$data,
            ]);
        }

        throw BMPException::transportMissingProtocol();
    }

    /**
     * @throws BMPException
     */
    protected function i2cRead(int $register, int $length): array
    {
        if (! is_null($this->i2c)) {
            return $this->i2c->writeRead([$this->getLowByte($register)], $length);
        }

        throw BMPException::transportMissingProtocol();
    }

    /**
     * @throws BMPException
     */
    protected function i2cWrite(int $register, array $data = []): int
    {
        if (! is_null($this->i2c)) {
            $payload = [$this->getLowByte($register), ...$data];

            return $this->i2c->write($payload);
        }

        throw BMPException::transportMissingProtocol();
    }
}
