<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class CollegeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'arabic_name' => 'required|string|max:255|unique:colleges,arabic_name',
            'english_name' => 'required|string|max:255|unique:colleges,english_name',
            'image' => 'nullable|string|max:255', // Adjust max size as needed
            'description' => 'nullable|string',
            'phone' => 'nullable|string|unique:colleges,phone',
            'email' => 'nullable|email|unique:colleges,email',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
        ];
    }
}
