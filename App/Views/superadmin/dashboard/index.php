<?php 
use Core\SiteUrls;
use Core\FormElements;

$me = $this->me;
$data = $this->data;

// Check for imported data in session
$importedEvents = isset($_SESSION['imported_calendar_events']) ? $_SESSION['imported_calendar_events'] : null;
if (!empty($importedEvents)) {
    $data['calendar_events'] = json_encode($importedEvents);
    $_SESSION['imported_calendar_events'] = null;
}

$importedStats = isset($_SESSION['imported_calendar_statistics']) ? $_SESSION['imported_calendar_statistics'] : null;
if (!empty($importedStats)) {
    $data['statistics'] = $importedStats;
    $_SESSION['imported_calendar_statistics'] = null;
}

// Get status options for filter
$statusOptions = [
    '' => 'All Status',
    1 => 'AUDIT (PENDING / ACTIVE)',
    2 => 'REVIEW (PENDING / ACTIVE)',
    3 => 'RE AUDIT (PENDING / ACTIVE)',
    4 => 'COMPLIANCE (PENDING / ACTIVE)',
    5 => 'REVIEW (PENDING / ACTIVE)',
    6 => 'RE COMPLIANCE (PENDING / ACTIVE)',
    7 => 'ASSESMENT COMPLETED',
    8 => 'REVIEWER TO AUDIT',
    9 => 'REVIEWER TO COMPLIANCE',
    10 => 'ADMIN INCREASE LIMIT IN AUDIT',
    11 => 'ADMIN INCREASE LIMIT IN COMPLIANCE',
    12 => 'ADMIN INCREASE DUE DATE IN AUDIT',
    13 => 'ADMIN INCREASE DUE DATE IN COMPLIANCE',
    14 => 'REVIEWER TO AUDIT (ENTIRE ASSESMENT)'
];
?>

