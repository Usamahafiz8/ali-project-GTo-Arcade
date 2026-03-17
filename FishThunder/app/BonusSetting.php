<?php

namespace VanguardLTE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'subtype',
        'name',
        'amount',
        'values'
    ];
}
