<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'task_id',
        'user_id',
    ];

    protected $appends = [
        'url',
        'icon_class',
        'can_delete',
        'uploader_name',
        'uploaded_at_formatted',
        'formatted_capacity',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->link);
    }

    public function getIconClassAttribute()
    {
        $extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        $map = [
            'pdf'  => 'fa-file-pdf',
            'doc'  => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls'  => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'png'  => 'fa-file-image',
            'jpg'  => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'gif'  => 'fa-file-image',
            'zip'  => 'fa-file-archive',
            'rar'  => 'fa-file-archive',
            'txt'  => 'fa-file-alt',
        ];

        return $map[$extension] ?? 'fa-file';
    }

    public function getCanDeleteAttribute()
    {
        return Auth::check() && (Auth::id() === $this->user_id);
    }

    public function getUploaderNameAttribute()
    {
        return $this->user ? $this->user->name : 'Unknown';
    }

    public function getUploadedAtFormattedAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }

    public function getFormattedCapacityAttribute()
    {
        $bytes = $this->capacity;
        if (!$bytes || $bytes == 0) return '0 Bytes';

        $k = 1024;
        $dm = 2;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, $k));

        return round($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            if ($attachment->link && Storage::exists($attachment->link)) {
                Storage::delete($attachment->link);
            }
        });
    }
}
