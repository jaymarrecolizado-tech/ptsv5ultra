<?php
/**
 * CSV Import Page
 */

$pageTitle = 'Data Migration';
$activeTab = 'import';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Upload Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import Projects from CSV/Excel
            </h2>
        </div>
        
        <div class="p-6">
            <!-- Drop Zone -->
            <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-blue-500 transition-colors cursor-pointer bg-gray-50">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-700 mb-2">Drag & Drop CSV File Here</h3>
                <p class="text-gray-500 mb-4">or click to browse files</p>
                <input type="file" id="file-input" accept=".csv" class="hidden">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Select File
                </button>
            </div>
            
            <!-- Progress -->
            <div id="upload-progress" class="hidden mt-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Uploading...</span>
                    <span class="text-sm text-gray-500" id="progress-text">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Results -->
            <div id="import-results" class="hidden mt-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-800 mb-3">Import Results</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                            <p class="text-2xl font-bold text-green-600" id="result-imported">0</p>
                            <p class="text-sm text-gray-500">Imported</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                            <p class="text-2xl font-bold text-gray-600" id="result-total">0</p>
                            <p class="text-sm text-gray-500">Total Rows</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                            <p class="text-2xl font-bold text-red-600" id="result-errors">0</p>
                            <p class="text-sm text-gray-500">Errors</p>
                        </div>
                    </div>
                    
                    <div id="error-details" class="hidden mt-4">
                        <h5 class="font-medium text-red-600 mb-2">Error Details:</h5>
                        <ul id="error-list" class="space-y-1 text-sm text-red-600 max-h-48 overflow-auto bg-red-50 rounded p-3"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Template Download -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">CSV Template</h3>
        <p class="text-gray-600 mb-4">Download the CSV template to ensure your data is in the correct format.</p>
        <a href="../api/import.php?action=template" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download Template
        </a>
    </div>
    
    <!-- Validation Rules -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Data Validation Rules
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Required Fields</h4>
                    <p class="text-sm text-gray-600">All mandatory fields must be present and not empty</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Data Format Validation</h4>
                    <p class="text-sm text-gray-600">Coordinates must be valid numbers, dates must follow standard format</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Value Consistency</h4>
                    <p class="text-sm text-gray-600">Province, Municipality, Barangay values must be consistent</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-medium text-gray-800">Duplicate Detection</h4>
                    <p class="text-sm text-gray-600">Site codes must be unique; duplicates will be flagged</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
