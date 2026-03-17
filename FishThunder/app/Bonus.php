<?php

namespace VanguardLTE;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'type',
        'subtype',
        'name',
        'total_step',
        'available_step',
        'current_step',
        'step_size',
        'step_progress',
        'last_date',
        'week_index',
        'is_collected'
    ];
}
