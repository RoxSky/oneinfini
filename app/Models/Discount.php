<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'day_type',
        'type',
        'amount',
        'description',
        'start_date',
        'end_date',
    ];
}
