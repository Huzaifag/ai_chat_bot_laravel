@extends('admin.layouts.app')

@section('pageTitle', 'Upload Data')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Upload Knowledge</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Import files to train your chatbot. Support for PDF, DOCX,
                    TXT, CSV.</p>
            </div>

            <!-- Drop Zone -->
            <div id="drop-zone"
                class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-10 transition-all duration-200 ease-in-out text-center cursor-pointer hover:border-primary-500 dark:hover:border-primary-500 bg-gray-50 dark:bg-gray-800/50">
                <input type="file" id="file-input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" multiple
                    accept=".pdf,.docx,.txt,.csv">

                <div class="space-y-4 pointer-events-none">
                    <div
                        class="mx-auto w-16 h-16 bg-blue-100 dark:bg-blue-900/30 text-primary-600 dark:text-blue-400 rounded-full flex items-center justify-center">
                        <i data-lucide="cloud-upload" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">
                            <span class="text-primary-600 hover:text-primary-500">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Max file size: 20MB
                        </p>
                    </div>
                </div>
            </div>

            <!-- File List -->
            <div id="file-list" class="mt-4 space-y-2"></div>

            <!-- Interest/Slug Selection -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Interest Category</label>
                <select id="document-slug" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Select Interest (Optional)</option>
                    <option value="general">General</option>
                    <option value="technical">Technical</option>
                    <option value="business">Business</option>
                    <option value="medical">Medical</option>
                    <option value="legal">Legal</option>
                    <option value="educational">Educational</option>
                    <option value="research">Research</option>
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Categorize your documents by interest for better organization and filtering.</p>
            </div>

            <!-- Upload Button & Progress -->
            <div id="upload-actions" class="mt-8 hidden">
                <div id="progress-container" class="mb-4 hidden">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Uploading...</span>
                        <span class="text-gray-500" id="progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div id="progress-bar" class="bg-primary-600 h-2.5 rounded-full transition-all duration-300"
                            style="width: 0%"></div>
                    </div>
                </div>

                <button id="btn-upload-start" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 text-white bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 px-4 py-2 text-sm w-full">
                    Start Upload
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                let uploadSelectedFiles = [];

                function addFiles(files) {
                    const fileList = document.getElementById('file-list');
                    const uploadActions = document.getElementById('upload-actions');
                    
                    console.log('Adding files', files);
                    files.forEach(file => {
                        if (!uploadSelectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                            uploadSelectedFiles.push(file);
                            const fileItem = document.createElement('div');
                            fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg';
                            fileItem.innerHTML = `
                                <div class="flex items-center space-x-3">
                                    <i data-lucide="file" class="w-5 h-5 text-gray-500"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">${file.name}</p>
                                        <p class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                                    </div>
                                </div>
                                <button class="remove-file-btn text-red-500 hover:text-red-700" data-filename="${file.name}">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            `;
                            
                            // Attach remove event
                            const removeBtn = fileItem.querySelector('.remove-file-btn');
                            removeBtn.addEventListener('click', () => removeFile(file.name));
                            
                            fileList.appendChild(fileItem);
                            lucide.createIcons();
                        }
                    });
                    
                    if (uploadSelectedFiles.length > 0) {
                        uploadActions.classList.remove('hidden');
                    }
                }

                function removeFile(name) {
                    uploadSelectedFiles = uploadSelectedFiles.filter(f => f.name !== name);
                    const fileList = document.getElementById('file-list');
                    const items = fileList.querySelectorAll('.flex');
                    items.forEach(item => {
                        const nameEl = item.querySelector('p');
                        if (nameEl && nameEl.textContent === name) {
                            item.remove();
                        }
                    });
                    if (uploadSelectedFiles.length === 0) {
                        document.getElementById('upload-actions').classList.add('hidden');
                    }
                }

                function startUpload() {
                    console.log('Starting upload of files:', uploadSelectedFiles);
                    if (uploadSelectedFiles.length === 0) return;

                    const slug = document.getElementById('document-slug').value;
                    const formData = new FormData();
                    
                    uploadSelectedFiles.forEach(file => {
                        formData.append('document[]', file);
                    });
                    
                    if (slug) {
                        formData.append('slug', slug);
                    }

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/admin/documents');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    const progressContainer = document.getElementById('progress-container');
                    const progressBar = document.getElementById('progress-bar');
                    const progressPercent = document.getElementById('progress-percent');

                    progressContainer.classList.remove('hidden');

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percent = (e.loaded / e.total) * 100;
                            progressBar.style.width = percent + '%';
                            progressPercent.textContent = Math.round(percent) + '%';
                        }
                    });

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (typeof showToast === 'function') {
                                showToast(response.message);
                            } else {
                                alert(response.message);
                            }
                            // Reset
                            uploadSelectedFiles = [];
                            document.getElementById('file-list').innerHTML = '';
                            document.getElementById('upload-actions').classList.add('hidden');
                            progressContainer.classList.add('hidden');
                            progressBar.style.width = '0%';
                        } else {
                            if (typeof showToast === 'function') {
                                showToast('Upload failed', true);
                            } else {
                                alert('Upload failed');
                            }
                        }
                    };

                    xhr.send(formData);
                }

                // Initialize on DOMContentLoaded
                document.addEventListener('DOMContentLoaded', () => {
                    console.log('Initializing upload page');
                    
                    const dropZone = document.getElementById('drop-zone');
                    const fileInput = document.getElementById('file-input');
                    const btnStartUpload = document.getElementById('btn-upload-start');
                    
                    if (!dropZone || !fileInput || !btnStartUpload) {
                        console.error('Required elements not found');
                        return;
                    }

                    // Bind upload button event
                    btnStartUpload.addEventListener('click', (e) => {
                        console.log('Upload button clicked');
                        e.preventDefault();
                        startUpload();
                    });

                    // Drag and drop
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropZone.classList.add('border-primary-500');
                    });

                    dropZone.addEventListener('dragleave', () => {
                        dropZone.classList.remove('border-primary-500');
                    });

                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.remove('border-primary-500');
                        const files = Array.from(e.dataTransfer.files);
                        console.log('Files dropped', files);
                        addFiles(files);
                    });

                    // File input change event
                    fileInput.addEventListener('change', (e) => {
                        console.log('File input changed', e.target.files);
                        const files = Array.from(e.target.files);
                        addFiles(files);
                        // Clear input so same file can be selected again
                        e.target.value = '';
                    });
                });
            })();
        </script>
    @endpush
@endsection