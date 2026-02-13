<?php
/**
 * Typed Import Page - Supports different CSV formats per project type
 */

$pageTitle = 'Import by Project Type';
$activeTab = 'import';
require_once __DIR__ . '/../includes/header.php';

// Load project types
$config = require __DIR__ . '/../config/project_types.php';
$projectTypes = $config['project_types'];
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Import Projects by Type</h1>
                <p class="text-gray-600 mt-1">Import data using type-specific CSV formats</p>
            </div>
            <a href="import.php" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Back to Standard Import
            </a>
        </div>
    </div>

    <!-- Project Type Selection -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Project Type</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="project-type-grid">
            <?php foreach ($projectTypes as $typeName => $typeConfig): ?>
            <div class="project-type-card border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all"
                 data-type="<?php echo htmlspecialchars($typeName); ?>"
                 onclick="selectProjectType('<?php echo htmlspecialchars($typeName); ?>')">
                <div class="flex items-start space-x-3">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-2xl"
                         style="background-color: <?php echo $typeConfig['color']; ?>">
                        <?php echo getIconForType($typeConfig['icon']); ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($typeName); ?></h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($typeConfig['description']); ?></p>
                        <span class="inline-flex items-center px-2 py-1 mt-2 rounded-full text-xs font-medium"
                              style="background-color: <?php echo $typeConfig['color']; ?>20; color: <?php echo $typeConfig['color']; ?>">
                            <?php echo count($typeConfig['fields']); ?> fields
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Import Form (Hidden initially) -->
    <div id="import-form" class="hidden bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div id="selected-type-icon" class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-xl"></div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800" id="selected-type-name"></h2>
                    <p class="text-sm text-gray-600" id="selected-type-desc"></p>
                </div>
            </div>
            <a id="download-template-btn" href="#" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Template
            </a>
        </div>

        <!-- Field Preview -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Required Fields</h3>
            <div id="field-preview" class="flex flex-wrap gap-2">
                <!-- Fields will be populated by JS -->
            </div>
        </div>

        <!-- Import Format Selection -->
        <div class="mb-4" id="format-selection" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">Import Format</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="import_format" value="template" checked class="mr-2">
                    <span class="text-sm">Use Template Format</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="import_format" value="real" class="mr-2">
                    <span class="text-sm">Use Real CSV Format (from reports)</span>
                </label>
            </div>
        </div>
        
        <!-- Upload Area -->
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 hover:bg-blue-50 transition-all"
             id="drop-zone">
            <input type="file" id="csv-file" accept=".csv" class="hidden" onchange="handleFileSelect(event)">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="text-gray-600 mb-2">Drag and drop your CSV file here, or <button onclick="document.getElementById('csv-file').click()" class="text-blue-600 hover:underline">browse</button></p>
            <p class="text-sm text-gray-500">Make sure your CSV matches the template format</p>
        </div>

        <!-- Progress and Results -->
        <div id="upload-progress" class="hidden mt-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Uploading...</span>
                <span class="text-sm text-gray-500" id="progress-percent">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" id="progress-bar" style="width: 0%"></div>
            </div>
        </div>

        <div id="import-results" class="hidden mt-6">
            <div id="results-success" class="hidden p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-green-800 font-medium">Import Successful!</span>
                </div>
                <p class="text-green-700 mt-1 text-sm" id="success-message"></p>
            </div>

            <div id="results-error" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="text-red-800 font-medium">Import Failed</span>
                </div>
                <p class="text-red-700 mt-1 text-sm" id="error-message"></p>
            </div>

            <div id="error-details" class="hidden mt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Errors Found:</h4>
                <div id="error-list" class="max-h-60 overflow-y-auto space-y-2">
                    <!-- Errors will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedProjectType = null;
let projectTypeConfig = <?php echo json_encode($projectTypes); ?>;

function getIconForType(iconType) {
    const icons = {
        'wifi': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>',
        'computer': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'broadcast': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>',
        'city': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'library': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'briefcase': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'government': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'education': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>',
        'shield': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        'globe': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };
    return icons[iconType] || icons['wifi'];
}

