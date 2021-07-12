<?php

namespace Lit\Models;

use Ignite\Auth\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read bool $is_admin
 */
class User extends Authenticatable implements CanResetPasswordContract
{
    use Notifiable, HasRoles, CanResetPassword;

    /**
     * Database table name.
     *
     * @var string
     */
    public $table = 'lit_users';

    /**
     * Guard name.
     *
     * @var string
     */
    protected $guard_name = 'lit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'first_name', 'last_name', 'email', 'locale',
    ];

    /**
     * Hidden attributes.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Determines if the user has the admin role.
     *
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->hasRole('admin');
    }

    /**
     * Send password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $link = route('lit.password.reset.show', $token);

        $link .= '?email='.urlencode($this->email);

        $this->notify(new ResetPasswordNotification($link));
    }

    /**
     * Has role admin scope.
     *
     * @param  Builder $query
     * @return Builder $query
     */
    public function scopeAdmin($query)
    {
        $query->role('admin');
    }

    /**
     * Has role user scope.
     *
     * @param  Builder $query
     * @return Builder $query
     */
    public function scopeUser($query)
    {
        return $query->role('user');
    }

    /**
     * Ordered Roles by permission count.
     *
     * @return MorphToMany
     */
    public function ordered_roles()
    {
        return $this->roles()
            ->withCount('permissions')
            ->orderByDesc('permissions_count');
    }
}
