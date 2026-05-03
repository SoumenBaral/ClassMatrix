<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Bank = 'bank';
    case Online = 'online';
    case Cheque = 'cheque';
}
