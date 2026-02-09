// --- DATA & STATE ---
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}
let documents = [];
let selectedFiles = [];
const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB

// --- INIT ---
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    initTheme();
});

// --- API FUNCTIONS ---
async function fetchDocuments() {
    try {
        const response = await axios.get('/admin/documents');
        documents = response.data.data || response.data; // Handle pagination
        return documents;
    } catch (error) {
        console.error('Error fetching documents:', error);
        showToast('Failed to load documents', 'error');
        return [];
    }
}

// --- ROUTING & NAVIGATION ---
// Note: Router functionality removed for server-side routing
function router(viewName) {
    // This function is kept for compatibility but does nothing in server-side routing
    console.log('Router called for:', viewName);
}

// --- THEME HANDLING ---
function initTheme() {
    const isDark = localStorage.getItem('theme') === 'dark' ||
                   (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);

    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    document.getElementById('theme-toggle').addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    });
}

// --- DASHBOARD LOGIC ---
async function updateDashboardStats() {
    // Fetch documents if not already loaded
    if (documents.length === 0) {
        await fetchDocuments();
    }

    // Recent Table - only update if it exists
    const recentBody = document.getElementById('recent-docs-body');
    if (recentBody) {
        recentBody.innerHTML = '';
        const sortedDocs = [...documents].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        sortedDocs.slice(0, 3).forEach(doc => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="${getFileIcon(doc.mime_type)}" class="w-4 h-4 text-gray-400"></i> ${doc.original_name}
                </td>
                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">${new Date(doc.created_at).toLocaleDateString()}</td>
                <td class="px-6 py-4">${getStatusBadge(doc.status)}</td>
            `;
            recentBody.appendChild(tr);
        });
        lucide.createIcons();
    }
}

function timeSince(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " years ago";
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " months ago";
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " days ago";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " hours ago";
    return "Just now";
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// --- DOCUMENTS TABLE LOGIC ---
async function renderDocumentsTable(filterText = '') {
    const tbody = document.getElementById('documents-table-body');
    if (!tbody) return; // Only run if on documents page

    // Fetch documents if not already loaded
    if (documents.length === 0) {
        await fetchDocuments();
    }

    tbody.innerHTML = '';

    const filteredDocs = documents.filter(doc =>
        doc.original_name.toLowerCase().includes(filterText.toLowerCase())
    );

    // Update counts - only if elements exist
    const paginationInfo = document.getElementById('pagination-info');
    const totalItems = document.getElementById('total-items');
    if (paginationInfo) paginationInfo.innerText = `1-${filteredDocs.length}`;
    if (totalItems) totalItems.innerText = filteredDocs.length;

    if (filteredDocs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-gray-500">No documents found</td></tr>`;
        return;
    }

    filteredDocs.forEach(doc => {
        const tr = document.createElement('tr');
        tr.className = "hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group";
        tr.innerHTML = `
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-3">
                <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded text-gray-500 dark:text-gray-300">
                    <i data-lucide="${getFileIcon(doc.mime_type)}" class="w-4 h-4"></i>
                </div>
                ${doc.original_name}
            </td>
            <td class="px-6 py-4">${doc.mime_type}</td>
            <td class="px-6 py-4 text-gray-500">${formatBytes(doc.size)}</td>
            <td class="px-6 py-4 text-gray-500">${new Date(doc.created_at).toLocaleString()}</td>
            <td class="px-6 py-4">${getStatusBadge(doc.status)}</td>
            <td class="px-6 py-4 text-gray-500">${doc.embeddings_count || 0}</td>
            <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a href="/admin/documents/${doc.id}" class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded dark:hover:bg-gray-700 dark:hover:text-primary-400" title="View Details">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </a>
                    <a href="/admin/documents/${doc.id}/read" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded dark:hover:bg-gray-700 dark:hover:text-blue-400" title="Read Content">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                    </a>
                    <a href="/admin/documents/${doc.id}/download" class="p-1.5 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded dark:hover:bg-gray-700 dark:hover:text-green-400" title="Download">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </a>
                    <button onclick="deleteDocument(${doc.id})" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded dark:hover:bg-gray-700 dark:hover:text-red-400" title="Delete">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
    lucide.createIcons();
}

// Setup document search only if element exists
const documentSearch = document.getElementById('document-search');
if (documentSearch) {
    documentSearch.addEventListener('input', (e) => {
        renderDocumentsTable(e.target.value);
    });
}

function deleteDocument(id) {
    if(confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        axios.delete('/admin/documents/' + id)
            .then(() => {
                documents = documents.filter(d => d.id !== id);
                renderDocumentsTable();
                updateDashboardStats();
                showToast('Document deleted successfully');
            })
            .catch(error => {
                console.error('Delete failed:', error);
                showToast('Failed to delete document', true);
            });
    }
}

function viewDocument(id) {
    window.location.href = '/admin/documents/' + id;
}

// --- UPLOAD LOGIC ---
function setupFileUpload() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');

    // Only setup if elements exist (upload page)
    if (!dropZone || !fileInput) {
        return;
    }

    // Drag Events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-active'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-active'), false);
    });

    // Handle Drops
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    });

    // Handle Click/Input
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    document.getElementById('btn-start-upload')?.addEventListener('click', startUpload);
}

function handleFiles(files) {
    const list = document.getElementById('file-list');
    const actions = document.getElementById('upload-actions');

    // Add new files to array
    [...files].forEach(file => {
        // Validation
        if (file.size > MAX_FILE_SIZE) {
            showToast('File too large: ' + file.name, true);
            return;
        }

        // Add to list
        selectedFiles.push(file);

        // Render list item
        const div = document.createElement('div');
        div.className = "flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 animate-fade-in";
        div.id = `file-${file.name.replace(/[^a-zA-Z0-9]/g, '')}`; // cleanup id
        div.innerHTML = `
            <div class="flex items-center gap-3">
                <i data-lucide="file" class="text-gray-400"></i>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">${file.name}</p>
                    <p class="text-xs text-gray-500">${formatBytes(file.size)}</p>
                </div>
            </div>
            <button onclick="removeFile('${file.name}', this)" class="text-gray-400 hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;
        list.appendChild(div);
    });

    lucide.createIcons();

    if (selectedFiles.length > 0) {
        actions.classList.remove('hidden');
    }
}

// Global scope for HTML onclick access
window.removeFile = function(fileName, btnElement) {
    selectedFiles = selectedFiles.filter(f => f.name !== fileName);
    btnElement.parentElement.remove();
    if (selectedFiles.length === 0) {
        document.getElementById('upload-actions').classList.add('hidden');
    }
};

async function startUpload() {
    const btn = document.getElementById('btn-start-upload');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const progressPercent = document.getElementById('progress-percent');

    btn.disabled = true;
    btn.innerText = "Uploading...";
    progressContainer.classList.remove('hidden');

    try {
        const formData = new FormData();
        selectedFiles.forEach(file => {
            formData.append('document[]', file);
        });

        const slug = document.getElementById('document-slug').value;
        if (slug) {
            formData.append('slug', slug);
        }

        const response = await axios.post('/admin/documents', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                progressBar.style.width = percent + '%';
                progressPercent.innerText = percent + '%';
            }
        });

        showToast('Documents uploaded and processing started');
        finalizeUpload();

    } catch (error) {
        console.error('Upload failed:', error);
        showToast('Upload failed: ' + (error.response?.data?.message || error.message), true);
        btn.disabled = false;
        btn.innerText = "Start Upload";
    }
}

