<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Auth;
use Hash;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'status'
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

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(Assignee::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function permisstionUsers(): HasMany
    {
        return $this->hasMany(PermissionUser::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function taskHistorys(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }

    public static function register(array $data)
    {
        return self::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
            'status' => 'active',
        ]);
    }

    // public static function login(array $credentials): bool
    // {
    //     $user = self::where('email', $credentials['email'])->first();

    //     if (!$user || $user->status !== 'active') {
    //         return false;
    //     }

    //     if (Auth::attempt($credentials)) {
    //         session()->regenerate();
    //         return true;
    //     }

    //     return false;
    // }

    public static function login(array $credentials, bool $remember = false): bool
    {
        $user = self::where('email', $credentials['email'])->first();

        if (!$user || $user->status !== 'active') {
            return false;
        }

        if (Auth::attempt($credentials, $remember)) {
            session()->regenerate();
            return true;
        }

        return false;
    }

    public static function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
