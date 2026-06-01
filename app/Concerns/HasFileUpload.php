<?php

namespace App\Concerns;

use Livewire\WithFileUploads;

trait HasFileUpload
{
    use WithFileUploads;

    // Untyped — Livewire assign TemporaryUploadedFile saat upload
    public $uploadedFile = null;
}
