<?php

namespace App\Interfaces;

interface PaymentStrategyInterface
{
    public function pay(array $data);
}
