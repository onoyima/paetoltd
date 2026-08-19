<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + assign_room permission
pt_require_page('assign_room');

include 'php/fetch_admin_info.php';
require_once __DIR__ . '/php/academic_helper.php';

$hostels = pt_all_hostels();
$sessions = pt_all_sessions();
$activeSession = pt_active_session();
$sessionId = pt_active_session_id();

// Parse hostel ID from URL (e.g. assigned_room.php/2 or assigned_room.php?hostel_id=2)
$hostelIdFromUrl = 0;
if (!empty($_SERVER['PATH_INFO'])) {
	$pathParts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
	if (count($pathParts) > 0 && is_numeric($pathParts[0])) {
		$hostelIdFromUrl = (int)$pathParts[0];
	}
}
if ($hostelIdFromUrl === 0 && !empty($_SERVER['REQUEST_URI'])) {
	$uriParts = explode('?', $_SERVER['REQUEST_URI']);
	$path = $uriParts[0];
	$pos = strpos($path, 'assigned_room.php/');
	if ($pos !== false) {
		$subPath = substr($path, $pos + strlen('assigned_room.php/'));
		$pathParts = explode('/', trim($subPath, '/'));
		if (count($pathParts) > 0 && is_numeric($pathParts[0])) {
			$hostelIdFromUrl = (int)$pathParts[0];
		}
	}
}
if ($hostelIdFromUrl === 0 && isset($_GET['hostel_id']) && is_numeric($_GET['hostel_id'])) {
	$hostelIdFromUrl = (int)$_GET['hostel_id'];
}

$selectedHostel = null;
if ($hostelIdFromUrl > 0) {
	foreach ($hostels as $h) {
		if ((int)$h['id'] === $hostelIdFromUrl) {
			$selectedHostel = $h;
			break;
		}
	}
	if (!$selectedHostel) {
		$hostelIdFromUrl = 0;
	}
}

$hostelColors = array(
	1 => '#F93A0B',
	2 => '#3a86ff',
	3 => '#06d6a0',
	4 => '#8338ec',
	5 => '#f9c74f',
);

function pt_hostel_color($colors, $id) {
	return isset($colors[$id]) ? $colors[$id] : '#F93A0B';
}

// Per-hostel assignment counts for the active session (server-side stat strip)
$hostelTaken = array();
$hostelAvailable = array();
$totalTaken = 0;
$totalAvailable = 0;
if ($sessionId > 0 && isset($conn)) {
	$st = $conn->prepare("SELECT hostel_id, SUM(student_name IS NOT NULL) AS taken, SUM(student_name IS NULL) AS available FROM assign_room WHERE session_id = ? GROUP BY hostel_id");
	if ($st) {
		$st->bind_param('i', $sessionId);
		$st->execute();
		$res = $st->get_result();
		while ($row = $res->fetch_assoc()) {
			$hid = (int)$row['hostel_id'];
			$hostelTaken[$hid] = (int)$row['taken'];
			$hostelAvailable[$hid] = (int)$row['available'];
		}
		$st->close();
	}
	$totalTaken = array_sum($hostelTaken);
	$totalAvailable = array_sum($hostelAvailable);
}

$pageTitle = 'Assign Room';
$pageHeader = 'Assign Room';
if ($selectedHostel) {
	$pageTitle .= ' - ' . htmlspecialchars($selectedHostel['name']);
	$pageHeader .= ' - ' . htmlspecialchars($selectedHostel['name']);
}
?>
<?php include 'includes/head.php'; ?>
<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!--**********************************
	Content body start