function finalizeUpload() {
    // Reset UI
    selectedFiles = [];
    document.getElementById('file-list').innerHTML = '';
    document.getElementById('upload-actions').classList.add('hidden');
    document.getElementById('btn-start-upload').disabled = false;
    document.getElementById('btn-start-upload').innerText = "Start Upload";
    document.getElementById('progress-bar').style.width = '0%';

    // Refresh documents and dashboard
    documents = []; // Clear cache to refetch
    updateDashboardStats();

    // Redirect to documents view
    setTimeout(() => {
        router('documents');
    }, 1000);
}

// --- UTILS ---
function getStatusBadge(status) {
    const styles = {
        'Ready': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'Processing': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'Failed': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
    };
    const icons = {
        'Ready': 'check-circle',
        'Processing': 'loader-2',
        'Failed': 'alert-circle'
    };

    const iconClass = status === 'Processing' ? 'animate-spin' : '';

    return `
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${styles[status] || styles['Failed']}"}>
            <i data-lucide="${icons[status]}" class="w-3 h-3 mr-1 ${iconClass}"></i>
            ${status}
        </span>
    `;
}

function getFileIcon(mimeType) {
    const map = {
        'application/pdf': 'file-text',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'file',
        'text/csv': 'sheet',
        'text/plain': 'file-code'
    };
    return map[mimeType] || 'file';
}

function formatBytes(bytes, decimals = 2) {
    if (!+bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}

function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    if (!toast) return; // Prevent error if toast element not found

    const toastMsg = document.getElementById('toast-message');

    toastMsg.innerText = message;

    // Reset classes
    toast.classList.remove('translate-y-20', 'opacity-0');

    if (isError) {
        toast.querySelector('.border-l-4').classList.replace('border-green-500', 'border-red-500');
        toast.querySelector('i').classList.replace('text-green-500', 'text-red-500');
        toast.querySelector('i').setAttribute('data-lucide', 'alert-octagon');
    } else {
         toast.querySelector('.border-l-4').classList.replace('border-red-500', 'border-green-500');
        toast.querySelector('i').classList.replace('text-red-500', 'text-green-500');
        toast.querySelector('i').setAttribute('data-lucide', 'check-circle');
    }
    lucide.createIcons();

    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}