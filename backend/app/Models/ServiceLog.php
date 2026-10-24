<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'motor',
        'serviced_at',
        'odometer_km',
        'service_type',
        'cost',
        'workshop',
        'notes',
    ];

    protected $hidden = ['user_id'];

    protected function casts(): array
    {
        return [
            'serviced_at' => 'date:Y-m-d',
            'odometer_km' => 'integer',
            'cost' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
