<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'device',
        'received_at',
        'received_by',
        'assigned_to',
        'purchase_date',
        'description',
        'condition',
        'password',
        'power_supply',
        'damage_fixed',
        'customer_notified',
        'cost',
        'status',
        'completed_at',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'purchase_date' => 'date',
            'power_supply' => 'boolean',
            'damage_fixed' => 'boolean',
            'customer_notified' => 'boolean',
            'cost' => 'decimal:2',
            'completed_at' => 'datetime',

            // Verschlüsselt das Passwort in der Datenbank.
            'password' => 'encrypted',
        ];
    }
}