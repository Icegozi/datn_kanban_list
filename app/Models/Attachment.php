<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class Attachment extends Model
{
    use HasFactory;

    use HasFactory;
    protected $fillable = ['name', 'link', 'file_type', 'size', 'task_id', 'user_id'];
    protected $appends = ['url', 'formatted_size', 'icon_class', 'can_delete', 'uploader_name', 'uploaded_at_formatted'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->link);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        if ($bytes == 0) return '0 Bytes';
        $k = 1024;
        $dm = 2;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, $k));
        return parseFloat(($bytes / pow($k, $i))) . toFixed($dm) . ' ' . $sizes[$i];
    }

    public function getIconClassAttribute()
    {
        $ext = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        $map = [
            'pdf' => 'fa-file-pdf text-danger',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls' => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'ppt' => 'fa-file-powerpoint text-warning',
            'pptx' => 'fa-file-powerpoint text-warning',
            'jpg' => 'fa-file-image text-info',
            'jpeg' => 'fa-file-image text-info',
            'png' => 'fa-file-image text-info',
            'gif' => 'fa-file-image text-info',
            'zip' => 'fa-file-archive text-secondary',
            'rar' => 'fa-file-archive text-secondary',
            'txt' => 'fa-file-alt text-muted',
        ];
        return $map[$ext] ?? 'fa-file text-dark';
    }

    public function getCanDeleteAttribute()
    {
        return Auth::check() && (Auth::id() === $this->user_id);
    }
    public function getUploaderNameAttribute()
    {
        return $this->user ? $this->user->name : 'không xác định';
    }
    public function getUploadedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : 'không xác định';
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($attachment) {
            if (Storage::disk('public')->exists($attachment->link)) {
                Storage::disk('public')->delete($attachment->link);
            }
        });
    }
}
