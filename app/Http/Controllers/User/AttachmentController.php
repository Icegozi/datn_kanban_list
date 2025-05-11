<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class AttachmentController extends Controller
{
    public function store(Request $request, Task $task) {
        $request->validate(['file' => 'required|file|max:10240']); 

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('attachments/task_' . $task->id, $filename, 'public');

            if (!$path) return response()->json(['success' => false, 'message' => 'Không thể lưu file.'], 500);

            $attachment = $task->attachments()->create([
                'user_id' => Auth::id(), 'name' => $originalName,
                'link' => $path, 'file_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
            $attachment->load('user'); 

            $task->taskHistories()->create([
                'user_id' => Auth::id(), 'action' => 'attachment_added',
                'note' => "Đã thêm đính kèm: {$originalName}",
            ]);
            return response()->json(['success' => true, 'message' => 'File đã được tải lên.', 'attachment' => $attachment]);
        } catch (\Exception $e) { /* log and error response */ }
    }

    public function destroy(Attachment $attachment) {
        if (!$attachment->can_delete) { 
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa đính kèm này.'], 403);
        }
        try {
            $name = $attachment->name;
            $attachment->delete(); 
            $attachment->task->taskHistories()->create([
                'user_id' => Auth::id(), 'action' => 'attachment_deleted',
                'note' => "Đã xóa đính kèm: {$name}",
            ]);
            return response()->json(['success' => true, 'message' => 'Đính kèm đã được xóa.']);
        } catch (\Exception $e) { /* log and error response */ }
    }
}
