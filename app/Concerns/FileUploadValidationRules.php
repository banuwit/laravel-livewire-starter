<?php

namespace App\Concerns;

trait FileUploadValidationRules
{
    protected function imageUploadRules(int $maxKb = 2048): array
    {
        return ['uploadedFile' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . $maxKb]];
    }

    protected function imageUploadMessages(): array
    {
        return [
            'uploadedFile.required' => __('Please select an image first.'),
            'uploadedFile.image' => __('The file must be a valid image (JPG, PNG, WebP, or GIF).'),
            'uploadedFile.mimes' => __('The file must be JPG, PNG, WebP, or GIF.'),
            'uploadedFile.max' => __('The image must not exceed 2MB.'),
        ];
    }

    protected function documentUploadRules(int $maxKb = 5120): array
    {
        return ['uploadedFile' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:' . $maxKb]];
    }

    protected function documentUploadMessages(): array
    {
        return [
            'uploadedFile.file' => __('The file must be a valid document.'),
            'uploadedFile.mimes' => __('The file must be PDF, DOC, DOCX, XLS, or XLSX.'),
            'uploadedFile.max' => __('The document must not exceed 5MB.'),
        ];
    }
}
