<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
{
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->user()->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'preferred_currency' => ['nullable', 'string', 'max:10'],
            'notification_email' => ['nullable', 'boolean'],
            'notification_browser' => ['nullable', 'boolean'],
            'theme' => ['nullable', 'in:light,dark'],
            'photo' => ['nullable', 'image', 'max:10048'],
        ];
}
}
