<?php

namespace App\Models;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity;

class Card extends Model
{
    use HasFactory;
    protected $fillable = [
        'content',
        'column_id',
        'position',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'card_user')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }
    
}
