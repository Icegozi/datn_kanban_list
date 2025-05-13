<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Auth;
use Hash;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    public function boardsOwned(): HasMany
    {
        return $this->hasMany(Board::class, 'user_id');
    }

    /**
     * Permissions explicitly granted to this user (global or context-specific entries).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_users');
    }

    /**
     * Get the PermissionUser pivot entries for this user.
     */
    public function permissionUserPivots(): HasMany
    {
        return $this->hasMany(PermissionUser::class); // Assuming PermissionUser model exists
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

    public function hasBoardPermission(Board $board, string $permissionName): bool
    {
        if ($board->user_id === $this->id) {
            return true; 
        }
        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            return false;
        }

        $permissionUser = PermissionUser::where('user_id', $this->id)
            ->where('permission_id', $permission->id)
            ->first();

        if (!$permissionUser) {
            return false;
        }

        $hasBoardPermission = BoardPermission::where('board_id', $board->id)
            ->where('permission_user_id', $permissionUser->id)
            ->exists();

        if ($hasBoardPermission) {
            return true;
        }
        
        return false;
    }



    public function getRoleForBoard(Board $board): ?string
    {
        if ($board->user_id === $this->id) {
            return 'owner';
        }

        $permissionNames = ['board_member_manager', 'board_editor', 'board_viewer'];
        foreach ($permissionNames as $pName) {
            if ($this->hasBoardPermission($board, $pName)) {
                return $pName;
            }
        }
        return null;
    }

    public function getAllAccessibleBoards()
    {
        $owned = $this->boardsOwned()->get();
        $boardIdsWithPermissions = BoardPermission::join('permission_users', 'board_permissions.permission_user_id', '=', 'permission_users.id')
            ->where('permission_users.user_id', $this->id)
            ->distinct()
            ->pluck('board_permissions.board_id');

        $memberOf = Board::whereIn('id', $boardIdsWithPermissions)->get();

        return $owned->merge($memberOf)->unique('id');
    }
}
