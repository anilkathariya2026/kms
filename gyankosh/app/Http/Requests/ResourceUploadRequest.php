<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (
            auth()->user()->hasRole('staff') || 
            auth()->user()->hasRole('librarian') || 
            auth()->user()->hasRole('admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'resource_type' => 'required|in:ebook,research_paper,journal,thesis,lecture_note,course_material,question_paper,assignment,presentation,article,institutional_document,video,external_link',
            'category_id' => 'required|exists:categories,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'department_id' => 'nullable|exists:departments,id',
            'tags' => 'nullable|string|max:500',
            'file' => 'required_without:external_url|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,mp4|max:102400', // Max 100MB
            'external_url' => 'required_without:file|url',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // Max 5MB
            'publication_date' => 'nullable|date',
            'access_level' => 'required|in:public,private,department,program',
            'download_permission' => 'required|boolean',
            'author' => 'required|string|max:255',
            'contributor' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.mimes' => 'Only PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, JPEG, PNG, and MP4 files are allowed.',
            'file.max' => 'File size cannot exceed 100MB.',
            'thumbnail.max' => 'Thumbnail size cannot exceed 5MB.',
            'external_url.url' => 'Please provide a valid URL.',
        ];
    }
}
