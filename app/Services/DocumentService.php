<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentService
{
    public function upload(User $user, UploadedFile $file): Document
    {
        $siteId = $user->site_id;
        $directory = "documents/{$siteId}/{$user->id}";

        $path = $file->store($directory, 'local');

        return Document::create([
            'site_id' => $siteId,
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function delete(Document $document): void
    {
        Storage::disk('local')->delete($document->file_path);
        $document->delete();
    }

    public function download(Document $document): StreamedResponse
    {
        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name
        );
    }
}
