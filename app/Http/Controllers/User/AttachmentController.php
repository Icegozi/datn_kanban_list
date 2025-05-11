<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Attachment; // Đảm bảo import đúng Model
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
        $request->validate([
            'attachments'   => 'required|array',
            'attachments.*' => 'file|max:10240', // max 10MB per file, ví dụ: mimes:jpg,png,pdf,doc,docx,xls,xlsx
        ]);

        $uploadedAttachmentsData = [];
        $successMessages = [];
        $errorMessages = [];

        try {
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    try {
                        $originalName = $file->getClientOriginalName();
                        $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('attachments/task_' . $task->id, $filename, 'public'); // Lưu vào disk 'public'

                        if (!$path) {
                            $errorMessages[] = "Không thể lưu file: {$originalName}.";
                            Log::error("Attachment store failed for file: {$originalName} on task {$task->id}. Path not returned.");
                            continue;
                        }

                        // >>> PHẦN QUAN TRỌNG CẦN KIỂM TRA <<<
                        $attachment = Attachment::create([
                            'file_name'  => $originalName,         // PHẢI CÓ
                            'file_path'  => $path,                 // PHẢI CÓ
                            'file_size'  => $file->getSize(),       // PHẢI CÓ
                            'mime_type'  => $file->getMimeType(),   // NÊN CÓ
                            'task_id'    => $task->id,
                            'user_id'    => Auth::id(),
                        ]);
                        // >>> KẾT THÚC PHẦN QUAN TRỌNG <<<

                        // Để trả về client, có thể thêm URL truy cập file và các thông tin khác từ accessor
                        $attachment->url = $attachment->url; // Kích hoạt accessor
                        $attachment->uploaded_at_formatted = $attachment->uploaded_at_formatted; // Kích hoạt accessor
                        // ... các accessor khác nếu cần

                        $uploadedAttachmentsData[] = $attachment;

                        // Load relationship user để có uploader_name nếu cần ngay
                        // $attachment->load('user'); // Nếu bạn muốn trả về uploader_name ngay

                        if ($task->taskHistories()) { // Kiểm tra trước khi tạo
                             $task->taskHistories()->create([
                                'user_id' => Auth::id(),
                                'action'  => 'attachment_added',
                                'note'    => "Đã thêm đính kèm: {$originalName}",
                            ]);
                        } else {
                            Log::warning("Task ID {$task->id}: task_histories is not available to record attachment addition.");
                        }

                        $successMessages[] = "File '{$originalName}' đã được tải lên.";

                    } catch (Exception $e) {
                        Log::error("Attachment upload failed for file: {$originalName} on task {$task->id}. Error: " . $e->getMessage(), ['exception' => $e]);
                        $errorMessages[] = "Lỗi khi tải lên file '{$originalName}'.";
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có tệp nào được gửi lên.'
                ], 400);
            }

            if (!empty($uploadedAttachmentsData)) {
                return response()->json([
                    'success'     => true,
                    'message'     => implode("\n", $successMessages) . (!empty($errorMessages) ? "\nLỗi: " . implode("\n", $errorMessages) : ''),
                    'attachments' => $uploadedAttachmentsData
                ]);
            } else {
                 return response()->json([
                    'success' => false,
                    'message' => 'Không có tệp nào được tải lên thành công. ' . implode("\n", $errorMessages)
                ], 500);
            }

        } catch (Exception $e) {
            Log::error("General attachment store error for task {$task->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi chung khi xử lý tải file.'
            ], 500);
        }
    }

    // ... các phương thức khác (index, destroy) cũng cần được kiểm tra xem có bị revert về phiên bản cũ không ...

    public function index(Task $task)
    {
        try {
            // Đảm bảo load đúng và sử dụng accessor nếu cần
            $attachments = $task->attachments()->latest()->get()->map(function ($attachment) {
                // Các accessor sẽ tự động được thêm vào nếu có trong $appends của Model
                // $attachment->url = $attachment->url; // Ví dụ, để đảm bảo accessor được gọi
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
        // Kiểm tra quyền sử dụng accessor can_delete
        if (!$attachment->can_delete) { // Giả sử accessor này hoạt động đúng
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xoá đính kèm này.'
            ], 403);
        }

        try {
            $originalName = $attachment->file_name;
            $task = $attachment->task; // Lấy task trước khi attachment bị xóa

            // Việc xóa file vật lý đã được xử lý trong Model event 'deleting'

            $attachment->delete();

            if ($task && $task->taskHistories()) { // Kiểm tra task và taskHistories
                $task->taskHistories()->create([
                    'user_id' => Auth::id(),
                    'action'  => 'attachment_deleted',
                    'note'    => "Đã xoá đính kèm: {$originalName}",
                ]);
            } else {
                 Log::warning("Task or task_histories not available to record attachment deletion for attachment ID {$attachment->id}.");
            }


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

     public function download(Attachment $attachment)
    {
        try {
            if (!Storage::disk('public')->exists($attachment->file_path)) {
                Log::error("File not found for download: Attachment ID {$attachment->id}, Path: {$attachment->file_path}");
                return response()->json(['success' => false, 'message' => 'Không tìm thấy tệp tin.'], 404);
            }

            // Trả về response để trình duyệt tải file xuống
            // Tham số thứ hai là tên file mà người dùng sẽ thấy khi tải về (nên dùng file_name gốc)
            // Tham số thứ ba là mảng các headers (tùy chọn)
            return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);

        } catch (Exception $e) {
            Log::error("Download attachment failed for attachment {$attachment->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tải tệp đính kèm.'
            ], 500);
        }
    }
}