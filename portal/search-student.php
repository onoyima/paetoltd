<?php
require_once __DIR__ . '/php/rbac.php';
pt_require_page('list_student');

include 'php/fetch_admin_info.php';
require_once __DIR__ . '/php/academic_helper.php';

$sessions = pt_all_sessions();
$hostels = pt_all_hostels();

$pageTitle = 'Search Student';
$pageHeader = 'Search Student';
?>
<?php include 'includes/head.php'; ?>
<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
	.search-hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; }
	.search-hero h3 { color: #fff; margin-bottom: 0.5rem; }
	.search-hero p { color: rgba(255,255,255,0.8); margin-bottom: 1rem; }
	.search-hero .form-control { border-radius: 8px; font-size: 1.1rem; padding: 0.75rem 1rem; }
	.student-profile-card { border-radius: 12px; overflow: hidden; }
	.student-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #667eea; }
	.info-label { font-size: 0.75rem; text-transform: uppercase; color: #888; margin-bottom: 2px; }
	.info-value { font-size: 0.95rem; font-weight: 500; margin-bottom: 0.75rem; }
	.tab-badge { font-size: 0.7rem; vertical-align: middle; }
	.pt-search-result { cursor: pointer; transition: background 0.15s; }
	.pt-search-result:hover { background: #f0f0f0; }
	.pt-search-result.active { background: #e8f0fe; border-left: 3px solid #667eea; }
	.pt-no-results { text-align: center; padding: 3rem; color: #888; }
	.pt-session-badge { font-size: 0.7rem; }
	.table td { vertical-align: middle; }
</style>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!--**********************************
	Content body start
***********************************-->
<div class="content-body" id="pt-content">
	<div class="container-fluid">

		<!-- Search Hero -->
		<div class="search-hero">
			<h3><i class="fas fa-search me-2"></i>Search Student</h3>
			<p>Find any student's registration, payment, and room assignment records across all sessions.</p>
			<div class="input-group">
				<input type="text" id="globalSearch" class="form-control" placeholder="Search by name, reg number, matric number, email, or phone..." autofocus>
				<button class="btn btn-light" id="searchBtn" type="button"><i class="fas fa-search"></i></button>
			</div>
		</div>

		<div class="row">
			<!-- Left Panel: Search Results List -->
			<div class="col-lg-4 col-xl-3">
				<div class="card" id="resultsCard" style="display:none;">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h6 class="card-title mb-0">Results</h6>
						<span class="badge bg-primary" id="resultCount">0</span>
					</div>
					<div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
						<div id="resultsList"></div>
					</div>
				</div>
			</div>

			<!-- Right Panel: Student Detail -->
			<div class="col-lg-8 col-xl-9">
				<div id="detailPanel">
					<div class="pt-no-results">
						<i class="fas fa-user-graduate fa-3x mb-3" style="color:#ccc;"></i>
						<h5>No student selected</h5>
						<p>Use the search bar above to find a student.</p>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<!-- Edit Registration Modal -->
<div class="modal fade" id="editRegModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Registration</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form id="editRegForm">
					<input type="hidden" id="editRegUserId">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Registration Number</label>
							<input type="text" class="form-control" id="editRegRegNo" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Gender</label>
							<select class="form-control" id="editRegGender" required>
								<option value="Male">Male</option>
								<option value="Female">Female</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 mb-3">
							<label class="form-label">First Name</label>
							<input type="text" class="form-control" id="editRegFirstName" required>
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Middle Name</label>
							<input type="text" class="form-control" id="editRegMiddleName">
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Last Name</label>
							<input type="text" class="form-control" id="editRegLastName" required>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Department</label>
							<input type="text" class="form-control" id="editRegDepartment">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Level</label>
							<select class="form-control" id="editRegLevel">
								<option value="">Select Level</option>
								<option value="100">100 Level</option>
								<option value="200">200 Level</option>
								<option value="300">300 Level</option>
								<option value="400">400 Level</option>
								<option value="500">500 Level</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 mb-3">
							<label class="form-label">Contact Number</label>
							<input type="text" class="form-control" id="editRegContactNo" required>
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Parent Phone</label>
							<input type="text" class="form-control" id="editRegParentPhone">
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Email</label>
							<input type="email" class="form-control" id="editRegEmail" required>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="saveRegBtn"><i class="fas fa-save me-1"></i>Save Changes</button>
			</div>
		</div>
	</div>
</div>

<!-- Edit Room Assignment Modal -->
<div class="modal fade" id="editAssignModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-bed me-2"></i>Edit Room Assignment</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form id="editAssignForm">
					<input type="hidden" id="editAssignId">
					<input type="hidden" id="editAssignHostelId">
					<div class="mb-3">
						<label class="form-label">Student Name</label>
						<input type="text" class="form-control" id="editAssignName" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Matric No</label>
						<input type="text" class="form-control" id="editAssignMatric" required>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Department</label>
							<input type="text" class="form-control" id="editAssignDept" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Level</label>
							<input type="text" class="form-control" id="editAssignLevel" required>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Parent Number</label>
							<input type="text" class="form-control" id="editAssignParent" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Student Number</label>
							<input type="text" class="form-control" id="editAssignStudentNo" required>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Room Bunk</label>
							<input type="text" class="form-control" id="editAssignRoomBunk" required readonly>
							<small class="text-muted">Cannot be changed.</small>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Bed Space</label>
							<input type="text" class="form-control" id="editAssignBedSpace" readonly>
							<small class="text-muted">Cannot be changed.</small>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="saveAssignBtn"><i class="fas fa-save me-1"></i>Save Changes</button>
			</div>
		</div>
	</div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPwdModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-sm modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Password</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body text-center">
				<p>Reset password for <strong id="resetPwdName"></strong>?</p>
				<p class="text-muted small">New password will be: <code>welcome</code></p>
			</div>
			<div class="modal-footer justify-content-center">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-warning" id="confirmResetPwdBtn"><i class="fas fa-key me-1"></i>Reset</button>
			</div>
		</div>
	</div>
</div>

<script>
var ptSearchResults = { users: [], payments: [], assignments: [] };
var ptSelectedUserId = null;

function esc(s) {
	return String(s ?? '').replace(/[&<>"']/g, function (c) {
		return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
	});
}

function doSearch() {
	var q = $('#globalSearch').val().trim();
	if (!q) return;
	$('#resultsCard').show();
	$('#resultsList').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted small">Searching...</p></div>');

	fetch('php/search_student_global.php?q=' + encodeURIComponent(q))
		.then(function(r) { return r.json(); })
		.then(function(data) {
			if (data.status !== 'success') {
				$('#resultsList').html('<div class="alert alert-danger m-3">' + esc(data.message) + '</div>');
				return;
			}
			ptSearchResults = data;
			$('#resultCount').text(data.users.length);
			renderResultsList(data.users);
		})
		.catch(function() {
			$('#resultsList').html('<div class="alert alert-danger m-3">Search failed. Please try again.</div>');
		});
}

function renderResultsList(users) {
	var html = '';
	if (!users.length) {
		html = '<div class="pt-no-results py-4"><i class="fas fa-user-slash fa-2x mb-2" style="color:#ccc;"></i><p>No students found</p></div>';
	} else {
		users.forEach(function(u) {
			var name = ((u.firstName || '') + ' ' + (u.lastName || '')).trim();
			var avatar = u.userImage
				? '<img src="data:image/jpeg;base64,' + u.userImage + '" class="student-avatar me-3" style="width:40px;height:40px;">'
				: '<div class="student-avatar me-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#667eea;color:#fff;font-size:1rem;">' + esc((u.firstName||'')[0] || '?') + esc((u.lastName||'')[0] || '') + '</div>';
			html += '<div class="d-flex align-items-center px-3 py-2 pt-search-result border-bottom" data-uid="' + u.id + '">'
				+ avatar
				+ '<div class="flex-grow-1 overflow-hidden">'
				+ '<div class="fw-semibold text-truncate">' + esc(name) + '</div>'
				+ '<small class="text-muted">' + esc(u.regNo || '') + (u.email ? ' &middot; ' + esc(u.email) : '') + '</small>'
				+ '</div>'
				+ '<i class="fas fa-chevron-right text-muted ms-2"></i>'
				+ '</div>';
		});
	}
	$('#resultsList').html(html);
	// Auto-select first result
	if (users.length > 0) {
		selectStudent(users[0].id);
	}
}

function selectStudent(uid) {
	ptSelectedUserId = uid;
	$('#resultsList .pt-search-result').removeClass('active');
	$('#resultsList .pt-search-result[data-uid="' + uid + '"]').addClass('active');
	renderDetailPanel(uid);
}

function renderDetailPanel(uid) {
	// Find user
	var user = null;
	for (var i = 0; i < ptSearchResults.users.length; i++) {
		if (ptSearchResults.users[i].id == uid) { user = ptSearchResults.users[i]; break; }
	}
	if (!user) return;

	// Filter payments and assignments for this user
	var myPayments = ptSearchResults.payments.filter(function(p) { return p.userId == uid; });
	var myAssignments = ptSearchResults.assignments.filter(function(a) {
		return a.matric_no == user.regNo || a.student_name == ((user.firstName||'') + ' ' + (user.middleName||'') + ' ' + (user.lastName||'')).trim();
	});

	var fullName = ((user.firstName || '') + ' ' + (user.middleName || '') + ' ' + (user.lastName || '')).trim();
	var avatarHtml = user.userImage
		? '<img src="data:image/jpeg;base64,' + user.userImage + '" class="student-avatar">'
		: '<div class="student-avatar d-flex align-items-center justify-content-center" style="background:#667eea;color:#fff;font-size:1.5rem;">' + esc((user.firstName||'')[0] || '?') + esc((user.lastName||'')[0] || '') + '</div>';

	var html = '';

	// Profile Card
	html += '<div class="card student-profile-card mb-4">';
	html += '<div class="card-body">';
	html += '<div class="d-flex align-items-center mb-3">';
	html += avatarHtml;
	html += '<div class="ms-3">';
	html += '<h4 class="mb-0">' + esc(fullName) + '</h4>';
	html += '<span class="text-muted">' + esc(user.regNo || '') + '</span>';
	html += '</div>';
	html += '<div class="ms-auto">';
	html += '<button class="btn btn-primary btn-sm me-1" id="editRegBtn"><i class="fas fa-edit me-1"></i>Edit</button>';
	html += '<button class="btn btn-warning btn-sm" id="resetPwdBtn"><i class="fas fa-key me-1"></i>Reset Password</button>';
	html += '</div>';
	html += '</div>';
	html += '<div class="row">';
	html += '<div class="col-md-3"><div class="info-label">Gender</div><div class="info-value">' + esc(user.gender || '—') + '</div></div>';
	html += '<div class="col-md-3"><div class="info-label">Department</div><div class="info-value">' + esc(user.department || '—') + '</div></div>';
	html += '<div class="col-md-3"><div class="info-label">Level</div><div class="info-value">' + esc(user.level || '—') + '</div></div>';
	html += '<div class="col-md-3"><div class="info-label">Contact</div><div class="info-value">' + esc(user.contactNo || '—') + '</div></div>';
	html += '<div class="col-md-3"><div class="info-label">Parent Phone</div><div class="info-value">' + esc(user.parentPhone || '—') + '</div></div>';
	html += '<div class="col-md-3"><div class="info-label">Email</div><div class="info-value">' + esc(user.email || '—') + '</div></div>';
	html += '</div>';
	html += '</div>';
	html += '</div>';

	// Tabs
	html += '<ul class="nav nav-tabs mb-3" role="tablist">';
	html += '<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#paymentsTab"><i class="fas fa-money-check-alt me-1"></i>Payments <span class="badge bg-secondary tab-badge">' + myPayments.length + '</span></a></li>';
	html += '<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#assignmentsTab"><i class="fas fa-bed me-1"></i>Room Assignments <span class="badge bg-secondary tab-badge">' + myAssignments.length + '</span></a></li>';
	html += '</ul>';

	html += '<div class="tab-content">';

	// Payments Tab
	html += '<div class="tab-pane fade show active" id="paymentsTab">';
	if (myPayments.length === 0) {
		html += '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No payment records found for this student.</div>';
	} else {
		html += '<div class="table-responsive"><table class="table table-hover display" id="paymentTable"><thead><tr>';
		html += '<th>Session</th><th>Hostel</th><th>Payer Name</th><th>Bank</th><th>Status</th><th>Room</th><th>Bed</th><th>Date</th>';
		html += '</tr></thead><tbody>';
		myPayments.forEach(function(p) {
			var statusBadge = p.status === 'Confirmed'
				? '<span class="badge bg-success">Confirmed</span>'
				: '<span class="badge bg-warning text-dark">' + esc(p.status || 'Pending') + '</span>';
			html += '<tr>';
			html += '<td><span class="badge bg-info pt-session-badge">' + esc(p.session_name || '—') + '</span></td>';
			html += '<td>' + esc(p.hostel_name || '—') + '</td>';
			html += '<td>' + esc(p.payers_name || '—') + '</td>';
			html += '<td>' + esc(p.bankName || '—') + '</td>';
			html += '<td>' + statusBadge + '</td>';
			html += '<td>' + esc(p.room || '—') + '</td>';
			html += '<td>' + esc(p.bed || '—') + '</td>';
			html += '<td>' + esc(p.uploadDate || '—') + '</td>';
			html += '</tr>';
		});
		html += '</tbody></table></div>';
	}
	html += '</div>';

	// Assignments Tab
	html += '<div class="tab-pane fade" id="assignmentsTab">';
	if (myAssignments.length === 0) {
		html += '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No room assignments found for this student.</div>';
	} else {
		html += '<div class="table-responsive"><table class="table table-hover display" id="assignTable"><thead><tr>';
		html += '<th>Session</th><th>Hostel</th><th>Name</th><th>Matric No</th><th>Department</th><th>Level</th><th>Room Bunk</th><th>Bed Space</th><th>Action</th>';
		html += '</tr></thead><tbody>';
		myAssignments.forEach(function(a) {
			html += '<tr>';
			html += '<td><span class="badge bg-info pt-session-badge">' + esc(a.session_name || '—') + '</span></td>';
			html += '<td>' + esc(a.hostel_name || '—') + '</td>';
			html += '<td>' + esc(a.student_name || '—') + '</td>';
			html += '<td>' + esc(a.matric_no || '—') + '</td>';
			html += '<td>' + esc(a.department || '—') + '</td>';
			html += '<td>' + esc(a.level || '—') + '</td>';
			html += '<td>' + esc(a.room_bunk || '—') + '</td>';
			html += '<td>' + esc(a.bed_space || '—') + '</td>';
			html += '<td><button class="btn btn-primary btn-sm edit-assign-btn" data-id="' + a.id + '" data-hostel-id="' + a.hostel_id + '" data-name="' + esc(a.student_name || '') + '" data-matric="' + esc(a.matric_no || '') + '" data-dept="' + esc(a.department || '') + '" data-parent="' + esc(a.parent_number || '') + '" data-level="' + esc(a.level || '') + '" data-student-no="' + esc(a.student_number || '') + '" data-room-bunk="' + esc(a.room_bunk || '') + '" data-bed-space="' + esc(a.bed_space || '') + '"><i class="fas fa-edit me-1"></i>Edit</button>';
			html += '</td>';
			html += '</tr>';
		});
		html += '</tbody></table></div>';
	}
	html += '</div>';

	html += '</div>'; // tab-content

	$('#detailPanel').html(html);

	// Init DataTables
	if (jQuery.fn.DataTable) {
		$('#paymentTable').DataTable({ pageLength: 10, order: [[6, 'desc']], searching: false, lengthChange: false });
		$('#assignTable').DataTable({ pageLength: 10, order: [], searching: false, lengthChange: false });
	}

	// Bind edit registration button
	$('#editRegBtn').on('click', function() {
		$('#editRegUserId').val(user.id);
		$('#editRegRegNo').val(user.regNo || '');
		$('#editRegFirstName').val(user.firstName || '');
		$('#editRegMiddleName').val(user.middleName || '');
		$('#editRegLastName').val(user.lastName || '');
		$('#editRegGender').val(user.gender || 'Male');
		$('#editRegDepartment').val(user.department || '');
		$('#editRegLevel').val(user.level || '');
		$('#editRegContactNo').val(user.contactNo || '');
		$('#editRegParentPhone').val(user.parentPhone || '');
		$('#editRegEmail').val(user.email || '');
		new bootstrap.Modal(document.getElementById('editRegModal')).show();
	});

	// Bind reset password button
	$('#resetPwdBtn').on('click', function() {
		$('#resetPwdName').text(fullName || 'this student');
		$('#confirmResetPwdBtn').data('email', user.email || '');
		new bootstrap.Modal(document.getElementById('resetPwdModal')).show();
	});

	// Bind edit assignment buttons
	$('.edit-assign-btn').on('click', function() {
		var btn = $(this);
		$('#editAssignId').val(btn.data('id'));
		$('#editAssignHostelId').val(btn.data('hostel-id'));
		$('#editAssignName').val(btn.data('name'));
		$('#editAssignMatric').val(btn.data('matric'));
		$('#editAssignDept').val(btn.data('dept'));
		$('#editAssignParent').val(btn.data('parent'));
		$('#editAssignLevel').val(btn.data('level'));
		$('#editAssignStudentNo').val(btn.data('student-no'));
		$('#editAssignRoomBunk').val(btn.data('room-bunk'));
		$('#editAssignBedSpace').val(btn.data('bed-space'));
		new bootstrap.Modal(document.getElementById('editAssignModal')).show();
	});
}

// --- Event Handlers ---

$('#searchBtn').on('click', doSearch);
$('#globalSearch').on('keypress', function(e) {
	if (e.which === 13) doSearch();
});

// Click on search result
$(document).on('click', '.pt-search-result', function() {
	var uid = $(this).data('uid');
	selectStudent(uid);
});

// Save Registration
$('#saveRegBtn').on('click', function() {
	var btn = this;
	var formData = new FormData();
	formData.append('userId', $('#editRegUserId').val());
	formData.append('regNo', $('#editRegRegNo').val());
	formData.append('firstName', $('#editRegFirstName').val());
	formData.append('middleName', $('#editRegMiddleName').val());
	formData.append('lastName', $('#editRegLastName').val());
	formData.append('gender', $('#editRegGender').val());
	formData.append('department', $('#editRegDepartment').val());
	formData.append('level', $('#editRegLevel').val());
	formData.append('contactNo', $('#editRegContactNo').val());
	formData.append('parentPhone', $('#editRegParentPhone').val());
	formData.append('email', $('#editRegEmail').val());

	PT.btnLoading(btn, true);
	fetch('php/update_user_full.php', { method: 'POST', body: formData })
		.then(function(r) { return r.json(); })
		.then(function(resp) {
			PT.btnLoading(btn, false);
			if (resp.status === 'success') {
				bootstrap.Modal.getInstance(document.getElementById('editRegModal')).hide();
				PT.success('Registration updated! Refreshing...');
				// Update local data and re-render
				var u = ptSearchResults.users.find(function(x) { return x.id == $('#editRegUserId').val(); });
				if (u) {
					u.regNo = $('#editRegRegNo').val();
					u.firstName = $('#editRegFirstName').val();
					u.middleName = $('#editRegMiddleName').val();
					u.lastName = $('#editRegLastName').val();
					u.gender = $('#editRegGender').val();
					u.department = $('#editRegDepartment').val();
					u.level = $('#editRegLevel').val();
					u.contactNo = $('#editRegContactNo').val();
					u.parentPhone = $('#editRegParentPhone').val();
					u.email = $('#editRegEmail').val();
					renderResultsList(ptSearchResults.users);
					renderDetailPanel(u.id);
				}
			} else {
				PT.error(resp.message || 'Update failed.');
			}
		})
		.catch(function() {
			PT.btnLoading(btn, false);
			PT.error('Error updating registration. Please try again.');
		});
});

// Save Room Assignment
$('#saveAssignBtn').on('click', function() {
	var btn = this;
	var formData = $('#editAssignForm').serialize();

	PT.btnLoading(btn, true);
	$.ajax({
		url: 'php/update_student_room.php',
		type: 'POST',
		data: formData,
		dataType: 'json',
		success: function(resp) {
			PT.btnLoading(btn, false);
			if (resp.status === 'success') {
				bootstrap.Modal.getInstance(document.getElementById('editAssignModal')).hide();
				PT.success('Assignment updated! Refreshing...');
				// Re-search to get fresh data
				doSearch();
			} else {
				PT.error(resp.message || 'Update failed.');
			}
		},
		error: function() {
			PT.btnLoading(btn, false);
			PT.error('Error updating assignment. Please try again.');
		}
	});
});

// Confirm Reset Password
$('#confirmResetPwdBtn').on('click', function() {
	var email = $(this).data('email');
	var btn = this;
	if (!email) { PT.error('No email found.'); return; }

	PT.btnLoading(btn, true);
	var formData = new FormData();
	formData.append('email', email);
	formData.append('type', 'user');
	fetch('php/reset.php', { method: 'POST', body: formData })
		.then(function(r) { return r.json(); })
		.then(function(resp) {
			PT.btnLoading(btn, false);
			bootstrap.Modal.getInstance(document.getElementById('resetPwdModal')).hide();
			if (resp.success) {
				PT.success('Password reset to "welcome" successfully!');
			} else {
				PT.error(resp.error || 'Password reset failed.');
			}
		})
		.catch(function() {
			PT.btnLoading(btn, false);
			PT.error('Error resetting password.');
		});
});
</script>

</div>
<!--**********************************
	Content body end
***********************************-->

<?php include 'includes/footer.php'; ?>
</body>
</html>
