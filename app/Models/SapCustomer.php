<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapCustomer extends Model
{
    use HasFactory;

    function addresses() {
        return $this->hasMany(SapCustomerAddress::class);
    }
}
