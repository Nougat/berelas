<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppleItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'inventory_item_id',
        'release_year',
        'tag',
    ];

    protected $casts = [
        'release_year' => 'integer',
        'tag' => 'string',
    ];


    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (AppleItem $appleItem) {
            $appleItem->inventoryItem?->delete();
        });
    }

}
