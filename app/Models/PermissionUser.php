<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionUser extends Pivot
{
    protected $table = 'permission_users';
    public $incrementing = true; 

    protected $fillable = ['user_id', 'permission_id'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function boardPermissions()
    {
        return $this->hasMany(BoardPermission::class, 'permission_user_id');
    }
}
