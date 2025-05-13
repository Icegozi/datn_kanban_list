<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardPermission extends Pivot
{
    protected $table = 'board_permissions';
    public $incrementing = true;

    protected $fillable = ['board_id', 'permission_user_id'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function permissionUser(): BelongsTo
    {
        return $this->belongsTo(PermissionUser::class, 'permission_user_id');
    }
}