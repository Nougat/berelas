<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'shelf',
        'manufacturer',
        'model',
        'cpu',
        'ram',
        'ssd',
        'gpu',
        'amount',
        'condition',
        'specials',
        'layout',
        'price',
        'comment'
    ];

    protected $casts = [
        'ram' => 'integer',
        'ssd' => 'integer',
        'amount' => 'integer',
        'price' => 'integer',
    ];


    public function appleItem(): HasOne
    {
        return $this->hasOne(AppleItem::class);
    }

}
