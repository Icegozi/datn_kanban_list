<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Attachment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        // Validate từng file trong mảng 'attachments'
        $request->validate([
            'attachments'   => 'required|array', // Phải là một mảng và bắt buộc
            // Kiểm tra từng file trong mảng, bạn có thể thêm các loại file cho phép (mimes)
            'attachments.*' => 'file|max:10240', // max 10MB per file
        ]);

        $uploadedAttachmentsData = [];
        $successMessages = [];
        $errorMessages = [];

        try {
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    try {
                        $originalName = $file->getClientOriginalName();
                        // Tạo tên file duy nhất, ví dụ:
                        $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                        // Lưu vào thư mục con của task để dễ quản lý
                        $path = $file->storeAs('attachments/task_' . $task->id, $filename, 'public');

                        if (!$path) {
                            $errorMessages[] = "Không thể lưu file: {$originalName}.";
                            continue; // Bỏ qua file này
                        }

                        $attachment = Attachment::create([
                            'file_name'  => $originalName, // Đổi tên cột cho nhất quán (nếu cần)
                            'file_path'  => $path,         // Đổi tên cột cho nhất quán
                            'file_size'  => $file->getSize(),    // Đổi tên cột cho nhất quán
                            'mime_type'  => $file->getMimeType(),// Lưu mime_type
                            'task_id'    => $task->id,
                            'user_id'    => Auth::id(),
                        ]);

                        // Để trả về client, có thể thêm URL truy cập file
                        $attachment->file_url = Storage::url($attachment->file_path);
                        $attachment->created_at_formatted = $attachment->created_at->format('d/m/Y H:i');

                        $uploadedAttachmentsData[] = $attachment;

                        $task->taskHistories()->create([
                            'user_id' => Auth::id(),
                            'action'  => 'attachment_added',
                            'note'    => "Đã thêm đính kèm: {$originalName}",
                        ]);
                        $successMessages[] = "File '{$originalName}' đã được tải lên.";

                    } catch (Exception $e) {
                        Log::error("Attachment upload failed for file: {$originalName} on task {$task->id}. Error: " . $e->getMessage());
                        $errorMessages[] = "Lỗi khi tải lên file '{$originalName}'.";
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có tệp nào được gửi lên.'
                ], 400); // Bad Request
            }

            if (!empty($uploadedAttachmentsData)) {
                return response()->json([
                    'success'   => true,
                    // Gộp các thông báo lại
                    'message'   => implode("\n", $successMessages) . (!empty($errorMessages) ? "\nLỗi: " . implode("\n", $errorMessages) : ''),
                    'attachments' => $uploadedAttachmentsData // Trả về danh sách các attachment đã tải lên
                ]);
            } else {
                 return response()->json([
                    'success' => false,
                    'message' => 'Không có tệp nào được tải lên thành công. ' . implode("\n", $errorMessages)
                ], 500);
            }

        } catch (Exception $e) {
            Log::error("General attachment store error for task {$task->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi chung khi xử lý tải file.'
            ], 500);
        }
    }

    public function index(Task $task)
    {
        try {
            // Lấy attachments, sắp xếp mới nhất lên trước, và thêm URL/định dạng ngày tháng
            $attachments = $task->attachments()->latest()->get()->map(function ($attachment) {
                $attachment->file_url = Storage::url($attachment->file_path); // Đảm bảo file_path đúng
                $attachment->created_at_formatted = $attachment->created_at->format('d/m/Y H:i');
                // Bạn có thể thêm thông tin người upload nếu cần, ví dụ:
                // $attachment->uploader_name = $attachment->user->name; (nếu có relationship 'user')
                return $attachment;
            });

            return response()->json([
                'success' => true,
                'attachments' => $attachments
            ]);
        } catch (Exception $e) {
            Log::error("Get attachments failed for task {$task->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách đính kèm.'
            ], 500);
        }
    }

    public function destroy(Attachment $attachment)
    {
        // Kiểm tra quyền, ví dụ: chỉ người upload hoặc admin mới được xóa
        // if (Auth::id() !== $attachment->user_id && !Auth::user()->isAdmin()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Bạn không có quyền xoá đính kèm này.'
        //     ], 403);
        // }
        // Hoặc sử dụng policy: $this->authorize('delete', $attachment);

        try {
            $originalName = $attachment->file_name; // Giả sử bạn lưu tên gốc vào file_name
            $filePath = $attachment->file_path;     // Giả sử bạn lưu đường dẫn vào file_path
            $task = $attachment->task;

            // Xóa file vật lý khỏi storage
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $attachment->delete(); // Xóa record khỏi DB

            $task->taskHistories()->create([
                'user_id' => Auth::id(),
                'action'  => 'attachment_deleted',
                'note'    => "Đã xoá đính kèm: {$originalName}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đính kèm đã được xoá.'
            ]);
        } catch (Exception $e) {
            Log::error("Attachment delete failed for attachment {$attachment->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xoá đính kèm.'
            ], 500);
        }
    }
}