***********************************-->
<div class="content-body" id="pt-content">
	<div class="container-fluid">
		<div class="row page-titles">
		</div>

		<?php if ($activeSession): ?>
			<div class="row">
				<div class="col-12">
					<div class="alert alert-success rounded-0 d-flex flex-wrap align-items-center">
						<i class="fas fa-calendar-check me-2"></i>
						<span>Active session: <strong><?php echo htmlspecialchars($activeSession['name']); ?></strong></span>
						<span class="ms-auto d-flex align-items-center">
							<label for="assignSessionSelector" class="me-2 mb-0 small text-muted">Viewing session:</label>
							<select id="assignSessionSelector" class="form-select form-select-sm w-auto">
								<?php foreach ($sessions as $s): ?>
									<option value="<?php echo (int)$s['id']; ?>" <?php echo !empty($s['is_active']) ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($s['name']); ?><?php echo !empty($s['is_active']) ? ' (Active)' : ''; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</span>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="row">
				<div class="col-12">
					<div class="alert alert-warning rounded-0 d-flex flex-wrap align-items-center">
						<i class="fas fa-exclamation-triangle me-2"></i>
						<span>No session is currently active.</span>
						<span class="ms-auto d-flex align-items-center">
							<label for="assignSessionSelector" class="me-2 mb-0 small text-muted">Viewing session:</label>
							<select id="assignSessionSelector" class="form-select form-select-sm w-auto">
								<?php foreach ($sessions as $s): ?>
									<option value="<?php echo (int)$s['id']; ?>">
										<?php echo htmlspecialchars($s['name']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</span>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Assignment stats for the selected session -->
		<div class="row mb-3">
			<div class="col-12">
				<div class="card">
					<div class="card-body py-3">
						<div class="d-flex flex-wrap align-items-center">
							<div class="pe-4">
								<small class="text-muted d-block">Total (Taken / Available)</small>
								<h3 class="mb-0" id="statTotal">
									<?php 
									if ($selectedHostel) {
										$sid = (int)$selectedHostel['id'];
										$t = isset($hostelTaken[$sid]) ? $hostelTaken[$sid] : 0;
										$a = isset($hostelAvailable[$sid]) ? $hostelAvailable[$sid] : 0;
										echo $t . ' / ' . $a;
									} else {
										echo (int)$totalTaken . ' / ' . (int)$totalAvailable; 
									}
									?>
								</h3>
							</div>
							<?php foreach ($hostels as $h): 
								if ($selectedHostel && (int)$h['id'] !== (int)$selectedHostel['id']) {
									continue;
								}
								$color = pt_hostel_color($hostelColors, (int)$h['id']); 
								$hid = (int)$h['id'];
								$t = isset($hostelTaken[$hid]) ? $hostelTaken[$hid] : 0;
								$a = isset($hostelAvailable[$hid]) ? $hostelAvailable[$hid] : 0;
							?>
								<div class="px-4 border-start" id="stat-hostel-container-<?php echo $hid; ?>">
									<small class="text-muted d-block" style="color:<?php echo $color; ?>;"><?php echo htmlspecialchars($h['name']); ?></small>
									<h3 class="mb-0" id="stat-hostel-<?php echo $hid; ?>"><?php echo $t . ' / ' . $a; ?></h3>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<ul class="nav nav-pills" id="assignHostelTabs" role="tablist">
							<?php if (!$selectedHostel): ?>
								<li class="nav-item" role="presentation">
									<a class="nav-link active pt-assign-tab" id="assign-hostel-all-tab" href="#" role="tab" aria-selected="true" data-hostel-id="0">All Hostels</a>
								</li>
							<?php endif; ?>
						<?php foreach ($hostels as $h): 
							if ($selectedHostel && (int)$h['id'] !== (int)$selectedHostel['id']) {
								continue;
							}
							$color = pt_hostel_color($hostelColors, (int)$h['id']); 
							$isActive = $selectedHostel && (int)$h['id'] === (int)$selectedHostel['id'];
						?>
							<li class="nav-item" role="presentation">
								<a class="nav-link pt-assign-tab <?php echo $isActive ? 'active' : ''; ?>" id="assign-hostel-<?php echo (int)$h['id']; ?>-tab" href="#" role="tab" aria-selected="<?php echo $isActive ? 'true' : 'false'; ?>" data-hostel-id="<?php echo (int)$h['id']; ?>" style="border:1px solid <?php echo $color; ?>;color:<?php echo $color; ?>;">
									<i class="fas fa-building me-1"></i><?php echo htmlspecialchars($h['name']); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>

					<!-- Search & filter toolbar -->
					<div class="pt-assign-view" id="pt-assign-view">
						<div class="row mt-3 g-2">
							<div class="col-md-3 col-sm-6">
								<input type="text" class="form-control form-control-sm" id="searchName" placeholder="Name">
							</div>
							<div class="col-md-3 col-sm-6">
								<input type="text" class="form-control form-control-sm" id="searchMatric" placeholder="Matric No">
							</div>
							<div class="col-md-3 col-sm-6">
								<input type="text" class="form-control form-control-sm" id="searchDepartment" placeholder="Department">
							</div>
							<div class="col-md-3 col-sm-6">
								<input type="text" class="form-control form-control-sm" id="searchLevel" placeholder="Level">
							</div>
							<div class="col-md-3 col-sm-6">
								<input type="text" class="form-control form-control-sm" id="searchStudentNumber" placeholder="Student Number">
							</div>
							<div class="col-md-3 col-sm-6">
								<input type="text" class="form-control form-control-sm" id="searchRoomBunk" placeholder="Room Bunk">
							</div>
							<div class="col-md-3 col-sm-6">
								<input type="text" class="form-control form-control-sm" id="searchBedSpace" placeholder="Bed Space">
							</div>
							<div class="col-md-3 col-sm-6">
								<button type="button" class="btn btn-sm btn-primary w-100" id="clearFilters">Clear Filters</button>
							</div>
						</div>

						<div class="card-footer px-0 pt-3 pb-0 d-flex flex-wrap gap-2 bg-transparent border-0">
							<button id="downloadCSV" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Download CSV</button>
							<button id="downloadCSVTemplate" class="btn btn-primary btn-sm"><i class="fas fa-file-csv me-1"></i>CSV Template</button>
							<button id="downloadFilteredExcel" class="btn btn-warning btn-sm"><i class="fas fa-file-excel me-1"></i>Download Filtered Excel</button>
							<button id="printTable" class="btn btn-secondary btn-sm"><i class="fas fa-print me-1"></i>Print Table</button>
							<button id="uploadCSVBtn" class="btn btn-success btn-sm"><i class="fas fa-upload me-1"></i>Upload CSV</button>
							<button id="manageUploadsBtn" class="btn btn-info btn-sm"><i class="fas fa-list-alt me-1"></i>Manage Uploads</button>
						</div>

						<div class="table-responsive pt-table-wrap mt-2">
							<table class="table display mb-4 dataTablesCard job-table card-table" id="userTable">
								<thead>
									<tr>
										<th>Serial Number</th>
										<th>Student Name</th>
										<th>Matric No</th>
										<th>Department</th>
										<th>Parent Number</th>
										<th>Level</th>
										<th>Student Number</th>
										<th>Room Bunk</th>
										<th>Bed Space</th>
										<th>Hostel</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<!-- Content will be dynamically loaded here via JavaScript -->
								</tbody>
							</table>
						</div>
					</div>

					<!-- Manage Uploads panel: pick hostel + session, review uploaded
					     batches and delete (revoke) one so it can be re-uploaded. -->
					<div class="pt-manage-uploads d-none mt-3" id="pt-manage-uploads">
						<div class="mb-3">
							<button type="button" class="btn btn-sm btn-outline-secondary" id="backToAssignBtn">
								<i class="fas fa-arrow-left me-1"></i>Back to Room Assignments
							</button>
						</div>
						<div class="row g-2 align-items-end mb-3">
							<div class="col-md-4 col-sm-6">
								<label class="form-label small text-muted mb-1" for="manageSession">Academic Session</label>
								<select id="manageSession" class="form-select form-select-sm">
									<?php foreach ($sessions as $s): ?>
										<option value="<?php echo (int)$s['id']; ?>" <?php echo !empty($s['is_active']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($s['name']); ?><?php echo !empty($s['is_active']) ? ' (Active)' : ''; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 col-sm-6">
								<label class="form-label small text-muted mb-1" for="manageHostel">Hostel</label>
								<select id="manageHostel" class="form-select form-select-sm">
									<?php foreach ($hostels as $h): 
										if ($selectedHostel && (int)$h['id'] !== (int)$selectedHostel['id']) {
											continue;
										}
									?>
										<option value="<?php echo (int)$h['id']; ?>"><?php echo htmlspecialchars($h['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 col-sm-12">
								<button type="button" class="btn btn-sm btn-primary w-100" id="loadBatchesBtn">
									<i class="fas fa-sync-alt me-1"></i>Load Uploads
								</button>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-striped table-hover mb-0" id="uploadBatchTable">
								<thead>
									<tr>
										<th>Upload</th>
										<th>File Name</th>
										<th>Rows</th>
										<th>Errors</th>
										<th>Uploaded By</th>
										<th>Uploaded At</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
						<div class="text-muted small mt-2" id="batchEmptyHint"></div>
					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- CSV Upload Modal -->
<div class="modal fade" id="csvUploadModal" tabindex="-1" role="dialog" aria-labelledby="csvUploadModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="csvUploadModalLabel">Upload CSV File</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="csvUploadForm" enctype="multipart/form-data">
					<div class="form-group">
						<label for="csvFile">Select CSV File</label>
						<input type="file" class="form-control-file form-control" id="csvFile" name="csv_file" accept=".csv" required>
					</div>
					<div class="form-group mt-3">
						<label for="hostelSelector">Hostel</label>
						<select id="hostelSelector" name="hostel_id" class="default-select form-control" required>
							<?php foreach ($hostels as $h): ?>
								<option value="<?php echo (int)$h['id']; ?>"><?php echo htmlspecialchars($h['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<div class="form-group mt-3">
					<label for="sessionSelector">Academic Session</label>
					<select id="sessionSelector" name="session_id" class="default-select form-control" required>
						<?php foreach ($sessions as $s): ?>
							<?php if (!empty($s['is_active'])): ?>
								<option value="<?php echo (int)$s['id']; ?>" selected>
									<?php echo htmlspecialchars($s['name']); ?> (Active)
								</option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
					<div class="alert alert-info mt-3 mb-0">
						<p>CSV file should have the following columns (8 columns total):</p>
						<ol>
							<li>Student Name</li>
							<li>Matric No</li>
							<li>Department</li>
							<li>Parent Number</li>
							<li>Level</li>
							<li>Student Number</li>
							<li>Room Bunk</li>
							<li>Bed Space (e.g., A1, B2, C1)</li>
						</ol>
						<p>Room Bunk is used as the unique identifier and cannot be changed.</p>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="submitCSV">Upload</button>
			</div>
		</div>
	</div>
</div>

<!-- Update Student Modal -->
<div class="modal fade" id="updateStudentModal" tabindex="-1" role="dialog" aria-labelledby="updateStudentModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="updateStudentModalLabel">Update Student Information</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="updateStudentForm">
					<input type="hidden" id="studentId" name="id">
					<input type="hidden" id="studentHostelId" name="hostel_id">
					<div class="form-group">
						<label for="studentName">Student Name</label>
						<input type="text" class="form-control" id="studentName" name="student_name" required>
					</div>
					<div class="form-group">
						<label for="matricNo">Matric No</label>
						<input type="text" class="form-control" id="matricNo" name="matric_no" required>
					</div>
					<div class="form-group">
						<label for="department">Department</label>
						<input type="text" class="form-control" id="department" name="department" required>
					</div>
					<div class="form-group">
						<label for="parentNumber">Parent Number</label>
						<input type="text" class="form-control" id="parentNumber" name="parent_number" required>
					</div>
					<div class="form-group">
						<label for="level">Level</label>
						<input type="text" class="form-control" id="level" name="level" required>
					</div>
					<div class="form-group">
						<label for="studentNumber">Student Number</label>
						<input type="text" class="form-control" id="studentNumber" name="student_number" required>
					</div>
					<div class="form-group">
						<label for="roomBunk">Room Bunk</label>
						<input type="text" class="form-control" id="roomBunk" name="room_bunk" required readonly>
						<small class="form-text text-muted">Room Bunk cannot be changed here.</small>
					</div>
					<div class="form-group">
						<label for="bedSpace">Bed Space</label>
						<input type="text" class="form-control" id="bedSpace" name="bed_space" placeholder="e.g. Bunk 2 Up" readonly>
						<small class="form-text text-muted">Bed Space cannot be changed here.</small>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="saveStudentChanges">Save Changes</button>
			</div>
		</div>
	</div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="ptConfirmModal" tabindex="-1" role="dialog" aria-labelledby="ptConfirmModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header border-0 pb-0">
				<h5 class="modal-title" id="ptConfirmModalLabel">Confirm Delete</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-center pt-2">
				<div class="pt-confirm-icon">
					<i class="fas fa-trash-alt"></i>
				</div>
				<h4 class="mt-3 mb-1" id="ptConfirmTitle">Are you sure?</h4>
				<p class="text-muted mb-0" id="ptConfirmMessage">You are about to delete this item. This action cannot be undone.</p>
			</div>
			<div class="modal-footer border-0 justify-content-center pb-4">
				<button type="button" class="btn btn-secondary light" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" id="ptConfirmDeleteBtn">Delete</button>
			</div>
		</div>
	</div>
</div>

<style>
	.pt-confirm-icon {
		width: 76px;
		height: 76px;
		line-height: 76px;
		border-radius: 50%;
		background: rgba(220, 53, 69, 0.12);
		color: #dc3545;
		font-size: 32px;
		margin: 0 auto;
	}
</style>

<script>
	var currentHostelId = <?php echo (int)$hostelIdFromUrl; ?>;
	var currentSessionId = <?php echo (int)$sessionId; ?>;
	var ptUsers = [];
	var ptConfirmAction = null;
	var ptRoomTable = null;
	var ptHostelList = <?php echo json_encode(array_map(function ($h) { return array('id' => (int)$h['id'], 'name' => $h['name']); }, $hostels)); ?>;

	function esc(s) {
		return String(s ?? '').replace(/[&<>"']/g, function (c) {
			return { '&': '&', '<': '<', '>': '>', '"': '"', "'": '\'' }[c];
		});
	}

	function ptGetSessionId() {
		var el = document.getElementById('assignSessionSelector');
		return el ? parseInt(el.value, 10) : currentSessionId;
	}

	function getHostelName(id) {
		for (var i = 0; i < ptHostelList.length; i++) {
			if (ptHostelList[i].id === id) return ptHostelList[i].name;
		}
		return '';
	}

	function destroyTables() {
		// DataTables disabled for now — plain table used instead
		// if (!jQuery.fn.DataTable) return;
		// ['#userTable'].forEach(function (sel) {
		// 	var $t = $(sel);
		// 	if (!$t.length) return;
		// 	if (jQuery.fn.DataTable.isDataTable($t)) { $t.DataTable().destroy(); }
		// });
	}

	function initDataTables() {
		// DataTables disabled for now — plain table used instead
		// if (!window.jQuery || !jQuery.fn) return;
		// var doInit = function () { ... };
		// ...
	}

	function applyColumnFilters() {
		var filtered = getFilteredUsers();
		var tableBody = $('#userTable tbody');
		tableBody.empty();
		filtered.forEach(function (user, index) {
			var row = `
				<tr data-sn="${esc(user.sn)}" data-hostel-id="${esc(user.hostel_id)}">
					<td class="text-center">${index + 1}</td>
					<td>${esc(user.student_name)}</td>
					<td>${esc(user.matric_no)}</td>
					<td>${esc(user.department)}</td>
					<td>${esc(user.parent_number)}</td>
					<td>${esc(user.level)}</td>
					<td>${esc(user.student_number)}</td>
					<td>${esc(user.room_bunk)}</td>
					<td>${esc(user.bed_space)}</td>
					<td>${esc(user.hostel_name || getHostelName(user.hostel_id))}</td>
					<td class="text-center text-nowrap">
						<button class="btn btn-primary btn-sm px-2 me-1 assign-update-btn" title="Update"
							data-id="${esc(user.id)}"
							data-sn="${esc(user.sn)}"
							data-hostel-id="${esc(user.hostel_id)}"
							data-student-name="${esc(user.student_name)}"
							data-matric-no="${esc(user.matric_no)}"
							data-department="${esc(user.department)}"
							data-parent-number="${esc(user.parent_number)}"
							data-level="${esc(user.level)}"
							data-student-number="${esc(user.student_number)}"
							data-room-bunk="${esc(user.room_bunk)}"
							data-bed-space="${esc(user.bed_space)}"><i class="fas fa-pencil-alt"></i> Update</button>
					</td>
				</tr>`;
			tableBody.append(row);
		});
	}

	function renderTable() {
		var tableBody = $('#userTable tbody');
		tableBody.empty();

		ptUsers.forEach(function (user, index) {
			var row = `
				<tr data-sn="${esc(user.sn)}" data-hostel-id="${esc(user.hostel_id)}">
					<td class="text-center">${index + 1}</td>
					<td>${esc(user.student_name)}</td>
					<td>${esc(user.matric_no)}</td>
					<td>${esc(user.department)}</td>
					<td>${esc(user.parent_number)}</td>
					<td>${esc(user.level)}</td>
					<td>${esc(user.student_number)}</td>
					<td>${esc(user.room_bunk)}</td>
					<td>${esc(user.bed_space)}</td>
					<td>${esc(user.hostel_name || getHostelName(user.hostel_id))}</td>
					<td class="text-center text-nowrap">
						<button class="btn btn-primary btn-sm px-2 me-1 assign-update-btn" title="Update"
							data-id="${esc(user.id)}"
							data-sn="${esc(user.sn)}"
							data-hostel-id="${esc(user.hostel_id)}"
							data-student-name="${esc(user.student_name)}"
							data-matric-no="${esc(user.matric_no)}"
							data-department="${esc(user.department)}"
							data-parent-number="${esc(user.parent_number)}"
							data-level="${esc(user.level)}"
							data-student-number="${esc(user.student_number)}"
							data-room-bunk="${esc(user.room_bunk)}"
							data-bed-space="${esc(user.bed_space)}"><i class="fas fa-pencil-alt"></i> Update</button>
						<!-- <button class="btn btn-danger btn-sm px-2 assign-revoke-btn" title="Revoke" data-sn="${esc(user.sn)}" data-name="${esc(user.student_name)}"><i class="fas fa-trash-alt"></i></button> -->
					</td>
				</tr>
			`;
			tableBody.append(row);
		});
	}

	function updateStatsFrom(users) {
		var taken = {}, available = {};
		users.forEach(function (u) {
			if (u.student_name && u.student_name.trim() !== '') {
				taken[u.hostel_id] = (taken[u.hostel_id] || 0) + 1;
			} else {
				available[u.hostel_id] = (available[u.hostel_id] || 0) + 1;
			}
		});

		function fmt(t, a) { return (t || 0) + ' / ' + (a || 0); }
		var totalT = 0, totalA = 0;

		if (currentHostelId > 0) {
			totalT = taken[currentHostelId] || 0;
			totalA = available[currentHostelId] || 0;
			$('#statTotal').text(fmt(totalT, totalA));
		} else {
			ptHostelList.forEach(function (h) { totalT += (taken[h.id] || 0); totalA += (available[h.id] || 0); });
			$('#statTotal').text(fmt(totalT, totalA));
		}
		ptHostelList.forEach(function (h) {
			var statEl = $('#stat-hostel-' + h.id);
			if (statEl.length) {
				statEl.text(fmt(taken[h.id], available[h.id]));
			}
		});
	}

	function loadStats(sessionId) {
		$.ajax({
			url: 'php/fetch_assigned_room.php',
			type: 'GET',
			dataType: 'json',
			data: { hostel_id: 0, session_id: sessionId },
			success: function (data) {
				if (data && data.status === 'success') {
					updateStatsFrom(data.users || []);
				}
			}
		});
	}

	function fetchAssignedRooms() {
		var sessionId = ptGetSessionId();
		var wrap = $('#userTable').closest('.pt-table-wrap')[0];
		if (window.PT && wrap) { PT.tableLoading(wrap, true); }
		$.ajax({
			url: 'php/fetch_assigned_room.php',
			type: 'GET',
			dataType: 'json',
			data: { hostel_id: currentHostelId, session_id: sessionId },
			success: function (data) {
				if (window.PT && wrap) { PT.tableLoading(wrap, false); }
				if (!data || data.status !== 'success') {
					if (window.PT) { PT.error((data && data.message) ? data.message : 'Could not load assigned rooms.'); }
					return;
				}
				ptUsers = data.users || [];
				currentSessionId = data.session_id || sessionId;
				destroyTables();
				renderTable();
				updateStatsFrom(ptUsers);
			},
			error: function () {
				if (window.PT && wrap) { PT.tableLoading(wrap, false); }
				if (window.PT) { PT.error('Could not load assigned rooms. Please try again.'); }
			}
		});
	}

	function getFilteredUsers() {
		var f = {
			name: String($('#searchName').val() || '').toLowerCase(),
			matric: String($('#searchMatric').val() || '').toLowerCase(),
			dept: String($('#searchDepartment').val() || '').toLowerCase(),
			level: String($('#searchLevel').val() || '').toLowerCase(),
			snum: String($('#searchStudentNumber').val() || '').toLowerCase(),
			room: String($('#searchRoomBunk').val() || '').toLowerCase(),
			bed: String($('#searchBedSpace').val() || '').toLowerCase()
		};
		return ptUsers.filter(function (u) {
			return (!f.name || String(u.student_name || '').toLowerCase().includes(f.name))
				&& (!f.matric || String(u.matric_no || '').toLowerCase().includes(f.matric))
				&& (!f.dept || String(u.department || '').toLowerCase().includes(f.dept))
				&& (!f.level || String(u.level || '').toLowerCase().includes(f.level))
				&& (!f.snum || String(u.student_number || '').toLowerCase().includes(f.snum))
				&& (!f.room || String(u.room_bunk || '').toLowerCase().includes(f.room))
				&& (!f.bed || String(u.bed_space || '').toLowerCase().includes(f.bed));
		});
	}

	function buildCsv(rows) {
		var headers = ['Serial Number', 'Student Name', 'Matric No', 'Department', 'Parent Number', 'Level', 'Student Number', 'Room Bunk', 'Bed Space', 'Hostel'];
		var lines = [headers.map(function (h) { return '"' + h + '"'; }).join(',')];
		rows.forEach(function (u, i) {
			var vals = [i + 1, u.student_name, u.matric_no, u.department, u.parent_number, u.level, u.student_number, u.room_bunk, u.bed_space, (u.hostel_name || getHostelName(u.hostel_id))];
			lines.push(vals.map(function (v) {
				var s = String(v == null ? '' : v);
				return (s.includes(',') || s.includes('"') || s.includes('\n')) ? '"' + s.replace(/"/g, '""') + '"' : s;
			}).join(','));
		});
		return lines.join('\n');
	}

	function downloadCsvFile(content, filename) {
		var blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
		var link = document.createElement('a');
		var url = URL.createObjectURL(blob);
		link.setAttribute('href', url);
		link.setAttribute('download', filename);
		link.style.display = 'none';
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
	}

	function openConfirmModal(title, message, onConfirm) {
		ptConfirmAction = onConfirm;
		$('#ptConfirmTitle').text(title);
		$('#ptConfirmMessage').text(message);
		bootstrap.Modal.getOrCreateInstance(document.getElementById('ptConfirmModal')).show();
	}

	function revokeAssignment() {
		var btn = $(this);
		var sn = btn.data('sn');
		var name = btn.data('name');
		openConfirmModal('Revoke Room Assignment', 'Revoke the room assignment for ' + (name || 'this student') + '? This action cannot be undone.', function () {
			$.ajax({
				url: 'php/revoke_room.php',
				type: 'POST',
				data: { id: sn },
				dataType: 'json',
				success: function (response) {
					if (response.status === 'success') {
						if (window.PT) { PT.success(response.message || 'Room assignment revoked successfully!'); }
						fetchAssignedRooms();
					} else {
						if (window.PT) { PT.error('Error: ' + response.message); }
					}
				},
				error: function () {
					if (window.PT) { PT.error('An error occurred. Please try again.'); }
				}
			});
		});
	}

	function openUpdateModal() {
		var btn = $(this);
		$('#studentId').val(btn.data('id'));
		$('#studentHostelId').val(btn.data('hostel-id') || '');
		$('#studentName').val(btn.data('student-name'));
		$('#matricNo').val(btn.data('matric-no'));
		$('#department').val(btn.data('department'));
		$('#parentNumber').val(btn.data('parent-number'));
		$('#level').val(btn.data('level'));
		$('#studentNumber').val(btn.data('student-number'));
		$('#roomBunk').val(btn.data('room-bunk'));
		$('#bedSpace').val(btn.data('bed-space'));
		bootstrap.Modal.getOrCreateInstance(document.getElementById('updateStudentModal')).show();
	}

	function saveStudentChanges() {
		var btn = this;
		var formData = $('#updateStudentForm').serialize();
		PT.btnLoading(btn, true);
		$.ajax({
			url: 'php/update_student_room.php',
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function (response) {
				PT.btnLoading(btn, false);
				if (response.status === 'success') {
					if (window.PT) { PT.success(response.message); }
					bootstrap.Modal.getInstance(document.getElementById('updateStudentModal'))?.hide();
					fetchAssignedRooms();
				} else {
					if (window.PT) { PT.error('Error: ' + response.message); }
				}
			},
			error: function () {
				PT.btnLoading(btn, false);
				if (window.PT) { PT.error('An error occurred during update. Please try again.'); }
			}
		});
	}

	function openCsvModal() {
		if (currentHostelId > 0) {
			$('#hostelSelector').val(String(currentHostelId));
		} else if (ptHostelList.length) {
			$('#hostelSelector').val(String(ptHostelList[0].id));
		}
		$('#sessionSelector').val(String(currentSessionId));
		bootstrap.Modal.getOrCreateInstance(document.getElementById('csvUploadModal')).show();
	}

	function submitCsv() {
		var btn = this;
		var formData = new FormData($('#csvUploadForm')[0]);
		PT.btnLoading(btn, true);
		$.ajax({
			url: 'php/upload_csv.php',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function (response) {
				PT.btnLoading(btn, false);
				if (response.status === 'success') {
					if (window.PT) { PT.success(response.message); }
					bootstrap.Modal.getInstance(document.getElementById('csvUploadModal'))?.hide();
					var uploadedHostel = parseInt($('#hostelSelector').val(), 10) || 0;
					var uploadedSession = parseInt($('#sessionSelector').val(), 10) || 0;
					if (uploadedHostel > 0) { currentHostelId = uploadedHostel; }
					if (uploadedSession > 0) {
						currentSessionId = uploadedSession;
						$('#assignSessionSelector').val(String(uploadedSession));
					}
					$('.nav-link[data-hostel-id="' + currentHostelId + '"]').tab('show');
					fetchAssignedRooms();
					if (!$('#pt-manage-uploads').hasClass('d-none')) { loadUploadBatches(); }
				} else {
					var errorMsg = response.message;
					if (response.errors && response.errors.length > 0) {
						errorMsg += '<br>Details:<ul>' + response.errors.map(function (e) { return '<li>' + e + '</li>'; }).join('') + '</ul>';
					}
					if (window.PT) { PT.error(errorMsg); }
				}
			},
			error: function () {
				PT.btnLoading(btn, false);
				if (window.PT) { PT.error('An error occurred. Please try again.'); }
			}
		});
	}

	function toggleDownloadCSV() {
		if (currentHostelId > 0) {
			$('#downloadCSV, #downloadCSVTemplate').show();
		} else {
			$('#downloadCSV, #downloadCSVTemplate').hide();
		}
	}

	function showManageUploads() {
		$('#pt-assign-view').addClass('d-none');
		$('#pt-manage-uploads').removeClass('d-none');
		$('#manageSession').val(String(ptGetSessionId() || 0));
		var h = currentHostelId > 0 ? currentHostelId : (ptHostelList.length ? ptHostelList[0].id : 0);
		$('#manageHostel').val(String(h));
		loadUploadBatches();
	}

	function showAssignView() {
		$('#pt-manage-uploads').addClass('d-none');
		$('#pt-assign-view').removeClass('d-none');
	}

	function loadUploadBatches() {
		var sess = $('#manageSession').val();
		var h = $('#manageHostel').val();
		var wrap = $('#uploadBatchTable').closest('.table-responsive')[0];
		if (window.PT && wrap) { PT.tableLoading(wrap, true); }
		if (!sess || !h) {
			if (window.PT && wrap) { PT.tableLoading(wrap, false); }
			renderBatches([]);
			return;
		}
		$.ajax({
			url: 'php/fetch_upload_batches.php',
			dataType: 'json',
			data: { session_id: sess, hostel_id: h },
			success: function (data) {
				if (window.PT && wrap) { PT.tableLoading(wrap, false); }
				if (!data || data.status !== 'success') {
					if (window.PT) { PT.error((data && data.message) ? data.message : 'Could not load upload batches.'); }
					return;
				}
				renderBatches(data.batches || []);
			},
			error: function () {
				if (window.PT && wrap) { PT.tableLoading(wrap, false); }
				if (window.PT) { PT.error('Could not load upload batches. Please try again.'); }
			}
		});
	}

	function renderBatches(batches) {
		var tbody = $('#uploadBatchTable tbody').empty();
		if (!batches.length) {
			$('#batchEmptyHint').text('No uploads found for the selected session and hostel.');
			return;
		}
		$('#batchEmptyHint').text('');
		batches.forEach(function (b) {
			var label = b.legacy
				? 'Earlier uploads (no file record)'
				: (b.file_name ? b.file_name : 'Upload #' + b.id);
			var uploadedBy = b.legacy ? '&mdash;' : esc(b.uploaded_by_name || '&mdash;');
			var uploadedAt = b.legacy ? '&mdash;' : esc(b.created_at);
			var confirmMsg = b.legacy
				? 'Delete all earlier uploaded data for this hostel and session (' + b.total_rows + ' rows)? This removes the room assignments and reservations it created so you can re-upload a corrected file.'
				: 'Delete this upload (' + b.total_rows + ' rows)? This removes the room assignments and reservations it created so you can re-upload a corrected file.';
			var row = `
				<tr>
					<td>${b.legacy ? '<span class="badge bg-warning text-dark">Earlier</span>' : '<span class="badge bg-success">File</span>'}</td>
					<td>${esc(label)}</td>
					<td>${esc(b.total_rows)}</td>
					<td>${b.error_rows ? '<span class="text-danger">' + esc(b.error_rows) + '</span>' : '0'}</td>
					<td>${uploadedBy}</td>
					<td>${uploadedAt}</td>
					<td class="text-center text-nowrap">
						<button class="btn btn-danger btn-sm px-2 delete-batch-btn" title="Delete this upload"
							data-id="${esc(b.id)}" data-legacy="${b.legacy ? '1' : '0'}" data-rows="${esc(b.total_rows)}" data-confirm="${esc(confirmMsg)}"><i class="fas fa-trash-alt"></i></button>
					</td>
				</tr>
			`;
			tbody.append(row);
		});
	}

	function deleteUploadBatch() {
		var btn = $(this);
		var id = btn.data('id');
		var legacy = btn.data('legacy') === 1;
		var rows = btn.data('rows');
		var confirmMsg = btn.data('confirm') || 'Delete this uploaded data? This cannot be undone.';
		openConfirmModal('Delete Uploaded Data', confirmMsg, function () {
			var payload = { batch_id: id };
			if (legacy) {
				payload.hostel_id = $('#manageHostel').val();
				payload.session_id = $('#manageSession').val();
			}
			$.ajax({
				url: 'php/delete_upload_batch.php',
				type: 'POST',
				data: payload,
				dataType: 'json',
				success: function (response) {
					if (response.status === 'success') {
						if (window.PT) { PT.success(response.message); }
						loadUploadBatches();
						fetchAssignedRooms();
						loadStats(ptGetSessionId());
					} else {
						if (window.PT) { PT.error('Error: ' + response.message); }
					}
				},
				error: function () {
					if (window.PT) { PT.error('An error occurred. Please try again.'); }
				}
			});
		});
	}

	function printTable() {
		var rows = getFilteredUsers();
		var rowsHtml = rows.map(function (u, i) {
			return '<tr><td>' + (i + 1) + '</td><td>' + esc(u.student_name) + '</td><td>' + esc(u.matric_no) + '</td><td>' + esc(u.department) + '</td><td>' + esc(u.parent_number) + '</td><td>' + esc(u.level) + '</td><td>' + esc(u.student_number) + '</td><td>' + esc(u.room_bunk) + '</td><td>' + esc(u.bed_space) + '</td><td>' + esc(u.hostel_name || getHostelName(u.hostel_id)) + '</td></tr>';
		}).join('');
		var w = window.open('', '', 'height=600,width=800');
		w.document.write('<html><head><title>Assigned Rooms</title>');
		w.document.write('<style>body{font-family:Arial,sans-serif;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:6px;font-size:12px;} th{background:#f0f0f0;}</style>');
		w.document.write('</head><body>');
		w.document.write('<h3>Assigned Rooms</h3>');
		w.document.write('<table><thead><tr><th>S/N</th><th>Student Name</th><th>Matric No</th><th>Department</th><th>Parent Number</th><th>Level</th><th>Student Number</th><th>Room Bunk</th><th>Bed Space</th><th>Hostel</th></tr></thead><tbody>' + rowsHtml + '</tbody></table>');
		w.document.close();
		w.print();
	}

	function ptSetupAssignRoom() {
	// Hostel tabs (manual pill switching; refetches data per hostel)
	$('#assignHostelTabs').on('click', '.pt-assign-tab', function (e) {
		e.preventDefault();
		$('#assignHostelTabs .pt-assign-tab').removeClass('active');
		$(this).addClass('active');
		if ($(this).data('mode') === 'uploads') {
			showManageUploads();
		} else {
			showAssignView();
			currentHostelId = parseInt($(this).data('hostelId'), 10) || 0;
			toggleDownloadCSV();
			fetchAssignedRooms();
		}
	});

		// Session selector
		$('#assignSessionSelector').on('change', function () {
			currentSessionId = parseInt($(this).val(), 10) || 0;
			fetchAssignedRooms();
		});

		// Row actions (delegated; survives DataTables re-renders)
		$('#userTable').on('click', '.assign-update-btn', openUpdateModal);
		$('#userTable').on('click', '.assign-revoke-btn', revokeAssignment);

		// Toolbar
		$('#saveStudentChanges').on('click', saveStudentChanges);
		$('#submitCSV').on('click', submitCsv);
		$('#uploadCSVBtn').on('click', openCsvModal);
		$('#manageUploadsBtn').on('click', showManageUploads);
		$('#backToAssignBtn').on('click', showAssignView);
		$('#downloadCSV').on('click', function () {
			downloadCsvFile(buildCsv(getFilteredUsers()), 'assigned_rooms.csv');
		});
		$('#downloadCSVTemplate').on('click', function () {
			downloadCsvFile(buildCsv(getFilteredUsers()), 'assigned_rooms_template.csv');
		});
		$('#downloadFilteredExcel').on('click', function () {
			var rows = getFilteredUsers();
			if (!rows.length) {
				if (window.PT) { PT.warning('No data to export. Please check your filters.'); }
				return;
			}
			downloadCsvFile('\uFEFF' + buildCsv(rows), 'assigned_rooms_filtered.csv');
		});
		$('#printTable').on('click', printTable);
		$('#clearFilters').on('click', function () {
			$('#searchName, #searchMatric, #searchDepartment, #searchLevel, #searchStudentNumber, #searchRoomBunk, #searchBedSpace').val('');
			applyColumnFilters();
		});

		// Column search filters
		$('#searchName, #searchMatric, #searchDepartment, #searchLevel, #searchStudentNumber, #searchRoomBunk, #searchBedSpace').on('keyup change', applyColumnFilters);

		// Auto-derive the bed space when the room bunk changes
		$('#roomBunk').on('change', function () {
			$('#bedSpace').val('');
		});

		// Confirm modal delete button
		$('#ptConfirmDeleteBtn').on('click', function () {
			var action = ptConfirmAction;
			ptConfirmAction = null;
			bootstrap.Modal.getOrCreateInstance(document.getElementById('ptConfirmModal')).hide();
			if (action) { action(); }
		});

		// Manage Uploads tab
		$('#loadBatchesBtn').on('click', loadUploadBatches);
		$('#manageSession, #manageHostel').on('change', loadUploadBatches);
		$('#uploadBatchTable').on('click', '.delete-batch-btn', deleteUploadBatch);

		toggleDownloadCSV();
		fetchAssignedRooms();
	}

	// Initial load: runs on direct page load and after AJAX navigation swaps content in.
	// DOMContentLoaded covers direct loads (jQuery is present by then); pt:content-loaded
	// covers AJAX swaps via pt-nav.js. Each execution binds to fresh swapped-in elements.
	document.addEventListener('DOMContentLoaded', ptSetupAssignRoom);
	document.addEventListener('pt:content-loaded', ptSetupAssignRoom);
</script>

</div>

<?php include 'includes/footer.php'; ?>