function selectProjectType(type) {
    selectedProjectType = type;
    const config = projectTypeConfig[type];
    
    document.querySelectorAll('.project-type-card').forEach(card => {
        card.classList.remove('border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-200');
    });
    document.querySelector(`[data-type="${type}"]`).classList.remove('border-gray-200');
    document.querySelector(`[data-type="${type}"]`).classList.add('border-blue-500', 'bg-blue-50');
    
    document.getElementById('import-form').classList.remove('hidden');
    
    document.getElementById('selected-type-name').textContent = type;
    document.getElementById('selected-type-desc').textContent = config.description;
    
    const iconDiv = document.getElementById('selected-type-icon');
    iconDiv.style.backgroundColor = config.color;
    iconDiv.innerHTML = getIconForType(config.icon);
    
    document.getElementById('download-template-btn').href = `../api/import-typed.php?action=download-template&project_type=${encodeURIComponent(type)}`;
    
    // Show format selection for EgovPH and Free-WiFi
    const formatSelection = document.getElementById('format-selection');
    if (type === 'EgovPH' || type === 'Free-WIFI for All') {
        formatSelection.style.display = 'block';
    } else {
        formatSelection.style.display = 'none';
    }
    
    const fieldPreview = document.getElementById('field-preview');
    fieldPreview.innerHTML = '';
    
    Object.entries(config.fields).forEach(([fieldName, fieldConfig]) => {
        const badge = document.createElement('span');
        badge.className = `inline-flex items-center px-2 py-1 rounded text-xs font-medium ${fieldConfig.required ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700'}`;
        badge.innerHTML = `${fieldConfig.label} ${fieldConfig.required ? '*' : ''}`;
        fieldPreview.appendChild(badge);
    });
    
    document.getElementById('import-form').scrollIntoView({ behavior: 'smooth' });
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!selectedProjectType) {
        alert('Please select a project type first');
        return;
    }
    
    uploadFile(file);
}

async function uploadFile(file) {
    const formData = new FormData();
    formData.append('csv_file', file);
    formData.append('project_type', selectedProjectType);
    
    const importFormat = document.querySelector('input[name="import_format"]:checked')?.value || 'template';
    formData.append('import_format', importFormat);
    
    document.getElementById('upload-progress').classList.remove('hidden');
    document.getElementById('import-results').classList.add('hidden');
    
    try {
        const response = await fetch('../api/import-typed.php?action=import', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        document.getElementById('upload-progress').classList.add('hidden');
        document.getElementById('import-results').classList.remove('hidden');
        
        if (result.success) {
            document.getElementById('results-success').classList.remove('hidden');
            document.getElementById('results-error').classList.add('hidden');
            
            let message = `Successfully imported ${result.imported} records`;
            if (result.metrics_imported) {
                message += ` and ${result.metrics_imported} daily metrics`;
            }
            document.getElementById('success-message').textContent = message;
            
            if (result.errors && result.errors.length > 0) {
                document.getElementById('error-details').classList.remove('hidden');
                displayErrors(result.errors);
            } else {
                document.getElementById('error-details').classList.add('hidden');
            }
        } else {
            document.getElementById('results-success').classList.add('hidden');
            document.getElementById('results-error').classList.remove('hidden');
            document.getElementById('error-message').textContent = result.error || 'Import failed';
        }
    } catch (error) {
        document.getElementById('upload-progress').classList.add('hidden');
        document.getElementById('import-results').classList.remove('hidden');
        document.getElementById('results-success').classList.add('hidden');
        document.getElementById('results-error').classList.remove('hidden');
        document.getElementById('error-message').textContent = 'Network error: ' + error.message;
    }
}

function displayErrors(errors) {
    const errorList = document.getElementById('error-list');
    errorList.innerHTML = '';
    
    errors.forEach(error => {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'p-3 bg-red-50 border border-red-200 rounded text-sm';
        errorDiv.innerHTML = `
            <div class="font-medium text-red-800">Row ${error.row}</div>
            <ul class="mt-1 text-red-700 list-disc list-inside">
                ${error.errors.map(e => `<li>${e}</li>`).join('')}
            </ul>
        `;
        errorList.appendChild(errorDiv);
    });
}

// Drag and drop
const dropZone = document.getElementById('drop-zone');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFileSelect({ target: { files: files } });
    }
});
</script>

<?php
function getIconForType($iconType) {
    // Return SVG based on icon type
    return '';
}

require_once __DIR__ . '/../includes/footer.php';
?>
