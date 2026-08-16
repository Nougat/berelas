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
        'generation',
    ];

    protected $casts = [
        'release_year' => 'integer',
        'generation' => 'integer',
    ];


    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

}
