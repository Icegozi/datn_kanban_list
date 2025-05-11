<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon; // Đảm bảo import Carbon

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name', // Đúng
        'file_path', // Đúng
        'file_size', // Đúng
        'mime_type', // Đúng
        'task_id',
        'user_id',
    ];

    // $appends giúp tự động thêm các giá trị này khi model được chuyển thành array hoặc JSON
    protected $appends = [
        'url',
        'icon_class',
        'can_delete',
        'uploader_name',
        'uploaded_at_formatted',
        'formatted_capacity', // Đã đổi tên thành formatted_file_size hoặc tương tự nếu muốn
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
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    // Accessor cho icon class
    public function getIconClassAttribute()
    {
        // Sử dụng file_name đã được migrate
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        $map = [
            'pdf'  => 'fa-file-pdf text-danger', 
            'doc'  => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls'  => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'ppt'  => 'fa-file-powerpoint text-warning',
            'pptx' => 'fa-file-powerpoint text-warning',
            'png'  => 'fa-file-image text-info',
            'jpg'  => 'fa-file-image text-info',
            'jpeg' => 'fa-file-image text-info',
            'gif'  => 'fa-file-image text-info',
            'zip'  => 'fa-file-archive text-secondary',
            'rar'  => 'fa-file-archive text-secondary',
            'txt'  => 'fa-file-alt text-muted',
        ];
        return $map[$extension] ?? 'fa-file text-muted'; 
    }

    // Accessor kiểm tra quyền xóa
    public function getCanDeleteAttribute()
    {
        // Có thể thêm điều kiện khác, ví dụ: admin cũng có thể xóa
        return Auth::check() && (Auth::id() === $this->user_id /* || Auth::user()->isAdmin() */ );
    }

    // Accessor cho tên người upload
    public function getUploaderNameAttribute()
    {
        return $this->user ? $this->user->name : 'Không xác định';
    }

    // Accessor cho ngày upload đã định dạng
    public function getUploadedAtFormattedAttribute()
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y H:i') : null;
    }

    // Accessor cho kích thước file đã định dạng
    public function getFormattedCapacityAttribute() // Xem xét đổi tên thành getFormattedFileSizeAttribute
    {
        $bytes = $this->file_size; // Sử dụng file_size đã được migrate
        if (is_null($bytes)) return ''; // Trả về chuỗi rỗng nếu null
        if ($bytes == 0) return '0 Bytes';

        $k = 1024;
        $dm = 1; // Số chữ số thập phân
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, $k));

        return round($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
    }

    // Sự kiện model để tự động xóa file vật lý khi record bị xóa
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            // Sử dụng file_path và disk 'public'
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        });
    }
}