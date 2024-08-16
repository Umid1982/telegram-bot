<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $table = 'cars';

    protected $fillable = [
        'id',
        'full_name',
        'model',
        'make',
        'alt_name',
        'model_cyrillic_name',
        'make_cyrillic_name',
    ];
}
