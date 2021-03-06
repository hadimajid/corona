<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $guarded=['id'];
    public function tests(){
        return $this->hasMany(Test::class);
    }
}
