<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskPositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task_id' => 'required|integer|exists:tasks,id',
            'new_column_id' => 'required|integer|exists:columns,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:tasks,id',
        ];
    }

    public function messages(): array
    {
        return [
            'task_id.required' => 'Thiếu task ID.',
            'new_column_id.required' => 'Thiếu column ID.',
            'order.required' => 'Danh sách sắp xếp không hợp lệ.',
            'order.*.exists' => 'Một trong các task không tồn tại.',
        ];
    }
}