<style>
/* Statistics Cards Styling */
.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
    border-radius: 15px;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-icon {
    font-size: 2rem;
    opacity: 0.7;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Blinking animation for today's date */
@keyframes blink {
    0% {
        background-color: #0d6efd;
        color: white;
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
    }
    50% {
        background-color: #ffc107;
        color: #0a3622;
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
    }
    100% {
        background-color: #0d6efd;
        color: white;
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
    }
}

.day-number-today {
    background-color: #0d6efd;
    color: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    animation: blink 1.5s ease-in-out infinite;
    cursor: pointer;
    transition: all 0.3s ease;
}

.day-number-today:hover {
    animation: none;
    transform: scale(1.1);
    background-color: #ffc107;
    color: #0a3622;
}

/* Custom badge styles for calendar events */
.event-badge {
    display: inline-block;
    width: 100%;
    padding: 6px 8px;
    margin-bottom: 4px;
    font-size: 0.7rem;
    font-weight: 500;
    text-align: left;
    border-radius: 4px;
    background-color: #ffffff;
    border: 1px solid;
    transition: all 0.2s ease;
}

.event-badge:hover {
    transform: translateX(2px);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.event-badge i {
    margin-right: 4px;
    font-size: 0.7rem;
}

.event-badge-audit {
    border-color: #198754;
    color: #0a3622;
}

.event-badge-audit i {
    color: #198754;
}

.event-badge-compliance {
    border-color: #ffc107;
    color: #664d03;
}

.event-badge-compliance i {
    color: #ffc107;
}

.event-badge-upcoming {
    border-color: #0dcaf0;
    color: #055160;
}

.event-badge-upcoming i {
    color: #0dcaf0;
}

.event-badge-due {
    border-color: #dc3545;
    color: #842029;
}

.event-badge-due i {
    color: #dc3545;
}

.event-badge-review {
    border-color: #17a2b8;
    color: #0c5460;
}

.event-badge-review i {
    color: #17a2b8;
}

.event-badge-default {
    border-color: #6c757d;
    color: #2b2f32;
}

/* Scrollable events container */
.events-scrollable {
    max-height: 80px;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
}

.events-scrollable::-webkit-scrollbar {
    width: 4px;
}

.events-scrollable::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.events-scrollable::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.events-scrollable::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.event-count-badge {
    background-color: #0d6efd;
    color: white;
    border-radius: 20px;
    padding: 2px 8px;
    font-size: 0.75rem;
    font-weight: 600;
}

.calendar-day-cell {
    min-height: 120px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.calendar-day-cell:hover {
    background-color: #f8f9fa;
    box-shadow: inset 0 0 0 1px #0d6efd;
}

.day-number {
    font-size: 1.1rem;
    font-weight: 600;
}

/* Pulse effect for today's cell */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
    }
}

.calendar-day-cell.border-primary.border-2 {
    animation: pulse 2s ease-in-out infinite;
}

.text-cyan {
    color: #17a2b8;
}

.border-cyan {
    border-color: #17a2b8;
}

.bg-cyan {
    background-color: #17a2b8;
}
</style>

<!-- Statistics Cards - 2 Rows of 3 Cards Each (Total card removed) -->
<div class="row mb-4 g-4">
    <div class="col-xl-4 col-md-6">
        <div class="card stat-card bg-white h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label text-success mb-2">AUDIT</h6>
                        <h2 class="stat-number text-success mb-0"><?= number_format($data['statistics']['audit_events'] ?? 0) ?></h2>
                    </div>
                    <i class="bi bi-check-circle stat-icon text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card stat-card bg-white h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label text-warning mb-2">COMPLIANCE</h6>
                        <h2 class="stat-number text-warning mb-0"><?= number_format($data['statistics']['compliance_events'] ?? 0) ?></h2>
                    </div>
                    <i class="bi bi-shield-check stat-icon text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card stat-card bg-white h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label text-danger mb-2">OVERDUE</h6>
                        <h2 class="stat-number text-danger mb-0"><?= number_format($data['statistics']['overdue_items'] ?? 0) ?></h2>
                    </div>
                    <i class="bi bi-exclamation-octagon stat-icon text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Filter Card -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-white py-3 border-0">
        <h5 class="mb-0 text-primary"><i class="bi bi-filter me-2"></i>Filter Events</h5>
    </div>
    <div class="card-body bg-white">
        <?= FormElements::generateFormStart([
            'method' => 'get',
            'action' => SiteUrls::getUrl('superAdminDashboard'),
            'class' => ''
        ]) ?>
        
        <input type="hidden" name="view" value="<?= $data['current_view'] ?>">
        
        <div class="row g-3">
            <div class="col-md-3">
                <?php
                $unitOptions = ['' => 'All Units'];
                if (!empty($data['audit_unit_data'])) {
                    foreach ($data['audit_unit_data'] as $unit) {
                        $unitOptions[$unit->id] = $unit->combined_name;
                    }
                }
                
                $selectMarkup = FormElements::generateSelect([
                    'name' => 'audit_unit_id',
                    'options' => $unitOptions,
                    'selected' => $this->request->input('audit_unit_id'),
                    'class' => 'form-select'
                ]);
                
                echo FormElements::generateFormGroup($selectMarkup, $data, null, 'default', ['audit_unit_id', 'Audit Unit']);
                ?>
            </div>

            <div class="col-md-3">
                <?php
                $typeMarkup = FormElements::generateSelect([
                    'name' => 'event_type',
                    'options' => [
                        '' => 'All Types',
                        'audit' => 'Audit',
                        'compliance' => 'Compliance',
                        'upcoming_audit' => 'Upcoming Audit'
                    ],
                    'selected' => $this->request->input('event_type'),
                    'class' => 'form-select'
                ]);
                
                echo FormElements::generateFormGroup($typeMarkup, $data, null, 'default', ['event_type', 'Event Type']);
                ?>
            </div>

            <div class="col-md-3">
                <?php
                $statusMarkup = FormElements::generateSelect([
                    'name' => 'status_id',
                    'options' => $statusOptions,
                    'selected' => $this->request->input('status_id'),
                    'class' => 'form-select'
                ]);
                
                echo FormElements::generateFormGroup($statusMarkup, $data, null, 'default', ['status_id', 'Status']);
                ?>
            </div>

            <div class="col-md-3">
                <?php
                $dateMarkup = FormElements::generateInput([
                    'name' => 'filter_date',
                    'type' => 'date',
                    'value' => $this->request->input('filter_date'),
                    'class' => 'form-control'
                ]);
                
                echo FormElements::generateFormGroup($dateMarkup, $data, null, 'default', ['filter_date', 'Date']);
                ?>
            </div>

            <div class="col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter"></i> Apply Filters
                </button>
                <a href="<?= SiteUrls::getUrl('superAdminDashboard') ?>?view=<?= $data['current_view'] ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-repeat"></i> Reset
                </a>
            </div>
        </div>
        
        <?= FormElements::generateFormClose() ?>
    </div>
</div>

<!-- Main Content -->
<?php if ($data['current_view'] == 'calendar'): ?>
    <!-- Calendar View -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
            <h5 class="mb-0 text-primary"><i class="bi bi-calendar3 me-2"></i>Calendar View</h5>
            <div>
                <span class="badge bg-white border border-success text-success me-1 p-2"><i class="bi bi-check-circle me-1"></i>Audit</span>
                <span class="badge bg-white border border-warning text-warning me-1 p-2"><i class="bi bi-shield-check me-1"></i>Compliance</span>
                <span class="badge bg-white border border-info text-info me-1 p-2"><i class="bi bi-calendar-plus me-1"></i>Upcoming</span>
                <span class="badge bg-white border border-danger text-danger me-1 p-2"><i class="bi bi-exclamation-triangle me-1"></i>Due Date</span>
                <span class="badge bg-white border border-cyan text-cyan p-2"><i class="bi bi-star me-1"></i>Review</span>
            </div>
        </div>
        <div class="card-body p-4 bg-white">
            <!-- Calendar Navigation -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-outline-primary" onclick="changeMonth(-1)">
                    <i class="bi bi-chevron-left me-2"></i>Previous
                </button>
                <h3 id="currentMonthYear" class="mb-0 fw-bold text-primary"></h3>
                <button class="btn btn-outline-primary" onclick="changeMonth(1)">
                    Next<i class="bi bi-chevron-right ms-2"></i>
                </button>
            </div>
            
            <!-- Weekday Headers -->
            <div class="row g-0 mb-2">
                <?php 
                $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                foreach ($weekdays as $day): 
                ?>
                    <div class="col text-center fw-bold py-2" style="color: #0d6efd;"><?= $day ?></div>
                <?php endforeach; ?>
            </div>
            
            <!-- Calendar Days Grid -->
            <div id="calendarDays" class="border rounded bg-white"></div>
        </div>
    </div>
<?php else: ?>
    <!-- List View with Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 text-primary"><i class="bi bi-list-ul me-2"></i>All Events List</h5>
        </div>
        <div class="card-body bg-white">
            <?php 
            $events = json_decode($data['calendar_events'], true);
            if (!empty($events)): 
            ?>
                <div class="table-responsive">
                    <table id="calendarEventsTable" class="table table-hover v-table">
                        <thead>
                            32
                                <th scope="col">#</th>
                                <th scope="col">Title</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date</th>
                                <th scope="col">Audit Unit</th>
                                <th scope="col">Period</th>
                                <th scope="col">Description</th>
                                <th scope="col" class="nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $srNo = 1;
                            foreach ($events as $event): 
                                $statusClass = '';
                                $statusText = $event['status_title'] ?? $event['status'] ?? '';
                                switch(strtolower($event['category'] ?? '')) {
                                    case 'due':
                                        $statusClass = 'danger';
                                        $statusText = 'Due Date';
                                        break;
                                    case 'review':
                                        $statusClass = 'cyan';
                                        $statusText = 'Review';
                                        break;
                                    case 'start':
                                        $statusClass = 'success';
                                        $statusText = 'Started';
                                        break;
                                    case 'end':
                                        $statusClass = 'secondary';
                                        $statusText = 'Completed';
                                        break;
                                    case 'upcoming':
                                        $statusClass = 'info';
                                        $statusText = 'Upcoming';
                                        break;
                                    default:
                                        $statusClass = 'info';
                                }
                                
                                // Set text color based on type for better visibility
                                $typeTextClass = '';
                                $typeBorderClass = '';
                                switch($event['type']) {
                                    case 'audit':
                                        $typeTextClass = 'text-success';
                                        $typeBorderClass = 'border-success';
                                        break;
                                    case 'compliance':
                                        $typeTextClass = 'text-warning';
                                        $typeBorderClass = 'border-warning';
                                        break;
                                    case 'upcoming_audit':
                                        $typeTextClass = 'text-info';
                                        $typeBorderClass = 'border-info';
                                        break;
                                    default:
                                        $typeTextClass = 'text-secondary';
                                        $typeBorderClass = 'border-secondary';
                                }
                            ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= $srNo++ ?></td>
                                    <td class="text-dark"><?= htmlspecialchars($event['title']) ?></td>
                                    <td>
                                        <span class="badge bg-white border <?= $typeBorderClass ?> <?= $typeTextClass ?> p-2">
                                            <i class="bi <?= $event['type'] == 'audit' ? 'bi-check-circle' : ($event['type'] == 'compliance' ? 'bi-shield-check' : ($event['type'] == 'upcoming_audit' ? 'bi-calendar-plus' : 'bi-calendar')) ?> me-1"></i>
                                            <?= ucfirst(str_replace('_', ' ', $event['type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-white border border-<?= $statusClass == 'cyan' ? 'cyan' : $statusClass ?> text-<?= $statusClass == 'cyan' ? 'cyan' : $statusClass ?> p-2">
                                            <i class="bi <?= $statusClass == 'danger' ? 'bi-exclamation-triangle' : ($statusClass == 'success' ? 'bi-play-circle' : ($statusClass == 'cyan' ? 'bi-star' : ($statusClass == 'info' ? 'bi-calendar-plus' : 'bi-stop-circle'))) ?> me-1"></i>
                                            <?= ucfirst($statusText) ?>
                                        </span>
                                    </td>
                                    <td class="text-dark"><i class="bi bi-calendar3 me-2 text-secondary"></i><?= date('d M Y', strtotime($event['start'])) ?></td>
                                    <td class="text-dark">
                                        <?php 
                                        if (!empty($event['audit_unit_name'])) {
                                            echo '<i class="bi bi-building me-2 text-secondary"></i>' . htmlspecialchars($event['audit_unit_name']);
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-dark">
                                        <?php if (!empty($event['assesment_period_from']) && !empty($event['assesment_period_to'])): ?>
                                            <small class="text-muted">
                                                <?= date('d M Y', strtotime($event['assesment_period_from'])) ?> - 
                                                <?= date('d M Y', strtotime($event['assesment_period_to'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-dark">
                                        <span class="text-truncate d-inline-block" style="max-width: 250px;">
                                            <?= htmlspecialchars($event['description'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showEventDetails('<?= $event['start'] ?>')">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x fs-1 text-secondary mb-3"></i>
                    <h5 class="text-secondary">No events found</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?= FormElements::generateFormStart([
                'method' => 'post',
                'action' => SiteUrls::getUrl('superAdminDashboard/importData'),
                'enctype' => 'multipart/form-data'
            ]) ?>
            
            <div class="modal-header bg-white">
                <h5 class="modal-title text-primary"><i class="bi bi-upload me-2"></i>Import Calendar Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body bg-white">
                <div class="text-center mb-4">
                    <i class="bi bi-file-earmark-arrow-up fs-1 text-primary mb-3"></i>
                    <p class="text-secondary">Upload a JSON file exported from this system</p>
                </div>
                
                <?php
                $fileMarkup = FormElements::generateInput([
                    'name' => 'import_file',
                    'type' => 'file',
                    'class' => 'form-control',
                    'accept' => '.json',
                    'required' => true
                ]);
                
                echo FormElements::generateFormGroup($fileMarkup, $data, null, 'default', ['import_file', 'Select JSON file']);
                ?>
                
                <div class="alert alert-light mt-3 text-secondary">
                    <i class="bi bi-info-circle me-2"></i>
                    Please select a valid JSON file exported from the calendar system.
                </div>
            </div>
            
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-2"></i>Cancel
                </button>
                <?= FormElements::generateSubmitButton('import', [
                    'value' => '<i class="bi bi-upload me-2"></i>Import',
                    'class' => 'btn btn-primary'
                ]) ?>
            </div>
            
            <?= FormElements::generateFormClose() ?>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title text-primary">
                    <i class="bi bi-calendar-check me-2"></i>
                    <span id="modalDate"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-white" id="eventDetailsModalBody">
                <!-- Events will be loaded here -->
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($data['current_view'] == 'calendar'): ?>
<script>
// Pass calendar events data to JavaScript
var calendarEventsData = <?= $data['calendar_events'] ?: '[]' ?>;
</script>
<script src="<?= SiteUrls::getUrl('public') ?>/js/superadmin/calendar-dashboard.js"></script>
<?php endif; ?>

<!-- Bootstrap Icons and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>