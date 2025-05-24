<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class ImportFile extends Component
{
    use WithFileUploads;

    public $file;
    public $fileInfo = [];
    public $isLoading = false;

    protected $rules = [
        'file' => 'required|file|max:5120|mimes:pdf,xls,xlsx,doc,docx' // 5MB max
    ];

    public function updatedFile()
    {
        $this->validate();

        if ($this->file) {
            $this->fileInfo = [
                'name' => $this->file->getClientOriginalName(),
                'size' => $this->formatFileSize($this->file->getSize()),
                'mime' => $this->file->getMimeType(),
                'timestamp' => now()->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s')
            ];
        }
    }

    public function reviewFile()
    {
        $this->validate();
        $this->isLoading = true;

        // Create temp file path for API
        $tempPath = $this->file->store('temp');

        // Call Python API
        try {
            $response = Http::attach(
                'file',
                file_get_contents(storage_path('app/' . $tempPath)),
                $this->file->getClientOriginalName()
            )->post(env('PYTHON_API_URL', 'http://localhost:8000') . '/api/import-file-review');

            if ($response->successful()) {
                $this->dispatch('file-reviewed', $response->json());
            } else {
                $this->dispatch('file-review-error', 'Failed to review file');
            }
        } catch (\Exception $e) {
            $this->dispatch('file-review-error', $e->getMessage());
        } finally {
            $this->isLoading = false;
            // Cleanup temp file
            \Storage::delete($tempPath);
        }
    }

    private function formatFileSize($bytes)
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        }
        return round($bytes / 1024, 2) . ' KB';
    }

    public function render()
    {
        return view('livewire.import-file');
    }
}
