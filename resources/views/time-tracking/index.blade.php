<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Time Tracking') }}</h2>
    </x-slot>

    <x-slot name="headerNav">
        <nav class="flex gap-6 -mb-px overflow-x-auto">
            <a href="{{ route('time-tracking.index') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('time-tracking.index') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Time Tracking</a>
            <a href="{{ route('time-tracking.logs') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('time-tracking.logs') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Logs</a>
            <a href="{{ route('work-schedules.index') }}" class="inline-flex items-center px-1 py-3 border-b-2 text-sm font-medium {{ request()->routeIs('work-schedules.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">Schedule</a>
        </nav>
    </x-slot>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
        <style>
            body {
                background-color: #f8f9fa;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .card {
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
                border: none;
            }
            .table th {
                background-color: #f1f5fd;
                border-top: none;
            }
            .table-responsive {
                position: relative;
            }
            .table-responsive thead th {
                position: sticky;
                top: 0;
                z-index: 5;
                background-color: #f1f5fd;
                box-shadow: inset 0 -1px 0 #dee2e6;
            }
            .clickable-row {
                cursor: pointer;
            }
            .clickable-row:hover {
                background-color: #f8f9ff;
            }
            .badge-late {
                background-color: #ffc107;
                color: #000;
            }
            .badge-undertime {
                background-color: #dc3545;
                color: #fff;
            }
            .badge-ontime {
                background-color: #28a745;
                color: #fff;
            }
            .summary-card {
                transition: transform 0.2s;
            }
            .summary-card:hover {
                transform: translateY(-5px);
            }
            .employee-name {
                font-weight: 600;
                color: #2c3e50;
            }
            .stat-number {
                font-size: 1.8rem;
                font-weight: bold;
            }
            .stat-label {
                font-size: 0.9rem;
                color: #6c757d;
            }
            .filter-section {
                background-color: #fff;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }
            .upload-area {
                border: 2px dashed #dee2e6;
                border-radius: 8px;
                padding: 30px;
                text-align: center;
                background-color: #f8f9fa;
                cursor: pointer;
                transition: all 0.3s;
            }
            .upload-area:hover {
                border-color: #0d6efd;
                background-color: #e7f1ff;
            }
            .upload-area.dragover {
                border-color: #0d6efd;
                background-color: #e7f1ff;
            }
            .upload-area.upload-area-compact {
                padding: 12px 14px;
                border-radius: 10px;
                min-width: 320px;
                max-width: 420px;
                text-align: left;
            }
            .upload-area.upload-area-compact .upload-title {
                font-weight: 700;
                margin: 0;
            }
            .upload-area.upload-area-compact .upload-subtitle {
                margin: 0;
                font-size: 0.85rem;
                color: #6c757d;
            }
            .upload-area.upload-area-compact .upload-filename {
                margin: 0;
                font-size: 0.8rem;
                color: #0d6efd;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 220px;
            }
            .loading {
                display: none;
                text-align: center;
                padding: 20px;
            }
            .time-in {
                color: #198754;
                font-weight: 600;
            }
            .time-out {
                color: #dc3545;
                font-weight: 600;
            }
            .hours-worked {
                font-weight: 600;
            }
            .late-minutes {
                font-weight: 600;
            }
            .undertime-minutes {
                font-weight: 600;
            }
            .hours-worked,
            .late-minutes,
            .undertime-minutes {
                color: #000;
            }
            .daily-table-scroll {
                max-height: 560px;
                overflow: auto;
            }
            #letterEditor {
                background: #ffffff;
                border: 1px solid #dee2e6;
                padding: 25mm;
                border-radius: 10px;
                font-family: "Times New Roman", Times, serif;
                font-size: 12pt;
                line-height: 1.55;
                color: #111827;
                min-height: 680px;
                outline: none;
            }
            #letterEditor:focus {
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
            }
            #letterEditor .letterhead {
                font-weight: 700;
            }
            #letterEditor .date {
                text-align: right;
                margin-top: 12pt;
            }
            #letterEditor .subject {
                font-weight: 700;
                text-transform: uppercase;
                margin: 14pt 0 10pt;
            }
            #letterEditor .para {
                text-indent: 0.5in;
                margin: 0 0 10pt;
            }
            #letterEditor .heading {
                font-weight: 700;
                margin: 10pt 0 6pt;
            }
            #letterEditor ul {
                margin: 6pt 0 10pt 0.5in;
            }
            #letterEditor li {
                margin: 2pt 0;
            }
            #letterEditor .fill {
                flex: 1;
                min-width: 0;
                border-bottom: 1px solid #111827;
                padding: 0 4px 1px;
                white-space: nowrap;
                vertical-align: baseline;
                line-height: 1.1;
            }
            #letterEditor .fill[contenteditable="true"]:empty:before {
                content: attr(data-placeholder);
                color: #9ca3af;
            }
            #letterEditor .fill:focus {
                outline: none;
            }
            #letterEditor .field-row {
                display: flex;
                align-items: flex-end;
                gap: 10px;
                margin-top: 2pt;
            }
            #letterEditor .field-label {
                white-space: nowrap;
            }
            #letterEditor .signature-block {
                margin-top: 18pt;
            }
            #letterEditor .spacer {
                height: 18pt;
            }
            #employeeTable tbody tr { height: 38px; }
            #employeeTable td { padding-top: 0.45rem; padding-bottom: 0.45rem; }
            .daily-table-scroll { max-height: 640px; }
        </style>
    @endpush

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="container px-0 py-0">
                        <div class="row">
                            <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h1 class="h3 mb-0"><i class="bi bi-clock-history me-2"></i>Employee Time Tracking System</h1>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="d-flex flex-column" style="min-width: 260px;">
                                <select id="savedBatchSelect" class="form-select form-select-sm" disabled>
                                    <option value="">Loading...</option>    
                                </select>
                            </div>

                            <div id="uploadArea" class="upload-area upload-area-compact">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-cloud-upload text-muted fs-4"></i>
                                        <div>
                                            <p class="upload-title">Upload CSV</p>
                                            <p class="upload-subtitle">Drop file here or browse</p>
                                            <p id="csvFileName" class="upload-filename d-none"></p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="file" id="csvFile" accept=".csv" class="d-none">
                                        <button class="btn btn-sm btn-primary" onclick="document.getElementById('csvFile').click()">
                                            <i class="bi bi-upload me-1"></i>Browse
                                        </button>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <div id="loadingIndicator" class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Processing time records...</p>
            </div>

            <div id="csvFeedback" class="alert d-none" role="alert"></div>

           

            <div id="summarySection" class="row mb-4" style="display: none;">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card summary-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people display-6 text-primary mb-2"></i>
                            <h2 id="totalEmployees" class="stat-number">0</h2>
                            <p class="stat-label">Total Employees</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card summary-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-history display-6 text-warning mb-2"></i>
                            <h2 id="totalLateCount" class="stat-number">0</h2>
                            <p class="stat-label">Total Late Incidents</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card summary-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-alarm display-6 text-danger mb-2"></i>
                            <h2 id="avgLateMinutes" class="stat-number">0</h2>
                            <p class="stat-label">Avg Late Minutes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card summary-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-week display-6 text-success mb-2"></i>
                            <h2 id="daysAnalyzed" class="stat-number">0</h2>
                            <p class="stat-label">Days Analyzed</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card summary-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-range display-6 text-info mb-2"></i>
                            <h2 id="periodRange" class="stat-number" style="font-size: 1.1rem;">-</h2>
                            <p class="stat-label">Period (Start - End)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="detailsSection">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Monthly Late Count Summary</h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="min-width: 260px;">
                                        <input type="text" id="monthlySearchInput" class="form-control" placeholder="Search name, ID, department" />
                                        <button class="btn btn-outline-secondary" type="button" onclick="applyMonthlySearch()">
                                            <i class="bi bi-search me-1"></i>Search
                                        </button>
                                    </div>

                                    <select id="departmentFilterSummary" class="form-select form-select-sm" style="min-width: 220px;">
                                        <option value="ALL">All Departments</option>
                                    </select>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="exportToCSV()">
                                            <i class="bi bi-download me-1"></i> Export CSV
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="printReport()">
                                            <i class="bi bi-printer me-1"></i> Print Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="monthlyTable">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Department</th>
                                                <th>Late Frequency</th>
                                                <th>Missed Logs</th>
                                                <th>Grace Days</th>
                                                <th>Absences</th>
                                                <th>Days Worked</th>
                                                <th>Late Duration</th>
                                                <th>Avg Late per Occurrence</th>
                                                <th>Total Undertime</th>
                                                <th>Undertime Frequency</th>
                                                <th>Most Frequent Late Time</th>
                                                <th>Paid Leave</th>
                                                <th>Unpaid Leave</th>
                                                <th>Letter</th>
                                            </tr>
                                        </thead>
                                        <tbody id="monthlyBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Employee Daily Time Records</h5>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="min-width: 260px;">
                                        <input type="text" id="dailySearchInput" class="form-control" placeholder="Search name, ID, department, date" />
                                        <button class="btn btn-outline-secondary" type="button" onclick="applyDailySearch()">
                                            <i class="bi bi-search me-1"></i>Search
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-muted small">From:</span>
                                        <input type="text" id="dailyStartDate" class="form-control form-control-sm" style="min-width: 150px;" placeholder="MM-DD-YYYY">
                                        <span class="text-muted small">To:</span>
                                        <input type="text" id="dailyEndDate" class="form-control form-control-sm" style="min-width: 140px;" placeholder="MM-DD-YY">
                                    </div>
                                    <select id="departmentFilter" class="form-select form-select-sm" style="min-width: 220px;">
                                        <option value="ALL">All Departments</option>
                                    </select>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="exportToCSV()">
                                            <i class="bi bi-download me-1"></i> Export CSV
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="printReport()">
                                            <i class="bi bi-printer me-1"></i> Print Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 mb-3">
                                    <strong class="me-2">Legend:</strong>
                                    <span class="badge bg-success">On time</span>
                                    <span class="badge bg-danger">Late</span>
                                    <span class="badge bg-primary">Early In</span>
                                    <span class="badge bg-warning text-dark">Undertime</span>
                                    <span class="badge bg-secondary">Missed log</span>
                                    <span class="text-muted ms-2">Applies to time columns (Time In/Out, Break In/In).</span>
                                </div>
                                <div class="table-responsive daily-table-scroll">
                                    <table class="table table-hover" id="employeeTable">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Date</th>
                                                <th>Time In</th>
                                                <th>Break In</th>
                                                <th>Break Out</th>
                                                <th>Time Out</th>
                                                <th>Grace Used</th>
                                                <th>Late In (min)</th>
                                                <th>Undertime Break In (min)</th>
                                                <th>Late Break Out (min)</th>
                                                <th>OT (min)</th>
                                                <th>Total Late (min)</th>
                                                <th>Undertime (min)</th>
                                                <th>Total Hours</th>
                                                <th>Missed Logs</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="employeeDailyModal" tabindex="-1" aria-labelledby="employeeDailyModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="employeeDailyModalLabel">Daily Time Records</h5>
                                <div id="employeeDailyModalMeta" class="text-muted small"></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 mb-3">
                                <strong class="me-2">Legend:</strong>
                                <span class="badge bg-success">On time</span>
                                <span class="badge bg-danger">Late</span>
                                <span class="badge bg-primary">Early In</span>
                                <span class="badge bg-warning text-dark">Undertime</span>
                                <span class="badge bg-secondary">Missed log</span>
                                <span class="text-muted ms-2">Applies to time columns (Time In/Out, Break In/In).</span>
                            </div>
                            <div class="table-responsive daily-table-scroll" style="max-height: 70vh;">
                                <table class="table table-hover" id="employeeDailyModalTable">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time In</th>
                                            <th>Break In</th>
                                            <th>Break Out</th>
                                            <th>Time Out</th>
                                            <th>Grace Used</th>
                                            <th>Total Late (min)</th>
                                            <th>Undertime (min)</th>
                                            <th>Total Hours</th>
                                            <th>Missed Logs</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employeeDailyModalBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="letterModal" tabindex="-1" aria-labelledby="letterModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="letterModalLabel">Letter</h5>
                                <div id="letterModalMeta" class="text-muted small"></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="letterEditor" contenteditable="true"></div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyLetterText()">Copy (text)</button>
                                <button class="btn btn-outline-success btn-sm" type="button" onclick="printLetter()">Print</button>
                                <button class="btn btn-outline-danger btn-sm" type="button" onclick="downloadLetterPdf()">Download PDF</button>
                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="downloadLetterWord()">Download Word</button>
                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="downloadLetter()">Download .txt</button>
                            </div>
                            <div class="text-muted small mt-2">Edit the template like a real letter. The underlined fields stay underlined as you type.</div>
                        </div>
                    </div>
                </div>

            </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

        <script>
            // Configuration
            const WORK_START_TIME = "08:00:00";
            const MORNING_LATE_START = "08:01:00";
            const MORNING_GRACE_END = "08:15:00";
            const BREAK_OUT_TIME = "12:00:00";
            const BREAK_OUT_GRACE_END = "12:30:00";
            const BREAK_OUT_LATEST = "12:44:59";
            const EARLY_BREAK_IN_START = "12:31:00";
            const BREAK_IN_EARLIEST = "12:45:00";
            const BREAK_IN_TIME = "13:00:00";
            const AFTERNOON_LATE_START = "13:01:00";
            const AFTERNOON_GRACE_END = "13:15:00";
            const WORK_END_TIME = "16:59:00";
            const AFTERNOON_UNDERTIME_END = "16:59:00";
            const EARLY_OUT_ALLOWANCE_MINUTES = 16;
            const MIN_WORK_BEFORE_LUNCH_MINUTES = 120;
            const MIN_LUNCH_BREAK_MINUTES = 15;
            const MIN_WORK_AFTER_LUNCH_MINUTES = 60;
            const MAX_BREAK_IN_DISTANCE_MINUTES = 180;
            const LUNCH_DURATION = 60; // minutes
            const DEDUPE_WINDOW_MINUTES = 2; // punches within this many minutes = same punch (double-tap/lag); keep one only

            const DEPARTMENT_SCHEDULES = @json($departmentSchedules ?? []);
            const VALID_EMPLOYEE_CODES = @json($validEmployeeCodes ?? []);
            let employeeData = [];
            let processedData = [];
            let monthlySummary = {};
            let periodSummary = {};
            let selectedDepartment = 'ALL';
            let dailySearchQuery = '';
            let monthlySearchQuery = '';
            let dateStart = '';
            let dateEnd = '';

            // DOM elements
            const uploadArea = document.getElementById('uploadArea');
            const csvFileInput = document.getElementById('csvFile');
            const saveCsvBtn = document.getElementById('saveCsvBtn');
            const loadingIndicator = document.getElementById('loadingIndicator');
            const summarySection = document.getElementById('summarySection');
            const detailsSection = document.getElementById('detailsSection');
            const tableBody = document.getElementById('tableBody');
            const monthlyBody = document.getElementById('monthlyBody');
            const departmentFilter = document.getElementById('departmentFilter');
            const departmentFilterSummary = document.getElementById('departmentFilterSummary');
            const dailySearchInput = document.getElementById('dailySearchInput');
            const monthlySearchInput = document.getElementById('monthlySearchInput');
            const dailyStartDateInput = document.getElementById('dailyStartDate');
            const dailyEndDateInput = document.getElementById('dailyEndDate');
            const monthlyStartDateInput = document.getElementById('monthlyStartDate');
            const monthlyEndDateInput = document.getElementById('monthlyEndDate');
            const employeeDailyModalEl = document.getElementById('employeeDailyModal');
            const employeeDailyModalLabel = document.getElementById('employeeDailyModalLabel');
            const employeeDailyModalMeta = document.getElementById('employeeDailyModalMeta');
            const employeeDailyModalBody = document.getElementById('employeeDailyModalBody');
            const letterModalEl = document.getElementById('letterModal');
            const letterModalLabel = document.getElementById('letterModalLabel');
            const letterModalMeta = document.getElementById('letterModalMeta');
            const letterEditor = document.getElementById('letterEditor');
            let currentLetterFilename = 'letter.txt';
            let currentLetterContext = null;

            // Event Listeners
            csvFileInput.addEventListener('change', handleFileUpload);
            
            uploadArea.addEventListener('dragover', handleDragOver);
            uploadArea.addEventListener('dragleave', handleDragLeave);
            uploadArea.addEventListener('drop', handleDrop);
            uploadArea.addEventListener('click', (e) => {
                if (e.target && e.target.closest && e.target.closest('button')) return;
                if (e.target && e.target.closest && e.target.closest('input')) return;
                csvFileInput.click();
            });
            departmentFilter.addEventListener('change', function() {
                selectedDepartment = this.value;
                if (departmentFilterSummary) departmentFilterSummary.value = selectedDepartment;
                displayResults();
            });
            departmentFilterSummary.addEventListener('change', function() {
                selectedDepartment = this.value;
                if (departmentFilter) departmentFilter.value = selectedDepartment;
                displayResults();
            });
            dailySearchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    applyDailySearch();
                }
            });
            monthlySearchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    applyMonthlySearch();
                }
            });

            function setDateRange(nextStart, nextEnd) {
                dateStart = parseUserDateToISO((nextStart || '').trim());
                dateEnd = parseUserDateToISO((nextEnd || '').trim());
                if (dateStart && dateEnd && dateStart > dateEnd) {
                    const tmp = dateStart;
                    dateStart = dateEnd;
                    dateEnd = tmp;
                }
                if (dailyStartDateInput) dailyStartDateInput.value = formatISOToUserMMDDYYYY(dateStart);
                if (dailyEndDateInput) dailyEndDateInput.value = formatISOToUserMMDDYY(dateEnd);
                if (monthlyStartDateInput) monthlyStartDateInput.value = formatISOToUserMMDDYYYY(dateStart);
                if (monthlyEndDateInput) monthlyEndDateInput.value = formatISOToUserMMDDYY(dateEnd);
                displayResults();
            }

            const dateInputs = [dailyStartDateInput, dailyEndDateInput, monthlyStartDateInput, monthlyEndDateInput].filter(Boolean);
            dateInputs.forEach(inp => {
                inp.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        setDateRange(dailyStartDateInput?.value, dailyEndDateInput?.value);
                    }
                });
                inp.addEventListener('blur', () => setDateRange(dailyStartDateInput?.value, dailyEndDateInput?.value));
                inp.addEventListener('change', () => setDateRange(dailyStartDateInput?.value, dailyEndDateInput?.value));
            });

            // Calendar picker for date filters (keeps your required formats)
            if (typeof flatpickr !== 'undefined') {
                const commonOpts = {
                    allowInput: true,
                    clickOpens: true,
                    altInput: false
                };
                if (dailyStartDateInput) flatpickr(dailyStartDateInput, { ...commonOpts, dateFormat: 'm-d-Y' });
                if (monthlyStartDateInput) flatpickr(monthlyStartDateInput, { ...commonOpts, dateFormat: 'm-d-Y' });
                if (dailyEndDateInput) flatpickr(dailyEndDateInput, { ...commonOpts, dateFormat: 'm-d-y' });
                if (monthlyEndDateInput) flatpickr(monthlyEndDateInput, { ...commonOpts, dateFormat: 'm-d-y' });
            }

            function parseUserDateToISO(input) {
                const s = (input || '').trim();
                if (!s) return '';
                const iso = s.match(/^\d{4}-\d{2}-\d{2}$/);
                if (iso) return s;
                const m = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2}|\d{4})$/);
                if (!m) {
                    const d = new Date(s);
                    if (!Number.isNaN(d.getTime())) {
                        return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
                    }
                    return '';
                }
                const mm = parseInt(m[1], 10);
                const dd = parseInt(m[2], 10);
                let yy = m[3];
                if (!(mm >= 1 && mm <= 12 && dd >= 1 && dd <= 31)) return '';
                let yyyy = parseInt(yy, 10);
                if (yy.length === 2) {
                    yyyy = 2000 + yyyy;
                }
                return `${yyyy}-${pad2(mm)}-${pad2(dd)}`;
            }

            function formatISOToUserMMDDYYYY(iso) {
                if (!iso) return '';
                const m = String(iso).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) return '';
                return `${m[2]}-${m[3]}-${m[1]}`;
            }

            function formatISOToUserMMDDYY(iso) {
                if (!iso) return '';
                const m = String(iso).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) return '';
                return `${m[2]}-${m[3]}-${m[1].slice(2)}`;
            }

            function handleDragOver(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            }

            function handleDragLeave(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            }

            function handleDrop(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');

                if (e.dataTransfer.files.length) {
                    csvFileInput.files = e.dataTransfer.files;
                    handleFileUpload();
                }
            }

            function handleFileUpload() {
                const file = csvFileInput.files[0];
                if (!file) return;

                if (csvFileNameEl) {
                    csvFileNameEl.textContent = file.name;
                    csvFileNameEl.classList.remove('d-none');
                }

                hideCsvFeedback();

                if (!file.name.toLowerCase().endsWith('.csv')) {
                    showCsvFeedback('danger', 'Please upload a CSV file.');
                    return;
                }

                showLoading();

                window.__selectedCsvFile = file;

                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const csvContent = e.target.result;
                        parseCSV(csvContent);
                        processData();
                        updateDepartmentFilterOptions();
                        displayResults();

                        saveCsvToServer();
                    } catch (error) {
                        showCsvFeedback('danger', 'Error processing CSV file: ' + (error?.message || error));
                        hideLoading();
                    }
                };
                reader.readAsText(file);
            }

            function showCsvFeedback(type, message) {
                if (!csvFeedback) return;
                csvFeedback.className = `alert alert-${type}`;
                csvFeedback.textContent = String(message || '');
                csvFeedback.classList.remove('d-none');
            }

            function hideCsvFeedback() {
                if (!csvFeedback) return;
                csvFeedback.classList.add('d-none');
                csvFeedback.textContent = '';
                csvFeedback.className = 'alert d-none';
            }

            function saveCsvToServer() {
                const file = window.__selectedCsvFile;
                if (!file) {
                    showCsvFeedback('warning', 'Please upload a CSV first.');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                if (saveCsvBtn) {
                    saveCsvBtn.disabled = true;
                }

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('{{ route('time-tracking.upload-csv') }}', {
                    method: 'POST',
                    headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
                    body: formData
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok) {
                        const msg = data?.message || 'Save failed.';
                        throw new Error(msg);
                    }
                    return data;
                })
                .then((data) => {
                    const c = data?.counts;
                    const msg = c
                        ? `Saved! Logs: ${c.logs}, Daily: ${c.daily_summaries}, Period: ${c.period_summaries}`
                        : 'Saved!';
                    showCsvFeedback('success', msg);

                    const batchUuid = String(data?.batch_uuid || '');
                    if (batchUuid) {
                        if (savedBatchSelect) {
                            savedBatchSelect.value = batchUuid;
                        }
                        return loadSavedSummariesByBatch(batchUuid);
                    }
                })
                .catch((err) => {
                    showCsvFeedback('danger', 'Server save failed: ' + (err?.message || err));
                })
                .finally(() => {
                    if (saveCsvBtn) {
                        saveCsvBtn.disabled = false;
                    }
                });
            }

            function loadSavedSummariesByBatch(batchUuid) {
                const url = new URL("{{ route('time-tracking.summaries') }}", window.location.origin);
                url.searchParams.set('batch', batchUuid);

                return fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then((data) => {
                    const range = data?.date_range;
                    if (range?.start && range?.end) {
                        const el = document.getElementById('periodRange');
                        if (el) {
                            el.textContent = `${range.start} - ${range.end}`;
                        }
                    }

                    const daily = Array.isArray(data?.daily) ? data.daily : [];
                    const period = Array.isArray(data?.period) ? data.period : [];

                    processedData = daily.map(d => {
                        const lateIn = Number(d.late_in_minutes || 0);
                        const lateBreak = Number(d.late_break_in_minutes || 0);
                        const undertime = Number(d.undertime_break_out_minutes || 0);
                        const totalLateMinutesOverall = lateIn + lateBreak;
                        const totalLateFrequency = (lateIn > 0 ? 1 : 0) + (lateBreak > 0 ? 1 : 0);

                        return {
                            employeeId: d.employee_code,
                            employeeName: d.employee_name || 'Unknown',
                            department: d.department || '',
                            date: d.summary_date,
                            timeIn: d.time_in,
                            breakOut: d.break_out,
                            breakIn: d.break_in,
                            timeOut: d.time_out,
                            rawTimeIn: d.time_in,
                            rawBreakOut: d.break_out,
                            rawBreakIn: d.break_in,
                            rawTimeOut: d.time_out,
                            lateMinutes: lateIn,
                            lateBreakInMinutes: lateBreak,
                            undertimeBreakOutMinutes: undertime,
                            undertimeTimeOutMinutes: 0,
                            undertimeMinutes: undertime,
                            overtimeMinutes: Number(d.ot_minutes || 0),
                            totalLateMinutesOverall,
                            totalLateFrequency,
                            withinMorningGrace: !!d.grace_used,
                            withinAfternoonGrace: false,
                            graceLateInMinutes: 0,
                            graceLateBreakInMinutes: 0,
                            totalHours: (Number(d.total_hours || 0)).toFixed(2),
                            status: mapDbStatusToUi(d.status),
                            missedLogs: Number(d.missed_logs || 0),
                            isWholeDayAbsent: String(d.status || '').toUpperCase() === 'ABSENT' || String(d.status || '').toLowerCase().includes('whole day absent')
                        };
                    });

                    periodSummary = {};
                    period.forEach(p => {
                        periodSummary[p.employee_code] = {
                            employeeName: p.employee_name || 'Unknown',
                            department: p.department || '',
                            totalLateFrequency: Number(p.late_frequency || 0),
                            totalLateMinutes: Number(p.late_duration || 0),
                            totalUndertime: Number(p.total_undertime || 0),
                            totalUndertimeFrequency: Number(p.undertime_frequency || 0),
                            missedPunches: Number(p.missed_logs_count || 0),
                            graceDays: Number(p.grace_days || 0),
                            absences: Number(p.absences || 0),
                            absenceDates: Array.isArray(p.absence_dates) ? p.absence_dates : [],
                            daysWorked: Number(p.days_worked || 0),
                            leavePaidDays: Number(p.leave_paid_days || 0),
                            leaveUnpaidDays: Number(p.leave_unpaid_days || 0),
                            mostFrequentLateTime: p.most_frequent_late_time || null,
                        };
                    });

                    monthlySummary = buildMonthlySummaryFromPeriodAndRecords(periodSummary, processedData);

                    updateDepartmentFilterOptions();
                    showRestDayNote();
                    displayResults();
                })
                .catch((err) => {
                    alert('Failed to load saved import: ' + (err?.message || err));
                });
            }

            function loadSavedSummaries(start, end) {
                const url = new URL("{{ route('time-tracking.summaries') }}", window.location.origin);
                url.searchParams.set('start', start);
                url.searchParams.set('end', end);

                return fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then((data) => {
                    const range = data?.date_range;
                    if (range?.start && range?.end) {
                        const el = document.getElementById('periodRange');
                        if (el) {
                            el.textContent = `${range.start} - ${range.end}`;
                        }
                    }

                    const daily = Array.isArray(data?.daily) ? data.daily : [];
                    const period = Array.isArray(data?.period) ? data.period : [];

                    processedData = daily.map(d => {
                        const lateIn = Number(d.late_in_minutes || 0);
                        const lateBreak = Number(d.late_break_in_minutes || 0);
                        const undertime = Number(d.undertime_break_out_minutes || 0);
                        const totalLateMinutesOverall = lateIn + lateBreak;
                        const totalLateFrequency = (lateIn > 0 ? 1 : 0) + (lateBreak > 0 ? 1 : 0);

                        return {
                            employeeId: d.employee_code,
                            employeeName: d.employee_name || 'Unknown',
                            department: d.department || '',
                            date: d.summary_date,
                            timeIn: d.time_in,
                            breakOut: d.break_out,
                            breakIn: d.break_in,
                            timeOut: d.time_out,
                            rawTimeIn: d.time_in,
                            rawBreakOut: d.break_out,
                            rawBreakIn: d.break_in,
                            rawTimeOut: d.time_out,
                            lateMinutes: lateIn,
                            lateBreakInMinutes: lateBreak,
                            undertimeBreakOutMinutes: undertime,
                            undertimeTimeOutMinutes: 0,
                            undertimeMinutes: undertime,
                            overtimeMinutes: Number(d.ot_minutes || 0),
                            totalLateMinutesOverall,
                            totalLateFrequency,
                            withinMorningGrace: !!d.grace_used,
                            withinAfternoonGrace: false,
                            graceLateInMinutes: 0,
                            graceLateBreakInMinutes: 0,
                            totalHours: (Number(d.total_hours || 0)).toFixed(2),
                            status: mapDbStatusToUi(d.status),
                            missedLogs: Number(d.missed_logs || 0),
                            isWholeDayAbsent: String(d.status || '').toUpperCase() === 'ABSENT' || String(d.status || '').toLowerCase().includes('whole day absent')
                        };
                    });

                    periodSummary = {};
                    period.forEach(p => {
                        periodSummary[p.employee_code] = {
                            employeeName: p.employee_name || 'Unknown',
                            department: p.department || '',
                            totalLateFrequency: Number(p.late_frequency || 0),
                            totalLateMinutes: Number(p.late_duration || 0),
                            totalUndertime: Number(p.total_undertime || 0),
                            totalUndertimeFrequency: Number(p.undertime_frequency || 0),
                            missedPunches: Number(p.missed_logs_count || 0),
                            graceDays: Number(p.grace_days || 0),
                            absences: Number(p.absences || 0),
                            absenceDates: Array.isArray(p.absence_dates) ? p.absence_dates : [],
                            daysWorked: Number(p.days_worked || 0),
                            leavePaidDays: Number(p.leave_paid_days || 0),
                            leaveUnpaidDays: Number(p.leave_unpaid_days || 0),
                            mostFrequentLateTime: p.most_frequent_late_time || null,
                        };
                    });

                    monthlySummary = buildMonthlySummaryFromPeriodAndRecords(periodSummary, processedData);

                    updateDepartmentFilterOptions();
                    showRestDayNote();
                    displayResults();
                })
                .catch(() => {
                    // ignore
                });
            }

            function showRestDayNote() {
                const el = document.getElementById('restDayNote');
                if (el) {
                    el.style.display = 'block';
                }
            }

            const savedBatchSelect = document.getElementById('savedBatchSelect');
            const viewSavedBtn = document.getElementById('viewSavedBtn');
            const csvFeedback = document.getElementById('csvFeedback');
            const csvFileNameEl = document.getElementById('csvFileName');

            function formatBatchLabel(b) {
                const start = b?.date_start ? String(b.date_start) : '';
                const end = b?.date_end ? String(b.date_end) : '';
                const fn = b?.source_filename ? String(b.source_filename) : 'Saved Import';
                if (start && end) return `${start} to ${end} - ${fn}`;
                return fn;
            }

            function loadImportBatchesAndMaybeAutoLoad() {
                if (!savedBatchSelect) return;

                const url = new URL("{{ route('time-tracking.import-batches') }}", window.location.origin);
                fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then((data) => {
                    const batches = Array.isArray(data?.batches) ? data.batches : [];
                    savedBatchSelect.innerHTML = '';

                    const opt0 = document.createElement('option');
                    opt0.value = '';
                    opt0.textContent = batches.length ? 'Select saved import...' : 'No saved imports yet';
                    savedBatchSelect.appendChild(opt0);

                    batches.forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = String(b.uuid || '');
                        opt.textContent = formatBatchLabel(b);
                        savedBatchSelect.appendChild(opt);
                    });

                    savedBatchSelect.disabled = batches.length === 0;

                    const params = new URLSearchParams(window.location.search);
                    const batchParam = String(params.get('batch') || '');

                    const toLoad = batchParam || (batches.length ? String(batches[0].uuid || '') : '');
                    if (toLoad) {
                        savedBatchSelect.value = toLoad;
                        const url = new URL(window.location.href);
                        url.searchParams.set('batch', toLoad);
                        url.searchParams.delete('saved');
                        history.replaceState({}, '', url.toString());
                        loadSavedSummariesByBatch(toLoad);
                    }
                })
                .catch(() => {
                    savedBatchSelect.innerHTML = '<option value="">Failed to load</option>';
                    savedBatchSelect.disabled = true;
                });
            }

            if (savedBatchSelect) {
                savedBatchSelect.addEventListener('change', function () {
                    const uuid = String(savedBatchSelect.value || '');
                    if (uuid) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('batch', uuid);
                        history.replaceState({}, '', url.toString());
                        loadSavedSummariesByBatch(uuid);
                    }
                });
            }

            if (viewSavedBtn) {
                viewSavedBtn.addEventListener('click', function () {
                    if (!savedBatchSelect) return;
                    const uuid = String(savedBatchSelect.value || '');
                    if (uuid) {
                        loadSavedSummariesByBatch(uuid);
                        return;
                    }
                    const url = new URL(window.location.href);
                    url.searchParams.set('saved', '1');
                    history.replaceState({}, '', url.toString());
                    loadImportBatchesAndMaybeAutoLoad();
                });
            }

            loadImportBatchesAndMaybeAutoLoad();

            function mapDbStatusToUi(status) {
                // AttendanceDailySummary.status is stored as the detailed UI status text.
                // Backward-compatible with legacy codes (ON_TIME/LATE/UNDERTIME/MISSED_LOG/ABSENT).
                const raw = String(status || '');
                const s = raw.toUpperCase();
                if (s === 'ON_TIME') return 'Ontime';
                if (s === 'LATE') return 'Late';
                if (s === 'UNDERTIME') return 'Undertime';
                if (s === 'MISSED_LOG') return 'Incomplete Logs';
                if (s === 'ABSENT') return 'Whole Day Absent';
                return raw;
            }

            function parseCSVLine(line, regex) {
                const matches = line.match(regex);
                return matches ? matches.map(m => m.replace(/^\"|\"$/g, '').replace(/\"\"/g, '"')) : null;
            }

            function getColumnIndex(headerCells, names) {
                const normalized = headerCells.map(c => c.trim().toLowerCase().replace(/\s+/g, ' '));
                for (const name of names) {
                    const n = name.replace(/\s+/g, ' ');
                    const idx = normalized.findIndex(c => c === n);
                    if (idx >= 0) return idx;
                }
                return -1;
            }

            function parseCSV(csvContent) {
                employeeData = [];
                const lines = csvContent.split(/\r?\n/).filter(l => l.trim() !== '');
                if (lines.length < 2) return;

                const regex = /(".*?"|[^",]+)(?=\s*,|\s*$)/g;
                const headerCells = parseCSVLine(lines[0], regex);
                if (!headerCells || headerCells.length < 6) return;

                const idx = {
                    employeeId: getColumnIndex(headerCells, ['employee id', 'employeeid', 'emp id', 'id']),
                    department: getColumnIndex(headerCells, ['department', 'dept']),
                    employeeName: getColumnIndex(headerCells, ['employee name', 'employeename', 'name']),
                    time: getColumnIndex(headerCells, ['time']),
                    date: getColumnIndex(headerCells, ['date']),
                    activity: getColumnIndex(headerCells, ['activity']),
                    remarks: getColumnIndex(headerCells, ['remarks', 'image', 'address', 'location'])
                };

                const useHeader = idx.employeeId >= 0 && idx.department >= 0 && idx.employeeName >= 0 && idx.time >= 0 && idx.date >= 0 && idx.activity >= 0;

                const validSet = new Set((VALID_EMPLOYEE_CODES || []).map(c => String(c || '').trim()).filter(Boolean));

                for (let i = 1; i < lines.length; i++) {
                    const cells = parseCSVLine(lines[i], regex);
                    if (!cells || cells.length < 6) continue;

                    const get = (key) => {
                        const i = useHeader ? idx[key] : { employeeId: 0, department: 1, employeeName: 2, time: 3, date: 4, activity: 5, remarks: 6 }[key];
                        return (i >= 0 && cells[i] !== undefined) ? cells[i].trim() : '';
                    };
                    const timeVal = get('time');
                    const dateVal = get('date');
                    const activityVal = get('activity');
                    if (!timeVal || !dateVal) continue;

                    const record = {
                        employeeId: get('employeeId') || 'Unknown',
                        department: normalizeDepartment(get('department') || ''),
                        employeeName: get('employeeName') || 'Unknown',
                        time: timeVal,
                        date: normalizeDateForGrouping(dateVal),
                        activity: normalizeActivity(activityVal),
                        remarks: ((useHeader && idx.remarks >= 0 ? cells[idx.remarks] : cells[6]) || '').trim()
                    };

                    const empCode = String(record.employeeId || '').trim();
                    if (validSet.size > 0 && (!empCode || !validSet.has(empCode))) {
                        continue;
                    }

                    employeeData.push(record);
                }

                employeeData = dedupeExactRecords(employeeData);
            }

            function normalizeDateForGrouping(dateStr) {
                if (!dateStr || !dateStr.trim()) return dateStr;
                const s = dateStr.trim();
                const mmddyy = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                if (mmddyy) {
                    const month = parseInt(mmddyy[1], 10), day = parseInt(mmddyy[2], 10), year = parseInt(mmddyy[3], 10);
                    if (month >= 1 && month <= 12 && day >= 1 && day <= 31) {
                        return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    }
                }
                const d = new Date(s);
                if (Number.isNaN(d.getTime())) return dateStr;
                const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            }

            function processData() {
                processedData = [];
                monthlySummary = {};

                const groupedData = {};

                employeeData.forEach(record => {
                    const key = `${record.employeeId}-${record.date}`;
                    if (!groupedData[key]) {
                        groupedData[key] = {
                            employeeId: record.employeeId,
                            employeeName: record.employeeName,
                            department: record.department,
                            date: record.date,
                            timeIn: null,
                            timeOut: null,
                            activities: []
                        };
                    }

                    if (!record.activity) return;
                    groupedData[key].activities.push({
                        time: record.time,
                        activity: record.activity,
                        remarks: record.remarks
                    });
                });

                for (const key in groupedData) {
                    const data = groupedData[key];
                    let activities = data.activities
                        .map(activity => ({
                            ...activity,
                            minutes: timeToMinutes(activity.time)
                        }))
                        .sort((a, b) => {
                            if (a.minutes === null && b.minutes === null) {
                                return a.time.localeCompare(b.time);
                            }
                            if (a.minutes === null) return 1;
                            if (b.minutes === null) return -1;
                            return a.minutes - b.minutes;
                        });

                    activities = dedupeNearbyPunches(activities);

                    const slots = selectDailySlots(activities);
                    const morningIn = slots.morningIn;
                    const morningOut = slots.morningOut;
                    const afternoonIn = slots.afternoonIn;
                    const afternoonOut = slots.afternoonOut;

                    if (morningIn || morningOut || afternoonIn || afternoonOut) {
                        data.timeIn = morningIn ? morningIn.time : null;
                        data.breakOut = morningOut ? morningOut.time : null;
                        data.breakIn = afternoonIn ? afternoonIn.time : null;
                        data.timeOut = afternoonOut ? afternoonOut.time : null;

                        const metrics = calculateMetrics(
                            data.date,
                            data.timeIn,
                            data.breakOut,
                            data.breakIn,
                            data.timeOut,
                            data.department
                        );

                        const processedRecord = {
                            ...data,
                            ...metrics,
                            rawTimeIn: data.timeIn,
                            rawBreakOut: data.breakOut,
                            rawBreakIn: data.breakIn,
                            rawTimeOut: data.timeOut
                        };

                        processedData.push(processedRecord);

                        if (!monthlySummary[data.employeeId]) {
                            monthlySummary[data.employeeId] = {
                                employeeName: data.employeeName,
                                department: data.department,
                                totalLateFrequency: 0,
                                totalLateMinutes: 0,
                                totalUndertime: 0,
                                totalUndertimeFrequency: 0,
                                missedLogDays: 0,
                                missedPunches: 0,
                                absentAMCount: 0,
                                absentPMCount: 0,
                                wholeDayAbsentCount: 0,
                                halfDayCount: 0,
                                lateTimes: []
                            };
                        }

                        const missingPunches = [data.timeIn, data.breakOut, data.breakIn, data.timeOut].filter(v => !v).length;
                        if (!processedRecord.isWholeDayAbsent && missingPunches > 0) {
                            monthlySummary[data.employeeId].missedLogDays += 1;
                            monthlySummary[data.employeeId].missedPunches += missingPunches;
                        }

                        if (metrics.totalLateFrequency > 0) {
                            monthlySummary[data.employeeId].totalLateFrequency += metrics.totalLateFrequency;
                            monthlySummary[data.employeeId].totalLateMinutes += metrics.totalLateMinutesOverall;
                            const timeInMinutes = timeToMinutes(data.timeIn);
                            if (timeInMinutes !== null) {
                                monthlySummary[data.employeeId].lateTimes.push(timeInMinutes);
                            }
                        }

                        if (metrics.undertimeMinutes > 0) {
                            monthlySummary[data.employeeId].totalUndertime += metrics.undertimeMinutes;
                            monthlySummary[data.employeeId].totalUndertimeFrequency += 1;
                        }

                        const statusText = (metrics.status || '').toLowerCase();
                        if (statusText.includes('absent am')) monthlySummary[data.employeeId].absentAMCount += 1;
                        if (statusText.includes('absent pm')) monthlySummary[data.employeeId].absentPMCount += 1;
                        if (statusText.includes('whole day absent')) monthlySummary[data.employeeId].wholeDayAbsentCount += 1;
                        if (statusText.includes('half day')) monthlySummary[data.employeeId].halfDayCount += 1;
                    }
                }

                processedData.sort((a, b) => {
                    const nameCompare = a.employeeName.localeCompare(b.employeeName, undefined, { sensitivity: 'base' });
                    if (nameCompare !== 0) return nameCompare;
                    return new Date(a.date) - new Date(b.date);
                });
            }

            function addMinutesToTimeString(timeStr, minutesToAdd) {
                const m = String(timeStr || '').match(/^(\d{1,2}):(\d{2}):(\d{2})$/);
                if (!m) return timeStr;
                const hh = parseInt(m[1], 10);
                const mm = parseInt(m[2], 10);
                const ss = parseInt(m[3], 10);
                const base = hh * 60 + mm + (ss >= 30 ? 1 : 0);
                const next = (base + minutesToAdd + 24 * 60) % (24 * 60);
                const nh = Math.floor(next / 60);
                const nm = next % 60;
                return `${pad2(nh)}:${pad2(nm)}:00`;
            }

            function setTimeStringSeconds(timeStr, seconds) {
                const m = String(timeStr || '').match(/^(\d{1,2}):(\d{2}):(\d{2})$/);
                if (!m) return timeStr;
                const hh = pad2(parseInt(m[1], 10));
                const mm = pad2(parseInt(m[2], 10));
                const ss = pad2(Math.max(0, Math.min(59, parseInt(seconds, 10) || 0)));
                return `${hh}:${mm}:${ss}`;
            }

            function normalizeDepartmentForSchedule(dept) {
                return String(dept || '').trim().toLowerCase().replace(/\s+/g, ' ');
            }

            function getScheduleForDepartment(department) {
                const d = normalizeDepartmentForSchedule(department);

                if (d && DEPARTMENT_SCHEDULES && DEPARTMENT_SCHEDULES[d]) {
                    const sched = DEPARTMENT_SCHEDULES[d] || {};
                    const start = sched.start || WORK_START_TIME;
                    const end = sched.end || '17:00:00';
                    return { start, end };
                }

                return { start: WORK_START_TIME, end: '17:00:00' };
            }

            function calculateMetrics(date, timeIn, breakOut, breakIn, timeOut, department) {
                const schedule = getScheduleForDepartment(department);
                const scheduleStart = schedule.start;
                const scheduleEnd = schedule.end;
                const morningLateStartStr = addMinutesToTimeString(scheduleStart, 16);
                const morningGraceEndStr = setTimeStringSeconds(addMinutesToTimeString(scheduleStart, 15), 59);
                const afternoonLateStartStr = addMinutesToTimeString(BREAK_IN_TIME, 16);
                const afternoonGraceEndStr = setTimeStringSeconds(addMinutesToTimeString(BREAK_IN_TIME, 15), 59);

                const workStart = createDateTime(date, scheduleStart);
                const morningLateStart = createDateTime(date, morningLateStartStr);
                const morningGraceEnd = createDateTime(date, morningGraceEndStr);
                const breakOutTime = createDateTime(date, BREAK_OUT_TIME);
                const breakOutGraceEnd = createDateTime(date, BREAK_OUT_GRACE_END);
                const breakInEarliest = createDateTime(date, BREAK_IN_EARLIEST);
                const breakInTime = createDateTime(date, BREAK_IN_TIME);
                const afternoonLateStart = createDateTime(date, afternoonLateStartStr);
                const afternoonGraceEnd = createDateTime(date, afternoonGraceEndStr);
                const workEnd = createDateTime(date, scheduleEnd);
                const actualStart = createDateTime(date, timeIn);
                const actualBreakOut = breakOut ? createDateTime(date, breakOut) : null;
                const actualBreakIn = breakIn ? createDateTime(date, breakIn) : null;
                const actualEnd = createDateTime(date, timeOut);

                const missingPunchesCount = [timeIn, breakOut, breakIn, timeOut].filter(v => !v).length;

                if (!workStart || !workEnd) {
                    return {
                        lateMinutes: 0,
                        undertimeBreakOutMinutes: 0,
                        undertimeTimeOutMinutes: 0,
                        lateBreakInMinutes: 0,
                        overtimeMinutes: 0,
                        totalLateFrequency: 0,
                        totalLateMinutesOverall: 0,
                        undertimeMinutes: 0,
                        lunchDeduction: '1 hour',
                        totalHours: '0.00',
                        status: 'Invalid Time'
                    };
                }

                const absentAM = !actualStart && !actualBreakOut;
                const absentPM = !actualBreakIn && !actualEnd;
                const hasTimeIn = !!actualStart;
                const hasTimeOut = !!actualEnd;
                const halfDayIncomplete = (hasTimeIn && !hasTimeOut) || (!hasTimeIn && hasTimeOut);

                let lateMinutes = 0;
                let graceLateInMinutes = 0;
                let withinMorningGrace = false;
                if (actualStart && workStart && morningGraceEnd && actualStart > workStart && actualStart <= morningGraceEnd) {
                    withinMorningGrace = true;
                    graceLateInMinutes = Math.round((actualStart - workStart) / (1000 * 60));
                } else if (actualStart && morningLateStart && actualStart >= morningLateStart) {
                    lateMinutes = Math.round((actualStart - workStart) / (1000 * 60));
                }

                let undertimeBreakOutMinutes = 0;
                let earlyBreakOutAllowanceMinutes = 0;
                if (actualBreakOut && breakOutTime) {
                    const diffMin = Math.round((breakOutTime - actualBreakOut) / (1000 * 60));
                    if (diffMin > 0) {
                        if (diffMin <= EARLY_OUT_ALLOWANCE_MINUTES) {
                            earlyBreakOutAllowanceMinutes = diffMin;
                            undertimeBreakOutMinutes = 0;
                        } else {
                            undertimeBreakOutMinutes = diffMin;
                        }
                    }
                }

                let lateBreakInMinutes = 0;
                let graceLateBreakInMinutes = 0;
                let withinAfternoonGrace = false;
                if (actualBreakIn && breakInTime && afternoonGraceEnd && actualBreakIn > breakInTime && actualBreakIn <= afternoonGraceEnd) {
                    withinAfternoonGrace = true;
                    graceLateBreakInMinutes = Math.round((actualBreakIn - breakInTime) / (1000 * 60));
                } else if (actualBreakIn && afternoonLateStart && actualBreakIn >= afternoonLateStart) {
                    lateBreakInMinutes = Math.round((actualBreakIn - breakInTime) / (1000 * 60));
                }

                let earlyBreakInMinutes = 0;
                if (actualBreakIn && breakInEarliest && actualBreakIn < breakInEarliest) {
                    earlyBreakInMinutes = Math.round((breakInEarliest - actualBreakIn) / (1000 * 60));
                }

                let overtimeMinutes = 0;
                if (actualEnd && actualEnd > workEnd) {
                    overtimeMinutes = Math.round((actualEnd - workEnd) / (1000 * 60));
                }

                const totalLateMinutesOverall = lateMinutes + lateBreakInMinutes;
                const totalLateFrequency = (lateMinutes > 0 ? 1 : 0) + (lateBreakInMinutes > 0 ? 1 : 0);

                let undertimeTimeOutMinutes = 0;
                let earlyTimeOutAllowanceMinutes = 0;
                if (actualEnd && workEnd) {
                    const diffMin = Math.round((workEnd - actualEnd) / (1000 * 60));
                    if (diffMin > 0) {
                        if (diffMin <= EARLY_OUT_ALLOWANCE_MINUTES) {
                            earlyTimeOutAllowanceMinutes = diffMin;
                            undertimeTimeOutMinutes = 0;
                        } else {
                            undertimeTimeOutMinutes = diffMin;
                        }
                    }
                }
                const undertimeMinutes = undertimeBreakOutMinutes + undertimeTimeOutMinutes;

                let totalHours = '0.00';
                if (actualStart && actualEnd) {
                    const totalMinutes = Math.round((actualEnd - actualStart) / (1000 * 60));
                    const netMinutes = Math.max(totalMinutes - LUNCH_DURATION, 0);
                    totalHours = (netMinutes / 60).toFixed(2);
                }

                let status;
                if (absentAM || absentPM) {
                    status = (absentAM && absentPM)
                        ? 'Whole Day Absent'
                        : `${absentAM ? 'Absent AM' : ''}${absentAM && absentPM ? ' / ' : ''}${absentPM ? 'Absent PM' : ''}`.trim();
                } else if (missingPunchesCount === 1) {
                    status = 'Incomplete Logs';
                } else if (halfDayIncomplete) {
                    status = 'Half Day (Incomplete Logs)';
                } else {
                    status = 'Ontime';
                    if (totalLateMinutesOverall > 0 && undertimeMinutes > 0) status = 'Late & Undertime';
                    else if (totalLateMinutesOverall > 0) status = 'Late';
                    else if (undertimeMinutes > 0) status = 'Undertime';
                }

                return {
                    lateMinutes,
                    undertimeBreakOutMinutes,
                    undertimeTimeOutMinutes,
                    lateBreakInMinutes,
                    earlyBreakInMinutes,
                    overtimeMinutes,
                    totalLateFrequency,
                    totalLateMinutesOverall,
                    undertimeMinutes,
                    withinMorningGrace,
                    withinAfternoonGrace,
                    graceLateInMinutes,
                    graceLateBreakInMinutes,
                    earlyBreakOutAllowanceMinutes,
                    earlyTimeOutAllowanceMinutes,
                    lunchDeduction: '1 hour',
                    totalHours,
                    status
                };
            }

            function displayResults() {
                const scopedRecords = getScopedProcessedData();
                const filteredRecords = getFilteredProcessedData();
                const filteredMonthlySummary = getFilteredMonthlySummary();

                document.getElementById('totalEmployees').textContent =
                    Object.keys(filteredMonthlySummary).length;

                const totalLateCount = scopedRecords.reduce((sum, d) => sum + d.totalLateFrequency, 0);
                document.getElementById('totalLateCount').textContent = totalLateCount;

                const avgLateMinutes = scopedRecords.filter(d => d.totalLateMinutesOverall > 0)
                    .reduce((sum, d) => sum + d.totalLateMinutesOverall, 0) / (totalLateCount || 1);
                document.getElementById('avgLateMinutes').textContent = avgLateMinutes.toFixed(1);

                const uniqueDays = [...new Set(scopedRecords.map(d => d.date))].length;
                document.getElementById('daysAnalyzed').textContent = uniqueDays;

                tableBody.innerHTML = '';
                if (filteredRecords.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = `<td colspan="18" class="text-muted fst-italic">No data yet. Upload a CSV to view Daily Time Records.</td>`;
                    tableBody.appendChild(emptyRow);
                }
                filteredRecords.forEach(record => {
                    const row = document.createElement('tr');

                    let statusBadge = '';
                    if (record.status === 'Late') {
                        statusBadge = '<span class="badge badge-late">Late</span>';
                    } else if (record.status === 'Undertime') {
                        statusBadge = '<span class="badge badge-undertime">Undertime</span>';
                    } else if (record.status === 'Late & Undertime') {
                        statusBadge = '<span class="badge badge-late me-1">Late</span><span class="badge badge-undertime">Undertime</span>';
                    } else if (record.status && (record.status.startsWith('Absent') || record.status === 'Whole Day Absent')) {
                        statusBadge = '<span class="badge bg-secondary">' + record.status + '</span>';
                    } else {
                        statusBadge = '<span class="badge badge-ontime">Ontime</span>';
                    }
                    const missed = '<span class="text-muted fst-italic">Missed log</span>';
                    const fmt = (val) => val ? val : missed;
                    const fmtBreakIn = (val) => {
                        if (!val) return missed;
                        if ((record.earlyBreakInMinutes || 0) > 0) return `${val} <span class="text-muted">(Early In)</span>`;
                        return val;
                    };
                    const missedCount = record.isWholeDayAbsent ? 0 : [record.rawTimeIn, record.rawBreakOut, record.rawBreakIn, record.rawTimeOut].filter(v => !v).length;
                    const missedBadge = (record.isWholeDayAbsent || missedCount === 0 || missedCount === 4) ? '-' : `<span class="badge bg-secondary">${missedCount}</span>`;
                    const timeInClass = record.rawTimeIn
                        ? (record.lateMinutes > 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold')
                        : '';
                    const breakOutClass = record.rawBreakOut
                        ? (record.undertimeBreakOutMinutes > 0 ? 'text-warning fw-semibold' : 'text-success fw-semibold')
                        : '';
                    const breakInClass = record.rawBreakIn
                        ? (record.lateBreakInMinutes > 0 ? 'text-danger fw-semibold' : ((record.earlyBreakInMinutes || 0) > 0 ? 'text-primary fw-semibold' : 'text-success fw-semibold'))
                        : '';
                    const timeOutClass = record.rawTimeOut
                        ? (record.undertimeTimeOutMinutes > 0 ? 'text-warning fw-semibold' : 'text-success fw-semibold')
                        : '';

                    const graceParts = [];
                    if (record.withinMorningGrace) graceParts.push('Time In');
                    if (record.withinAfternoonGrace) graceParts.push('Break Out');
                    const graceUsed = graceParts.length ? graceParts.join(' + ') : '-';

                    row.innerHTML = `
                        <td>${record.employeeId}</td>
                        <td class="employee-name">${record.employeeName}</td>
                        <td>${record.department}</td>
                        <td>${formatDateMMDDYYYY(record.date)}</td>
                        <td class="${timeInClass}">${fmt(record.rawTimeIn)}</td>
                        <td class="${breakOutClass}">${fmt(record.rawBreakOut)}</td>
                        <td class="${breakInClass}">${fmtBreakIn(record.rawBreakIn)}</td>
                        <td class="${timeOutClass}">${fmt(record.rawTimeOut)}</td>
                        <td>${graceUsed}</td>
                        <td class="late-minutes">${record.lateMinutes > 0 ? record.lateMinutes : '-'}</td>
                        <td class="undertime-minutes">${record.undertimeBreakOutMinutes > 0 ? record.undertimeBreakOutMinutes : '-'}</td>
                        <td class="late-minutes">${record.lateBreakInMinutes > 0 ? record.lateBreakInMinutes : '-'}</td>
                        <td class="late-minutes">${record.overtimeMinutes > 0 ? record.overtimeMinutes : '-'}</td>
                        <td class="late-minutes">${record.totalLateMinutesOverall > 0 ? record.totalLateMinutesOverall : '-'}</td>
                        <td class="undertime-minutes">${record.undertimeMinutes > 0 ? record.undertimeMinutes : '-'}</td>
                        <td class="hours-worked">${record.totalHours}</td>
                        <td>${missedBadge}</td>
                        <td>${statusBadge}</td>
                    `;
                    tableBody.appendChild(row);
                });

                monthlyBody.innerHTML = '';
                const monthlyRows = Object.entries(filteredMonthlySummary)
                    .filter(([empId, summary]) => {
                        if (!monthlySearchQuery) return true;
                        const q = monthlySearchQuery.toLowerCase();
                        const haystack = [empId, summary.employeeName, summary.department].join(' ').toLowerCase();
                        return haystack.includes(q);
                    })
                    .sort(([, a], [, b]) => a.employeeName.localeCompare(b.employeeName, undefined, { sensitivity: 'base' }));
                if (monthlyRows.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = `<td colspan="15" class="text-muted fst-italic">No data yet. Upload a CSV to view Monthly Summary.</td>`;
                    monthlyBody.appendChild(emptyRow);
                }
                for (const [empId, summary] of monthlyRows) {
                    const avgLate = summary.totalLateFrequency > 0
                        ? (summary.totalLateMinutes / summary.totalLateFrequency).toFixed(1)
                        : '0';
                    const lateFrequency = summary.totalLateFrequency;

                    let mostFrequentLate = '-';
                    if (summary.lateTimes.length > 0) {
                        const timeCounts = {};
                        summary.lateTimes.forEach(minutes => {
                            const hour = Math.floor(minutes / 60);
                            const hourKey = pad2(hour);
                            timeCounts[hourKey] = (timeCounts[hourKey] || 0) + 1;
                        });

                        const mostFrequent = Object.entries(timeCounts)
                            .sort((a, b) => b[1] - a[1])[0];

                        if (mostFrequent) {
                            const startHour = parseInt(mostFrequent[0], 10);
                            const endHour = (startHour + 1) % 24;
                            mostFrequentLate = `${pad2(startHour)}:00 - ${pad2(endHour)}:00`;
                        }
                    }

                    const row = document.createElement('tr');
                    row.classList.add('clickable-row');
                    row.title = "Click to view this employee's daily time records";
                    const totalAbsences = Number(summary.absences || 0);
                    const absencesDisplay = Number.isInteger(totalAbsences) ? String(totalAbsences.toFixed(0)) : totalAbsences.toFixed(1);

                    const daysWorked = Number(summary.daysWorked || 0);
                    const daysWorkedDisplay = Number.isInteger(daysWorked)
                        ? String(daysWorked.toFixed(0))
                        : daysWorked.toFixed(1);

                    const leavePaidDays = Number(summary.leavePaidDays || 0);
                    const leavePaidDaysDisplay = Number.isInteger(leavePaidDays)
                        ? String(leavePaidDays.toFixed(0))
                        : leavePaidDays.toFixed(1);

                    const leaveUnpaidDays = Number(summary.leaveUnpaidDays || 0);
                    const leaveUnpaidDaysDisplay = Number.isInteger(leaveUnpaidDays)
                        ? String(leaveUnpaidDays.toFixed(0))
                        : leaveUnpaidDays.toFixed(1);

                    const undertimeFrequency = summary.totalUndertimeFrequency || 0;
                    const eligibleLate = lateFrequency > 3 || summary.totalUndertimeFrequency > 3;
                    const eligibleMissed = (summary.missedLogDays || 0) > 0;
                    const showLetter = eligibleLate || eligibleMissed;

                    const letterMenuItems = [
                        eligibleLate ? `<li><a class="dropdown-item" href="#" onclick="openLetterModal(event, '${empId}', 'late')">Late only</a></li>` : '',
                        eligibleMissed ? `<li><a class="dropdown-item" href="#" onclick="openLetterModal(event, '${empId}', 'missed')">Missed logs only</a></li>` : '',
                        (eligibleLate && eligibleMissed) ? `<li><a class="dropdown-item" href="#" onclick="openLetterModal(event, '${empId}', 'both')">Late + Missed logs</a></li>` : ''
                    ].filter(Boolean).join('');

                    const letterCell = showLetter
                        ? `
                            <div class="letter-action dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Create Letter
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    ${letterMenuItems}
                                </ul>
                            </div>
                          `
                        : '-';
                    const missedLogsDisplay = String(summary.missedPunches || 0);
                    row.innerHTML = `
                        <td class="employee-name">${summary.employeeName}</td>
                        <td>${summary.department}</td>
                        <td><span class="fw-bold">${lateFrequency}</span></td>
                        <td><span class="fw-bold">${missedLogsDisplay}</span></td>
                        <td><span class="fw-bold">${summary.graceDays || 0}</span></td>
                        <td><span class="fw-bold">${absencesDisplay}</span></td>
                        <td><span class="fw-bold">${daysWorkedDisplay}</span></td>
                        <td>${summary.totalLateMinutes} min</td>
                        <td>${avgLate} min</td>
                        <td>${summary.totalUndertime} min</td>
                        <td><span class="fw-bold">${undertimeFrequency}</span></td>
                        <td>${mostFrequentLate}</td>
                        <td><span class="fw-bold">${leavePaidDaysDisplay}</span></td>
                        <td><span class="fw-bold">${leaveUnpaidDaysDisplay}</span></td>
                        <td>${letterCell}</td>
                    `;
                    row.addEventListener('click', (e) => {
                        if (e.target && e.target.closest && e.target.closest('.letter-action')) return;
                        openEmployeeDailyModal(empId, summary);
                    });
                    monthlyBody.appendChild(row);
                }

                hideLoading();
                summarySection.style.display = 'flex';
                detailsSection.style.display = 'block';
            }

            function isWithinDateRange(isoDate) {
                if (!isoDate) return false;
                if (dateStart && isoDate < dateStart) return false;
                if (dateEnd && isoDate > dateEnd) return false;
                return true;
            }

            function getScopedProcessedData() {
                let records = processedData;
                if (selectedDepartment !== 'ALL') {
                    const selectedDeptNorm = normalizeDepartmentForSchedule(selectedDepartment);
                    records = records.filter(record => normalizeDepartmentForSchedule(record.department) === selectedDeptNorm);
                }
                if (dateStart || dateEnd) {
                    records = records.filter(record => isWithinDateRange(record.date));
                }
                return records;
            }

            function getFilteredProcessedData() {
                let records = getScopedProcessedData();
                if (!dailySearchQuery) return records;

                const q = dailySearchQuery.toLowerCase();
                return records.filter(record => {
                    const haystack = [
                        record.employeeId,
                        record.employeeName,
                        record.department,
                        formatDateMMDDYYYY(record.date)
                    ]
                        .join(' ')
                        .toLowerCase();
                    return haystack.includes(q);
                });
            }

            function getFilteredMonthlySummary() {
                // Prefer schedule-aware monthly summary (API `period`) so employees with zero logs still appear.
                // Fallback to legacy record-based aggregation when `periodSummary` isn't available (e.g. CSV-only flow).
                const hasPeriod = periodSummary && Object.keys(periodSummary).length > 0;
                if (!hasPeriod) {
                    return buildMonthlySummaryFromRecords(getScopedProcessedData());
                }

                const scopedRecords = getScopedProcessedData();
                const scoped = buildMonthlySummaryFromPeriodAndRecords(periodSummary, scopedRecords);

                if (selectedDepartment === 'ALL') return scoped;

                const selectedDeptNorm = normalizeDepartmentForSchedule(selectedDepartment);
                const filtered = {};
                for (const [empId, summary] of Object.entries(scoped || {})) {
                    if (normalizeDepartmentForSchedule(summary.department) === selectedDeptNorm) {
                        filtered[empId] = summary;
                    }
                }
                return filtered;
            }

            function buildMonthlySummaryFromPeriodAndRecords(periodByEmployeeId, records) {
                const summary = {};
                const entries = Object.entries(periodByEmployeeId || {});
                entries.forEach(([empId, p]) => {
                    summary[empId] = {
                        employeeName: p.employeeName || 'Unknown',
                        department: p.department || '',
                        totalLateFrequency: Number(p.totalLateFrequency || 0),
                        totalLateMinutes: Number(p.totalLateMinutes || 0),
                        totalUndertime: Number(p.totalUndertime || 0),
                        totalUndertimeFrequency: Number(p.totalUndertimeFrequency || 0),
                        missedLogDays: 0,
                        missedPunches: Number(p.missedPunches || 0),
                        graceDays: Number(p.graceDays || 0),
                        absences: Number(p.absences || 0),
                        daysWorked: Number(p.daysWorked || 0),
                        leavePaidDays: Number(p.leavePaidDays || 0),
                        leaveUnpaidDays: Number(p.leaveUnpaidDays || 0),
                        lateTimes: [],
                    };
                });

                // Enrich from daily records for letter + late time distribution + missed logs.
                (records || []).forEach(r => {
                    if (!summary[r.employeeId]) {
                        summary[r.employeeId] = {
                            employeeName: r.employeeName,
                            department: r.department,
                            totalLateFrequency: 0,
                            totalLateMinutes: 0,
                            totalUndertime: 0,
                            totalUndertimeFrequency: 0,
                            missedLogDays: 0,
                            missedPunches: 0,
                            graceDays: 0,
                            absences: 0,
                            daysWorked: 0,
                            leavePaidDays: 0,
                            leaveUnpaidDays: 0,
                            lateTimes: [],
                        };
                    }

                    const s = summary[r.employeeId];
                    const missingPunches = [r.rawTimeIn, r.rawBreakOut, r.rawBreakIn, r.rawTimeOut].filter(v => !v).length;
                    if (!r.isWholeDayAbsent && missingPunches > 0) {
                        s.missedLogDays += 1;
                    }

                    if ((r.lateMinutes || 0) > 0 && r.rawTimeIn) {
                        const m = timeToMinutes(r.rawTimeIn);
                        if (m !== null) s.lateTimes.push(m);
                    }
                    if ((r.lateBreakInMinutes || 0) > 0 && r.rawBreakIn) {
                        const m = timeToMinutes(r.rawBreakIn);
                        if (m !== null) s.lateTimes.push(m);
                    }
                });

                return summary;
            }

            function buildMonthlySummaryFromRecords(records) {
                const summary = {};
                records.forEach(r => {
                    if (!summary[r.employeeId]) {
                        summary[r.employeeId] = {
                            employeeName: r.employeeName,
                            department: r.department,
                            totalLateFrequency: 0,
                            totalLateMinutes: 0,
                            totalUndertime: 0,
                            totalUndertimeFrequency: 0,
                            missedLogDays: 0,
                            missedPunches: 0,
                            graceDays: 0,
                            daysWorked: 0,
                            absentAMCount: 0,
                            absentPMCount: 0,
                            wholeDayAbsentCount: 0,
                            halfDayCount: 0,
                            lateTimes: []
                        };
                    }

                    const s = summary[r.employeeId];
                    s.totalLateFrequency += (r.totalLateFrequency || 0);
                    s.totalLateMinutes += (r.totalLateMinutesOverall || 0);
                    s.totalUndertime += (r.undertimeMinutes || 0);
                    if ((r.undertimeMinutes || 0) > 0) {
                        s.totalUndertimeFrequency += 1;
                    }
                    const missingPunches = [r.rawTimeIn, r.rawBreakOut, r.rawBreakIn, r.rawTimeOut].filter(v => !v).length;
                    if (!r.isWholeDayAbsent && missingPunches > 0) {
                        s.missedLogDays += 1;
                        s.missedPunches += missingPunches;
                    }

                    if (!r.isWholeDayAbsent && (r.withinMorningGrace || r.withinAfternoonGrace)) {
                        s.graceDays += 1;
                    }

                    const statusText = (r.status || '').toLowerCase();
                    if (statusText.includes('absent am')) s.absentAMCount += 1;
                    if (statusText.includes('absent pm')) s.absentPMCount += 1;
                    if (statusText.includes('whole day absent')) s.wholeDayAbsentCount += 1;
                    if (statusText.includes('half day')) s.halfDayCount += 1;

                    if (r.isWholeDayAbsent || statusText.includes('whole day absent')) {
                        s.daysWorked += 0;
                    } else if (statusText.includes('absent am') || statusText.includes('absent pm') || statusText.includes('half day')) {
                        s.daysWorked += 0.5;
                    } else {
                        s.daysWorked += 1;
                    }

                    if ((r.lateMinutes || 0) > 0 && r.rawTimeIn) {
                        const m = timeToMinutes(r.rawTimeIn);
                        if (m !== null) s.lateTimes.push(m);
                    }
                    if ((r.lateBreakInMinutes || 0) > 0 && r.rawBreakIn) {
                        const m = timeToMinutes(r.rawBreakIn);
                        if (m !== null) s.lateTimes.push(m);
                    }
                });
                return summary;
            }

            function updateDepartmentFilterOptions() {
                const previousSelection = selectedDepartment;
                const departments = [...new Set(processedData.map(d => d.department).filter(Boolean))]
                    .sort((a, b) => a.localeCompare(b));

                departmentFilter.innerHTML = '<option value="ALL">All Departments</option>';
                departmentFilterSummary.innerHTML = '<option value="ALL">All Departments</option>';
                departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    departmentFilter.appendChild(option);

                    const optionSummary = document.createElement('option');
                    optionSummary.value = dept;
                    optionSummary.textContent = dept;
                    departmentFilterSummary.appendChild(optionSummary);
                });

                selectedDepartment = departments.includes(previousSelection) ? previousSelection : 'ALL';
                departmentFilter.value = selectedDepartment;
                departmentFilterSummary.value = selectedDepartment;
            }

            function applyDailySearch() {
                dailySearchQuery = (dailySearchInput.value || '').trim().toLowerCase();
                displayResults();
            }

            function applyMonthlySearch() {
                monthlySearchQuery = (monthlySearchInput.value || '').trim().toLowerCase();
                displayResults();
            }

            function openEmployeeDailyModal(empId, summary) {
                if (!employeeDailyModalEl || typeof bootstrap === 'undefined') return;

                const records = getScopedProcessedData()
                    .filter(r => r.employeeId === empId)
                    .sort((a, b) => new Date(a.date) - new Date(b.date));

                const deptLabel = selectedDepartment === 'ALL' ? 'All Departments' : selectedDepartment;
                employeeDailyModalLabel.textContent = `Daily Time Records — ${summary.employeeName} (${empId})`;
                const absDates = Array.isArray(summary.absenceDates) ? summary.absenceDates : [];
                const absLabel = absDates.length ? absDates.join(', ') : '-';
                employeeDailyModalMeta.textContent = `Department Filter: ${deptLabel} | Absence Dates (no logs): ${absLabel}`;

                employeeDailyModalBody.innerHTML = '';
                if (records.length === 0) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="11" class="text-muted fst-italic">No records found for this employee in the current filter.</td>`;
                    employeeDailyModalBody.appendChild(tr);
                } else {
                    const missed = '<span class="text-muted fst-italic">Missed log</span>';
                    const fmt = (val) => val ? val : missed;

                    for (const r of records) {
                        const missedCount = r.isWholeDayAbsent ? 0 : [r.rawTimeIn, r.rawBreakOut, r.rawBreakIn, r.rawTimeOut].filter(v => !v).length;
                        const missedBadge = (r.isWholeDayAbsent || missedCount === 0 || missedCount === 4) ? '-' : `<span class="badge bg-secondary">${missedCount}</span>`;
                        const graceParts = [];
                        if (r.withinMorningGrace) graceParts.push('Time In');
                        if (r.withinAfternoonGrace) graceParts.push('Break Out');
                        const graceUsed = graceParts.length ? graceParts.join(' + ') : '-';
                        const fmtBreakIn = (val) => {
                            if (!val) return missed;
                            if ((r.earlyBreakInMinutes || 0) > 0) return `${val} <span class="text-muted">(Early In)</span>`;
                            return val;
                        };

                        const tr = document.createElement('tr');
                        const timeInClass = r.rawTimeIn
                            ? (r.lateMinutes > 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold')
                            : '';
                        const breakOutClass = r.rawBreakOut
                            ? (r.undertimeBreakOutMinutes > 0 ? 'text-warning fw-semibold' : 'text-success fw-semibold')
                            : '';
                        const breakInClass = r.rawBreakIn
                            ? (r.lateBreakInMinutes > 0 ? 'text-danger fw-semibold' : ((r.earlyBreakInMinutes || 0) > 0 ? 'text-primary fw-semibold' : 'text-success fw-semibold'))
                            : '';
                        const timeOutClass = r.rawTimeOut
                            ? (r.undertimeTimeOutMinutes > 0 ? 'text-warning fw-semibold' : 'text-success fw-semibold')
                            : '';

                        tr.innerHTML = `
                            <td>${formatDateMMDDYYYY(r.date)}</td>
                            <td class="${timeInClass}">${fmt(r.rawTimeIn)}</td>
                            <td class="${breakOutClass}">${fmt(r.rawBreakOut)}</td>
                            <td class="${breakInClass}">${fmtBreakIn(r.rawBreakIn)}</td>
                            <td class="${timeOutClass}">${fmt(r.rawTimeOut)}</td>
                            <td>${graceUsed}</td>
                            <td class="late-minutes">${r.totalLateMinutesOverall > 0 ? r.totalLateMinutesOverall : '-'}</td>
                            <td class="undertime-minutes">${r.undertimeMinutes > 0 ? r.undertimeMinutes : '-'}</td>
                            <td class="hours-worked">${r.totalHours}</td>
                            <td>${missedBadge}</td>
                            <td>${r.status || ''}</td>
                        `;
                        employeeDailyModalBody.appendChild(tr);
                    }
                }

                const modal = bootstrap.Modal.getOrCreateInstance(employeeDailyModalEl);
                modal.show();
            }

            function openLetterModal(event, empId, type) {
                if (event) event.preventDefault();
                if (event && event.stopPropagation) event.stopPropagation();
                if (!letterModalEl || typeof bootstrap === 'undefined') return;

                const summary = (getFilteredMonthlySummary() || {})[empId] || monthlySummary[empId];
                if (!summary) return;

                const ctx = getLetterContext(empId, summary, type);
                currentLetterContext = ctx;
                buildLetterText(ctx);
                letterEditor.innerHTML = buildLetterHtmlTemplate(ctx);

                const typeLabel = type === 'late' ? 'Late' : (type === 'missed' ? 'Missed Logs' : 'Late + Missed Logs');
                letterModalLabel.textContent = `Letter — ${typeLabel}`;
                const deptLabel = selectedDepartment === 'ALL' ? 'All Departments' : selectedDepartment;
                letterModalMeta.textContent = `${summary.employeeName} (${empId}) • Department Filter: ${deptLabel}`;

                const safeName = (summary.employeeName || 'employee').replace(/[^a-z0-9]+/gi, '_').toLowerCase();
                currentLetterFilename = `letter_${safeName}_${typeLabel.replace(/\\s+/g,'_').toLowerCase()}.txt`;

                const modal = bootstrap.Modal.getOrCreateInstance(letterModalEl);
                modal.show();
            }

            letterEditor?.addEventListener('keydown', (e) => {
                const target = e.target;
                if (target && target.classList && target.classList.contains('fill')) {
                    if (e.key === 'Enter') e.preventDefault();
                }
            });
            letterEditor?.addEventListener('paste', (e) => {
                const target = e.target;
                if (target && target.classList && target.classList.contains('fill')) {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    document.execCommand('insertText', false, text);
                }
            });

            function getLetterContext(empId, summary, type) {
                const deptLabel = selectedDepartment === 'ALL' ? (summary.department || '') : selectedDepartment;
                const today = new Date();
                const dateStr = `${pad2(today.getMonth() + 1)}/${pad2(today.getDate())}/${today.getFullYear()}`;

                const records = getScopedProcessedData()
                    .filter(r => r.employeeId === empId)
                    .sort((a, b) => new Date(a.date) - new Date(b.date));

                const lateDays = records.filter(r => (r.totalLateMinutesOverall || 0) > 0);
                const missedDays = records.filter(r => !r.isWholeDayAbsent && [r.rawTimeIn, r.rawBreakOut, r.rawBreakIn, r.rawTimeOut].filter(v => !v).length > 0);

                return { empId, summary, type, deptLabel, dateStr, records, lateDays, missedDays };
            }

            function buildLetterText(ctx) {
                const { empId, summary, type, deptLabel, dateStr, lateDays, missedDays } = ctx;

                const lines = [];
                lines.push('ECO TRADE INDUSTRIAL MARKETING');
                lines.push('Purok 3 Dao Highway Hinaplanon, Iligan City,');
                lines.push('');
                lines.push(dateStr);
                lines.push('');
                lines.push(`${summary.employeeName}`);
                lines.push(`Employee ID: ${empId}`);
                if ((deptLabel || summary.department || '').trim()) {
                    lines.push(`Department: ${(deptLabel || summary.department || '').trim()}`);
                }
                lines.push('');
                lines.push('Subject: NOTICE TO EXPLAIN (Attendance and Timekeeping)');
                lines.push('');
                lines.push('Dear ' + (summary.employeeName || 'Employee') + ',');
                lines.push('');
                lines.push('This Notice to Explain (NTE) is being issued to request your written explanation regarding your attendance and/or timekeeping records, as reflected in the monitoring period covered by the uploaded logs.');
                lines.push('Please provide your explanation and supporting evidence (e.g., screenshots, location proof, supervisor confirmation, medical certificate, or other relevant documents).');
                lines.push('');
                lines.push('Details of concern(s):');
                lines.push('');

                if (type === 'late' || type === 'both') {
                    lines.push(`A. Tardiness / Late Logs`);
                    lines.push(`   - Late Frequency: ${summary.totalLateFrequency || 0}`);
                    lines.push(`   - Total Late Minutes: ${summary.totalLateMinutes || 0} minute(s)`);
                    lines.push(`   - Reference Date(s):`);
                    if (lateDays.length === 0) {
                        lines.push('     (none)');
                    } else {
                        lateDays.slice(0, 30).forEach(r => {
                            lines.push(`     - ${formatDateMMDDYYYY(r.date)}: Late ${r.totalLateMinutesOverall} minute(s) (Time In: ${r.rawTimeIn || 'Missed log'}, Break Out: ${r.rawBreakIn || 'Missed log'})`);
                        });
                        if (lateDays.length > 30) lines.push(`     - ...and ${lateDays.length - 30} more`);
                    }
                    lines.push('');
                }

                if (type === 'missed' || type === 'both') {
                    lines.push(`B. Missed Log(s) / Incomplete Punches`);
                    lines.push(`   - Days with Missing Punch: ${summary.missedLogDays || 0}`);
                    lines.push(`   - Reference Date(s):`);
                    if (missedDays.length === 0) {
                        lines.push('     (none)');
                    } else {
                        missedDays.slice(0, 30).forEach(r => {
                            const missing = [];
                            if (!r.rawTimeIn) missing.push('Time In');
                            if (!r.rawBreakOut) missing.push('Break In');
                            if (!r.rawBreakIn) missing.push('Break Out');
                            if (!r.rawTimeOut) missing.push('Time Out');
                            lines.push(`     - ${formatDateMMDDYYYY(r.date)}: Missing ${missing.join(', ')}`);
                        });
                        if (missedDays.length > 30) lines.push(`     - ...and ${missedDays.length - 30} more`);
                    }
                    lines.push('');
                }

                lines.push('In view of the above, you are hereby directed to submit your written explanation and evidence to HR/Management.');
                lines.push('');
                lines.push('Deadline for submission: ____________________');
                lines.push('Mode of submission: ________________________');
                lines.push('');
                lines.push('Failure to submit your explanation within the prescribed period may be construed as a waiver of your right to be heard, and management may proceed based on available records.');
                lines.push('');
                lines.push('Sincerely,');
                lines.push('');
                lines.push('________________________________________');
                lines.push('[Name of HR/Manager]');
                lines.push('[Position]');

                return lines.join('\n');
            }

            function buildLetterHtmlTemplate(ctx) {
                const { empId, summary, type, deptLabel, dateStr, lateDays, missedDays } = ctx;
                const esc = (s) => escapeHtml(s || '');
                const typeLabel = type === 'late' ? 'Late' : (type === 'missed' ? 'Missed Logs' : 'Late + Missed Logs');

                const lateList = lateDays.slice(0, 30).map(r => {
                    const ti = r.rawTimeIn || 'Missed log';
                    const bi = r.rawBreakIn || 'Missed log';
                    return `<li><strong>${esc(formatDateMMDDYYYY(r.date))}</strong> — Late <strong>${esc(String(r.totalLateMinutesOverall))}</strong> minute(s) (Time In: ${esc(ti)}, Break Out: ${esc(bi)})</li>`;
                }).join('');

                const missedList = missedDays.slice(0, 30).map(r => {
                    const missing = [];
                    if (!r.rawTimeIn) missing.push('Time In');
                    if (!r.rawBreakOut) missing.push('Break In');
                    if (!r.rawBreakIn) missing.push('Break Out');
                    if (!r.rawTimeOut) missing.push('Time Out');
                    return `<li><strong>${esc(formatDateMMDDYYYY(r.date))}</strong> — Missing: ${esc(missing.join(', '))}</li>`;
                }).join('');

                return `
                    <div class="letterhead">
                        <div>ECO TRADE INDUSTRIAL MARKETING</div>
                        <div>Purok 3 Dao Highway Hinaplanon, Iligan City,</div>
                    </div>

                    <div class="date">${esc(dateStr)}</div>

                    <div class="meta" style="margin-top: 14pt;">
                        <div><strong>${esc(summary.employeeName)}</strong></div>
                        <div>Employee ID: ${esc(empId)}</div>
                        <div>Department: ${esc((deptLabel || summary.department || '').trim())}</div>
                    </div>

                    <div class="subject">Subject: Notice to Explain (Attendance and Timekeeping) — ${esc(typeLabel)}</div>

                    <div class="salutation">Dear ${esc(summary.employeeName || 'Employee')},</div>

                    <div class="para">
                        This Notice to Explain (NTE) is being issued to request your written explanation regarding your attendance and/or timekeeping records, as reflected in the monitoring period covered by the uploaded logs.
                    </div>
                    <div class="para">
                        Please provide your explanation and supporting evidence (e.g., screenshots, location proof, supervisor confirmation, medical certificate, or other relevant documents).
                    </div>

                    <div class="heading">Details of concern(s):</div>

                    ${(type === 'late' || type === 'both') ? `
                        <div class="heading">A. Tardiness / Late Logs</div>
                        <div>- Late Frequency: <strong>${esc(String(summary.totalLateFrequency || 0))}</strong></div>
                        <div>- Total Late Minutes: <strong>${esc(String(summary.totalLateMinutes || 0))}</strong> minute(s)</div>
                        <div style="margin-top: 6pt;"><strong>Reference Date(s):</strong></div>
                        ${lateDays.length ? `<ul>${lateList}</ul>` : `<div>(none)</div>`}
                        ${lateDays.length > 30 ? `<div>...and ${esc(String(lateDays.length - 30))} more</div>` : ``}
                    ` : ``}

                    ${(type === 'missed' || type === 'both') ? `
                        <div class="heading">B. Missed Log(s) / Incomplete Punches</div>
                        <div>- Days with Missing Punch: <strong>${esc(String(summary.missedLogDays || 0))}</strong></div>
                        <div style="margin-top: 6pt;"><strong>Reference Date(s):</strong></div>
                        ${missedDays.length ? `<ul>${missedList}</ul>` : `<div>(none)</div>`}
                        ${missedDays.length > 30 ? `<div>...and ${esc(String(missedDays.length - 30))} more</div>` : ``}
                    ` : ``}

                    <div class="para">
                        In view of the above, you are hereby directed to submit your written explanation and evidence to HR/Management.
                    </div>

                    <div style="margin-top: 10pt;">
                        <div class="field-row">
                            <span class="field-label"><strong>Deadline for submission:</strong></span>
                            <span class="fill" contenteditable="true" data-placeholder="Type here"></span>
                        </div>
                        <div class="field-row">
                            <span class="field-label"><strong>Mode of submission:</strong></span>
                            <span class="fill" contenteditable="true" data-placeholder="Type here"></span>
                        </div>
                    </div>

                    <div class="para" style="margin-top: 10pt;">
                        Failure to submit your explanation within the prescribed period may be construed as a waiver of your right to be heard, and management may proceed based on available records.
                    </div>

                    <div class="signature-block">
                        <div>Sincerely,</div>
                        <div class="spacer"></div>
                        <div>________________________________________</div>
                        <div>[Name of HR/Manager]</div>
                        <div>[Position]</div>
                    </div>
                `;
            }

            function copyLetterText() {
                if (!letterEditor) return;
                const text = (letterEditor.innerText || '').trim();
                if (!text) return;
                navigator.clipboard?.writeText(text).catch(() => {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                });
            }

            const LETTER_MARGIN_MM = 25;

            function printLetter() {
                if (!letterEditor) return;
                const w = window.open('', '_blank');
                if (!w) {
                    alert('Popup blocked. Please allow popups to print the letter.');
                    return;
                }
                const letterHtml = getLetterExportBodyHtml();
                w.document.write(`
                    <!doctype html>
                    <html>
                    <head>
                        <meta charset="utf-8" />
                        <title>Letter</title>
                        <style>
                            @page { size: A4; margin: ${LETTER_MARGIN_MM}mm; }
                            html, body { height: 100%; }
                            body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                            ${getLetterExportCss()}
                        </style>
                    </head>
                    <body>
                        ${letterHtml}
                    </body>
                    </html>
                `);
                w.document.close();
                w.focus();
                setTimeout(() => {
                    w.print();
                    w.close();
                }, 200);
            }

            async function downloadLetterPdf() {
                if (!letterEditor || !window.jspdf || !window.html2canvas) {
                    alert('PDF export libraries not loaded. Please reload the page and try again.');
                    return;
                }

                const { jsPDF } = window.jspdf;
                const A4_WIDTH_PX = 794;
                const A4_HEIGHT_PX = 1123;
                const MARGIN_PX = Math.round(96 * (LETTER_MARGIN_MM / 25.4));
                const SCALE = 2;
                const pdf = new jsPDF({
                    unit: 'px',
                    format: [A4_WIDTH_PX, A4_HEIGHT_PX],
                    hotfixes: ['px_scaling']
                });

                const container = document.createElement('div');
                container.style.position = 'fixed';
                container.style.left = '0';
                container.style.top = '0';
                container.style.width = `${A4_WIDTH_PX - (MARGIN_PX * 2)}px`;
                container.style.background = '#ffffff';
                container.style.pointerEvents = 'none';
                container.style.zIndex = '-9999';

                container.innerHTML = `
                    <style>
                        ${getLetterExportCss()}
                    </style>
                    ${getLetterExportBodyHtml()}
                `;

                document.body.appendChild(container);

                try {
                    const canvas = await window.html2canvas(container, {
                        scale: SCALE,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    });

                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();

                    const usableWidth = pageWidth - (MARGIN_PX * 2);
                    const usableHeight = pageHeight - (MARGIN_PX * 2);

                    const pageSliceHeight = Math.floor(usableHeight * SCALE);
                    const sliceCanvas = document.createElement('canvas');
                    const sliceCtx = sliceCanvas.getContext('2d');
                    if (!sliceCtx) throw new Error('Canvas context unavailable');

                    sliceCanvas.width = canvas.width;

                    const totalSlices = Math.max(1, Math.ceil(canvas.height / pageSliceHeight));
                    for (let pageIndex = 0; pageIndex < totalSlices; pageIndex++) {
                        const sy = pageIndex * pageSliceHeight;
                        const sHeight = Math.min(pageSliceHeight, canvas.height - sy);
                        sliceCanvas.height = sHeight;

                        sliceCtx.clearRect(0, 0, sliceCanvas.width, sliceCanvas.height);
                        sliceCtx.drawImage(canvas, 0, sy, canvas.width, sHeight, 0, 0, canvas.width, sHeight);

                        const imgData = sliceCanvas.toDataURL('image/png', 1.0);
                        const renderedHeight = sHeight / SCALE;

                        if (pageIndex > 0) pdf.addPage();
                        pdf.addImage(imgData, 'PNG', MARGIN_PX, MARGIN_PX, usableWidth, renderedHeight);
                    }

                    const name = (currentLetterFilename || 'letter.txt').replace(/\.txt$/i, '.pdf');
                    pdf.save(name);
                } catch (err) {
                    console.error('PDF export failed:', err);
                    alert('PDF export failed. Please try again.');
                } finally {
                    document.body.removeChild(container);
                }
            }

            function downloadLetterWord() {
                if (!letterEditor) return;
                const html = `
                    <html xmlns:o="urn:schemas-microsoft-com:office:office"
                          xmlns:w="urn:schemas-microsoft-com:office:word"
                          xmlns="http://www.w3.org/TR/REC-html40">
                    <head>
                        <meta charset="utf-8" />
                        <title>Letter</title>
                        <style>
                            @page WordSection1 { size: 21cm 29.7cm; margin: 2.5cm; }
                            div.WordSection1 { page: WordSection1; }
                            ${getLetterExportCss()}
                        </style>
                    </head>
                    <body>
                        <div class="WordSection1">
                            ${getLetterExportBodyHtml()}
                        </div>
                    </body>
                    </html>
                `;

                const blob = new Blob([html], { type: 'application/msword;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = (currentLetterFilename || 'letter.txt').replace(/\.txt$/i, '.doc');
                a.click();
                URL.revokeObjectURL(url);
            }

            function downloadLetter() {
                if (!letterEditor) return;
                const text = (letterEditor.innerText || '').trim();
                const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = currentLetterFilename || 'letter.txt';
                a.click();
                URL.revokeObjectURL(url);
            }

            function escapeHtml(s) {
                return (s || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getLetterExportCss() {
                return `
                    .letter {
                        font-family: "Times New Roman", Times, serif;
                        font-size: 12pt;
                        line-height: 1.55;
                        color: #111827;
                    }
                    .letterhead { font-weight: 700; }
                    .date { text-align: right; margin-top: 12pt; }
                    .subject { font-weight: 700; text-transform: uppercase; margin: 14pt 0 10pt; }
                    .para { text-indent: 0.5in; margin: 0 0 10pt; }
                    .heading { font-weight: 700; margin: 10pt 0 6pt; }
                    ul { margin: 6pt 0 10pt 0.5in; }
                    li { margin: 2pt 0; }
                    .field-row { display: flex; align-items: flex-end; gap: 10px; margin-top: 2pt; }
                    .field-label { white-space: nowrap; }
                    .fill { flex: 1; min-width: 0; border-bottom: 1px solid #111827; padding: 0 4px 1px; white-space: nowrap; line-height: 1.1; }
                    .signature-block { margin-top: 18pt; }
                    .spacer { height: 18pt; }
                `;
            }

            function getLetterExportBodyHtml() {
                const htmlBody = letterEditor?.innerHTML || '';
                return `<div class="letter">${htmlBody}</div>`;
            }

            function showLoading() {
                loadingIndicator.style.display = 'block';
                summarySection.style.display = 'none';
                detailsSection.style.display = 'none';
            }

            function normalizeTimeTo24(timeStr) {
                if (!timeStr) return null;
                const cleaned = ('' + timeStr).trim().replace(/\s+/g, ' ');
                if (!cleaned) return null;
                const withoutMs = cleaned.replace(/\.\d+$/, '');

                const ampmMatch = withoutMs.match(/^(\d{1,2})(?::(\d{2}))?(?::(\d{2}))?\s*(am|pm)$/i);
                if (ampmMatch) {
                    let hour = parseInt(ampmMatch[1], 10);
                    const minute = parseInt(ampmMatch[2] || '0', 10);
                    const second = parseInt(ampmMatch[3] || '0', 10);
                    const period = ampmMatch[4].toLowerCase();

                    if (period === 'am' && hour === 12) hour = 0;
                    if (period === 'pm' && hour < 12) hour += 12;

                    if (!isValidTime(hour, minute, second)) return null;
                    return `${pad2(hour)}:${pad2(minute)}:${pad2(second)}`;
                }

                const hmsMatch = withoutMs.match(/^(\d{1,2})(?::(\d{2}))(?::(\d{2}))?$/);
                if (hmsMatch) {
                    const hour = parseInt(hmsMatch[1], 10);
                    const minute = parseInt(hmsMatch[2], 10);
                    const second = parseInt(hmsMatch[3] || '0', 10);
                    if (!isValidTime(hour, minute, second)) return null;
                    return `${pad2(hour)}:${pad2(minute)}:${pad2(second)}`;
                }

                return null;
            }

            function timeToMinutes(timeStr) {
                const normalized = normalizeTimeTo24(timeStr);
                if (!normalized) return null;
                const parts = normalized.split(':').map(Number);
                if (parts.length < 2) return null;
                const [hour, minute, second = 0] = parts;
                return hour * 60 + minute + (second / 60);
            }

            function createDateTime(dateStr, timeStr) {
                const normalized = normalizeTimeTo24(timeStr);
                const timeValue = normalized || timeStr;
                const dateTime = new Date(`${dateStr} ${timeValue}`);
                return Number.isNaN(dateTime.getTime()) ? null : dateTime;
            }

            function isValidTime(hour, minute, second) {
                return hour >= 0 && hour <= 23 && minute >= 0 && minute <= 59 && second >= 0 && second <= 59;
            }

            function pad2(value) {
                return String(value).padStart(2, '0');
            }

            function formatDateMMDDYYYY(dateStr) {
                if (!dateStr || !dateStr.trim()) return dateStr || '';
                const s = dateStr.trim();
                const ymd = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
                if (ymd) return `${pad2(ymd[2])}/${pad2(ymd[3])}/${ymd[1]}`;
                const mdy = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                if (mdy) return `${pad2(mdy[1])}/${pad2(mdy[2])}/${mdy[3]}`;
                const d = new Date(s);
                if (!Number.isNaN(d.getTime())) return `${pad2(d.getMonth() + 1)}/${pad2(d.getDate())}/${d.getFullYear()}`;
                return dateStr;
            }

            function dedupeNearbyPunches(activities) {
                if (!activities.length || DEDUPE_WINDOW_MINUTES <= 0) return activities;
                const out = [];
                let lastKeptMinutes = -9999;
                for (const a of activities) {
                    const m = a.minutes;
                    if (m === null) continue;
                    if (m - lastKeptMinutes >= DEDUPE_WINDOW_MINUTES) {
                        out.push(a);
                        lastKeptMinutes = m;
                    }
                }
                return out;
            }

            function normalizeActivity(activityStr) {
                const v = (activityStr || '').trim().toLowerCase();
                if (v === 'in' || v === 'time in' || v === 'clock in') return 'in';
                if (v === 'out' || v === 'time out' || v === 'clock out') return 'out';
                return '';
            }

            function normalizeDepartment(dept) {
                const raw = (dept || '').trim();
                if (!raw) return '';
                const key = raw.toLowerCase().replace(/\s+/g, ' ');
                if (key === 'ct print stop' || key === 'ct print shop') return 'CT Print Shop';
                if (key === 'jct') return 'JCT';
                if (key === 'jct print stop') return 'JCT Print Stop';
                if (key === 'eco trade' || key === 'ecotrade') return 'Ecotrade';
                if (key === 'shop / eco') return 'Shop / Eco';
                if (key === 'shop') return 'Shop';
                return raw;
            }

            function dedupeExactRecords(records) {
                const seen = new Set();
                const out = [];
                for (const r of records) {
                    const key = [
                        r.employeeId,
                        r.employeeName,
                        r.department,
                        r.date,
                        normalizeTimeTo24(r.time) || r.time,
                        r.activity
                    ].join('|');
                    if (seen.has(key)) continue;
                    seen.add(key);
                    out.push(r);
                }
                return out;
            }

            function inferActivitiesByTimeRange(activities) {
                const winMorningInEnd = timeToMinutes(BREAK_OUT_TIME);
                const winBreakOutStart = timeToMinutes(WORK_START_TIME);
                const winBreakOutEnd = timeToMinutes(BREAK_OUT_LATEST);
                const afternoonStart = timeToMinutes(BREAK_IN_EARLIEST);
                const earlyBreakInStart = timeToMinutes(EARLY_BREAK_IN_START);
                if (winMorningInEnd === null || winBreakOutEnd === null || afternoonStart === null) return activities;

                const result = activities.map(a => ({ ...a, activity: 'other' }));

                let morningInIdx = null;
                for (let i = 0; i < result.length; i++) {
                    const m = result[i].minutes;
                    if (m !== null && m >= 0 && m <= winMorningInEnd) {
                        result[i].activity = 'in';
                        morningInIdx = i;
                        break;
                    }
                }

                let breakOutIdx = null;
                for (let i = 0; i < result.length; i++) {
                    const m = result[i].minutes;
                    if (m !== null && m >= winBreakOutStart && m <= winBreakOutEnd && i !== morningInIdx) {
                        result[i].activity = 'out';
                        breakOutIdx = i;
                        break;
                    }
                }

                const afternoonInEnd = timeToMinutes(WORK_END_TIME);
                const afternoonOutStart = timeToMinutes(BREAK_IN_TIME);
                const dayEnd = 23 * 60 + 59;
                if (afternoonInEnd !== null && afternoonOutStart !== null) {
                    let breakInIdx = null;
                    const breakInStart = (breakOutIdx !== null && earlyBreakInStart !== null) ? earlyBreakInStart : afternoonStart;

                    for (let i = 0; i < result.length; i++) {
                        const m = result[i].minutes;
                        if (m === null) continue;
                        if (i === morningInIdx || i === breakOutIdx) continue;
                        if (m >= breakInStart && m <= afternoonInEnd) {
                            result[i].activity = 'in';
                            breakInIdx = i;
                            break;
                        }
                    }

                    for (let i = result.length - 1; i >= 0; i--) {
                        const m = result[i].minutes;
                        if (m === null) continue;
                        if (i === breakInIdx) continue;
                        if (m >= afternoonOutStart && m <= dayEnd) {
                            result[i].activity = 'out';
                            break;
                        }
                    }
                }

                return result;
            }

            function getDayOfWeekFromISO(isoDate) {
                const m = String(isoDate || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) return null;
                const y = parseInt(m[1], 10);
                const mo = parseInt(m[2], 10);
                const d = parseInt(m[3], 10);
                const dt = new Date(y, mo - 1, d);
                return Number.isNaN(dt.getTime()) ? null : dt.getDay();
            }

            function addDaysISO(isoDate, days) {
                const m = String(isoDate || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) return '';
                const y = parseInt(m[1], 10);
                const mo = parseInt(m[2], 10);
                const d = parseInt(m[3], 10);
                const dt = new Date(y, mo - 1, d);
                if (Number.isNaN(dt.getTime())) return '';
                dt.setDate(dt.getDate() + days);
                return `${dt.getFullYear()}-${pad2(dt.getMonth() + 1)}-${pad2(dt.getDate())}`;
            }

            function addWholeDayAbsences(records) {
                if (!Array.isArray(records) || records.length === 0) return records;

                const allDates = [...new Set(records.map(r => r.date).filter(Boolean))].sort();
                if (allDates.length === 0) return records;
                const minDate = allDates[0];
                const maxDate = allDates[allDates.length - 1];

                const byEmp = {};
                for (const r of records) {
                    if (!byEmp[r.employeeId]) byEmp[r.employeeId] = [];
                    byEmp[r.employeeId].push(r);
                }

                const out = [...records];

                for (const [empId, empRecs] of Object.entries(byEmp)) {
                    const existing = new Set(empRecs.map(r => r.date));
                    const worksSunday = empRecs.some(r => getDayOfWeekFromISO(r.date) === 0);
                    const meta = empRecs[0] || {};

                    for (let d = minDate; d && d <= maxDate; d = addDaysISO(d, 1)) {
                        const dow = getDayOfWeekFromISO(d);
                        if (dow === null) continue;
                        if (dow === 6) continue;
                        if (dow === 0 && !worksSunday) continue;
                        if (existing.has(d)) continue;

                        const metrics = calculateMetrics(d, null, null, null, null);
                        out.push({
                            employeeId: empId,
                            employeeName: meta.employeeName,
                            department: meta.department,
                            date: d,
                            timeIn: null,
                            breakOut: null,
                            breakIn: null,
                            timeOut: null,
                            activities: [],
                            ...metrics,
                            rawTimeIn: null,
                            rawBreakOut: null,
                            rawBreakIn: null,
                            rawTimeOut: null,
                            isWholeDayAbsent: true
                        });
                    }
                }

                return out;
            }

            function selectClosestInWindow(times, windowStart, windowEnd, targetTime, minMinutes) {
                const startMinutes = timeToMinutes(windowStart);
                const endMinutes = timeToMinutes(windowEnd);
                const targetMinutes = timeToMinutes(targetTime);
                if (startMinutes === null || endMinutes === null || targetMinutes === null) return null;

                let best = null;
                let bestScore = Infinity;
                for (const t of times) {
                    if (t.minutes === null) continue;
                    if (t.minutes < startMinutes || t.minutes > endMinutes) continue;
                    if (typeof minMinutes === 'number' && t.minutes < minMinutes) continue;
                    const score = Math.abs(t.minutes - targetMinutes);
                    if (score < bestScore) {
                        bestScore = score;
                        best = t;
                    }
                }
                return best;
            }

            function selectDailySlots(activities) {
                const times = (activities || []).filter(a => a.minutes !== null);
                if (times.length === 0) return { morningIn: null, morningOut: null, afternoonIn: null, afternoonOut: null };

                const morningIn = (() => {
                    const end = timeToMinutes(BREAK_OUT_TIME);
                    if (end === null) return null;
                    return times.find(t => t.minutes !== null && t.minutes >= 0 && t.minutes <= end) || null;
                })();

                const timeInMin = morningIn?.minutes ?? null;
                const minForBreakOut = (timeInMin !== null) ? (timeInMin + MIN_WORK_BEFORE_LUNCH_MINUTES) : null;
                const morningOut = selectClosestInWindow(
                    times,
                    WORK_START_TIME,
                    BREAK_OUT_LATEST,
                    BREAK_OUT_TIME,
                    (minForBreakOut !== null ? minForBreakOut : undefined)
                );

                const breakOutMin = morningOut?.minutes ?? null;
                const breakInWindowStart = morningOut ? EARLY_BREAK_IN_START : BREAK_IN_EARLIEST;
                const minForBreakIn = (breakOutMin !== null) ? (breakOutMin + MIN_LUNCH_BREAK_MINUTES) : null;

                const targetBreakInMin = timeToMinutes(BREAK_IN_TIME);
                const afternoonIn = (() => {
                    const startMinutes = timeToMinutes(breakInWindowStart);
                    const endMinutes = timeToMinutes(WORK_END_TIME);
                    if (startMinutes === null || endMinutes === null || targetBreakInMin === null) return null;
                    const candidates = times.filter(t =>
                        t.minutes !== null &&
                        t.minutes >= startMinutes &&
                        t.minutes <= endMinutes &&
                        (minForBreakIn === null || t.minutes >= minForBreakIn)
                    );
                    if (candidates.length === 0) return null;
                    let best = null;
                    let bestScore = Infinity;
                    for (const t of candidates) {
                        const score = Math.abs(t.minutes - targetBreakInMin);
                        if (score < bestScore) {
                            bestScore = score;
                            best = t;
                        }
                    }
                    if (!best) return null;
                    if (bestScore > MAX_BREAK_IN_DISTANCE_MINUTES) return null;
                    return best;
                })();

                const afternoonOut = (() => {
                    const startMinutes = timeToMinutes(BREAK_IN_TIME);
                    const endMinutes = timeToMinutes("23:59:59");
                    if (startMinutes === null || endMinutes === null) return null;

                    const minOut = afternoonIn?.minutes !== null && afternoonIn?.minutes !== undefined
                        ? Math.max(startMinutes, afternoonIn.minutes + MIN_WORK_AFTER_LUNCH_MINUTES)
                        : startMinutes;

                    const candidates = times.filter(t =>
                        t.minutes !== null &&
                        t.minutes >= minOut &&
                        t.minutes <= endMinutes
                    );
                    return candidates.length ? candidates[candidates.length - 1] : null;
                })();

                return { morningIn, morningOut, afternoonIn, afternoonOut };
            }

            function hideLoading() {
                loadingIndicator.style.display = 'none';
            }

            function exportToCSV() {
                const filteredRecords = getFilteredProcessedData();
                if (filteredRecords.length === 0) {
                    alert('No data to export');
                    return;
                }

                const headers = [
                    'Employee ID', 'Employee Name', 'Department', 'Date',
                    'Time In', 'Break In', 'Break Out', 'Time Out',
                    'Grace Used', 'Grace Late In (min)', 'Grace Late Break Out (min)',
                    'Late In Minutes', 'Undertime Break In Minutes', 'Late Break Out Minutes',
                    'OT Minutes', 'Total Late Minutes', 'Undertime Minutes',
                    'Total Hours', 'Status'
                ];

                const csvRows = [
                    headers.join(','),
                    ...filteredRecords.map(record => [
                        record.employeeId,
                        `"${record.employeeName}"`,
                        `"${record.department}"`,
                        formatDateMMDDYYYY(record.date),
                        record.rawTimeIn,
                        record.rawBreakOut || '',
                        record.rawBreakIn || '',
                        record.rawTimeOut,
                        (record.withinMorningGrace || record.withinAfternoonGrace)
                            ? `${record.withinMorningGrace ? 'Time In' : ''}${record.withinMorningGrace && record.withinAfternoonGrace ? ' + ' : ''}${record.withinAfternoonGrace ? 'Break Out' : ''}`
                            : '',
                        record.graceLateInMinutes || 0,
                        record.graceLateBreakInMinutes || 0,
                        record.lateMinutes,
                        record.undertimeBreakOutMinutes,
                        record.lateBreakInMinutes,
                        record.overtimeMinutes,
                        record.totalLateMinutesOverall,
                        record.undertimeMinutes,
                        record.totalHours,
                        record.status
                    ].join(','))
                ];

                const csvContent = csvRows.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const suffix = selectedDepartment === 'ALL' ? 'all_departments' : selectedDepartment.replace(/[^a-z0-9]+/gi, '_').toLowerCase();
                a.download = `employee_time_report_${suffix}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            }

            function printReport() {
                if (processedData.length === 0) {
                    alert('No data to print');
                    return;
                }

                const filterLabel = selectedDepartment === 'ALL' ? 'All Departments' : selectedDepartment;
                const summaryHtml = summarySection ? summarySection.outerHTML : '';
                const detailsHtml = detailsSection ? detailsSection.outerHTML : '';

                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    alert('Popup blocked. Please allow popups to print the report.');
                    return;
                }

                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8" />
                        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                        <title>Employee Time Tracking Report</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                        <style>
                            @page { size: landscape; margin: 10mm; }
                            body { background: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                            .container { max-width: 100%; }
                            .card { box-shadow: none !important; border: 1px solid #dee2e6; break-inside: avoid; }
                            .table { font-size: 11px; }
                            .table th { white-space: nowrap; }
                            .table td { vertical-align: middle; }
                            .table-responsive { overflow: visible !important; }
                            .upload-area, .filter-section, .alert, .btn, button, input, select { display: none !important; }
                        </style>
                    </head>
                    <body>
                        <div class="container mt-3">
                            <h3 class="mb-1">Employee Time Tracking Report</h3>
                            <p class="text-muted mb-3">Department Filter: ${filterLabel}</p>
                            ${summaryHtml}
                            ${detailsHtml}
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 300);
            }

            displayResults();
        </script>
    @endpush
</x-app-layout>
