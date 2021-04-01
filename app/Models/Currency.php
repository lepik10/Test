<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function history()
    {
        return $this->hasMany(CurrencyHistory::class);
    }

    protected static function booted()
    {
        // Добавляем валюту в историю при обновлении
        static::updating(function ($currency) {
            $currency->history()->create([
                'rate' => $currency->getOriginal('rate')
            ]);
        });
    }
}
