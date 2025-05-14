<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      // ít nhất một chữ thường
                'regex:/[A-Z]/',      // ít nhất một chữ hoa
                'regex:/[0-9]/',      // ít nhất một chữ số (nếu bạn muốn)
                'regex:/[@$!%*#?&]/', // ít nhất một ký tự đặc biệt
                'confirmed'           // phải khớp với password_confirmation
            ],
            'status' => 'required|in:active,inactive,banned',
            'is_admin' => 'nullable|boolean',
        ];
    }
}
