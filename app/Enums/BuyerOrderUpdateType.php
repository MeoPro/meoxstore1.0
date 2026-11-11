<?php

namespace App\Enums;

enum BuyerOrderUpdateType
{
    case PaymentRequired;
    case PaymentSubmitted;
    case PaymentConfirmed;
    case DeliveryReady;
}
