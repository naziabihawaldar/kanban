<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use LaravelAndVueJS\Traits\LaravelPermissionToVueJS;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles,LaravelPermissionToVueJS;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }
    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_user')->withTimestamps();
    }

    public function assignees()
    {
        return $this->belongsToMany(Board::class, 'board_user')->withPivot('assigned_by')->withTimestamps();
    }
}
