<?php

namespace App\Enums;

enum PlotStatus: string
{
    case Available = 'available';
    case Held = 'held';
    case Reserved = 'reserved';
    case Booked = 'booked';
    case Sold = 'sold';
    case Blocked = 'blocked';
    case Disputed = 'disputed';
}
