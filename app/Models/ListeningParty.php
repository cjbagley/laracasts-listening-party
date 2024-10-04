<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * App\Models\ListeningParty
 *
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property bool $is_active
 */
class ListeningParty extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function podcast(): HasOneThrough
    {
        return $this->hasOneThrough(Podcast::class, Episode::class, 'id', 'id', 'episode_id', 'podcast_id');
    }
}
