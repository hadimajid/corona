<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $guarded=['id'];
    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
