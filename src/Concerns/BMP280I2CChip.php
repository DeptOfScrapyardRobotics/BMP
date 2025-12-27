<?php

namespace ScrapyardIO\Sensors\Environmental\BMP280\Concerns;

use ScrapyardIO\Transports\I2CTransport;

trait BMP280I2CChip
{
    protected ?I2CTransport $bmp280_i2c = null;
    protected int $bmp280_i2c_bus = 1;
    protected int $bmp280_i2c_address = 0;
    protected int $max_packet_size = 0;

    protected function i2c_bmp280_bus(?int $bus = null): int
    {
        if($bus)
        {
            $this->bmp280_i2c_bus = $bus;
        }
        return $this->bmp280_i2c_bus;
    }

    protected function i2c_bmp280_address(?int $address = null): int
    {
        if($address)
        {
            $this->bmp280_i2c_address = $address;
        }
        return $this->bmp280_i2c_address;
    }

    protected function bmp280_i2c(): ?I2CTransport
    {
        if(empty($this->bmp280_i2c))
        {
            $this->bmp280_i2c = new I2CTransport(
                $this->i2c_bmp280_address(),
                $this->i2c_bmp280_bus()
            );
        }

        return $this->bmp280_i2c;
    }

    public function readData(int $command, int $num_bytes_to_read): array
    {
        $this->sendCommand([$command]);
        return $this->bmp280_i2c()->read($num_bytes_to_read);
    }

    public function sendCommand(array $bytes): void
    {
        $this->bmp280_i2c()->notify($bytes);
    }
}
