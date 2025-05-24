<x-layouts.app>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 dark:bg-zinc-900 dark:border dark:border-zinc-700">
                <!-- File Upload Area -->
                <div
                    x-data="{ isHovered: false }"
                    @dragover.prevent="isHovered = true"
                    @dragleave.prevent="isHovered = false"
                    @drop.prevent="isHovered = false; $event.dataTransfer.files.length && $refs.fileInput.files = $event.dataTransfer.files"
                    :class="{ 'border-blue-500 bg-blue-50': isHovered }"
                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors duration-200"
                >
                    <div class="space-y-4">
                        <div class="flex items-center justify-center">
                            <label for="file-upload" class="cursor-pointer">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600">
                                        Kéo thả file vào đây hoặc
                                        <span class="text-blue-600 hover:text-blue-700">chọn file</span>
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        PDF, Excel (XLS/XLSX), Word (DOC/DOCX) - Tối đa 5MB
                                    </p>
                                </div>
                                <input
                                    x-ref="fileInput"
                                    id="file-upload"
                                    type="file"
                                    wire:model="file"
                                    accept=".pdf,.xls,.xlsx,.doc,.docx"
                                    class="hidden"
                                >
                            </label>
                        </div>

                        <!-- File Preview -->
                        @if($fileInfo)
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Thông tin file</h3>
                            <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Tên file:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $fileInfo['name'] }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Kích thước:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $fileInfo['size'] }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Định dạng:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $fileInfo['mime'] }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Thời gian:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $fileInfo['timestamp'] }}</dd>
                                </div>
                            </dl>
                        </div>
                        @endif

                        <!-- PDFTron WebViewer -->
                        @if($file)
                        <div class="mt-4">
                            <div id="viewer" style="height: 600px;" wire:ignore></div>
                        </div>
                        @endif

                        <!-- Review Button -->
                        @if($file)
                        <div class="mt-4 flex justify-end">
                            <button
                                wire:click="reviewFile"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Review File
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                @error('file')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webviewer/8.0.0/webviewer.min.js"></script>
    <script>
    document.addEventListener('livewire:init', () => {
        let viewer = null;

        function initViewer() {
            WebViewer({
                path: '/webviewer/lib',
                initialDoc: URL.createObjectURL(@this.file),
                licenseKey: 'YOUR_LICENSE_KEY' // Replace with your PDFTron license key
            }, document.getElementById('viewer')).then(instance => {
                viewer = instance;
                const { documentViewer, annotationManager } = instance.Core;

                // Enable all default tools
                instance.setToolbarGroup('toolbarGroup-View');
                instance.setToolbarGroup('toolbarGroup-Annotate');
                instance.setToolbarGroup('toolbarGroup-Shapes');
                instance.setToolbarGroup('toolbarGroup-Edit');
                instance.setToolbarGroup('toolbarGroup-Insert');

                // Save viewer state
                window.viewer = viewer;
            });
        }

        // Initialize viewer when file is uploaded
        @this.on('file-uploaded', () => {
            if (@this.file) {
                if (viewer) {
                    viewer.dispose();
                }
                initViewer();
            }
        });

        // Handle file review events
        @this.on('file-reviewed', (response) => {
            // Handle successful review
            console.log('File reviewed:', response);
        });

        @this.on('file-review-error', (error) => {
            // Handle review error
            console.error('Review error:', error);
        });
    });
    </script>
    @endpush
</x-layouts.app>
