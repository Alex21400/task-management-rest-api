<?php

namespace App\Models;

use App\Builders\TaskBuilder;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'is_done',
        'creator_id',
        'project_id',
        'scheduled_at',
        'due_at'
    ];

    protected $casts = [
        'is_done' => 'boolean'
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeScheduledBetween(Builder $query, string $fromDate, string $toDate)
    {
        // $query->where('scheduled_at', '>=', $fromDate)->where('scheduled_at', '<=', $toDate);
        return $query->whereBetween('scheduled_at', [$fromDate, $toDate]);
    }

    public function scopeDueBetween($query, string $fromDate, string $toDate)
    {
        // $query->where('due_at', '>=', $fromDate)->where('due_at', '<=', $toDate);
        return $query->whereBetween('due_at', [$fromDate, $toDate]);
    }

    public function scopeDue($query, string $filter)
    {
        if($filter === 'today') {
            return $query->where('due_at', '=', Carbon::today()->toDateString());
        } else if($filter === 'past') {
            return $query->where('due_at', '<', Carbon::today()->toDateString());
        }

        return $query;
    }

    protected static function booted(): void
    {
        static::addGlobalScope('member', function(Builder $builder) {
            $builder->where('creator_id', Auth::id())
                    ->orWhereIn('project_id', Auth::user()->memberships->pluck('id'));
        });
    }
}
