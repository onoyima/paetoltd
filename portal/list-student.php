<?php
require_once __DIR__ . '/php/rbac.php';
pt_require_page('list_student');

include 'php/fetch_admin_info.php';
include 'php/config.php';

$pageTitle = 'List Students';
$pageHeader = 'List Students';

// Fetch all registered students server-side — no AJAX, no JSON
$students = [];
$stmt = $conn->prepare(
    "SELECT id, regNo, firstName, middleName, lastName, gender, department, level, contactNo, parentPhone, email
     FROM userregistration
     ORDER BY firstName ASC, lastName ASC"
);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}
?>
<?php include 'includes/head.php'; ?>

<body>

	<?php include 'includes/header.php'; ?>
	<?php include 'includes/sidebar.php'; ?>

	<!--**********************************
	Content body start
	***********************************-->
	<div class="content-body" id="pt-content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header d-flex justify-content-between align-items-center">
							<h4 class="card-title">List Students <span class="badge bg-secondary ms-2"><?php echo count($students); ?></span></h4>
							<div>
								<input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search by name, reg no, email..." style="width: 300px; display: inline-block;">
							</div>
						</div>
						<div class="card-body">
							<div class="table-responsive pt-table-wrap">
								<table class="table display mb-4 dataTablesCard card-table" id="userTable">
									<thead>
										<tr>
											<th>S/N</th>
											<th>Reg No/Matric No.</th>
											<th>First Name</th>
											<th>Middle Name</th>
											<th>Last Name</th>
											<th>Gender</th>
											<th>Department</th>
											<th>Level</th>
											<th>Contact No</th>
											<th>Email</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										<?php if (empty($students)): ?>
											<tr><td colspan="11" class="text-center text-muted py-4">No registered students found.</td></tr>
										<?php else: ?>
											<?php foreach ($students as $i => $s): ?>
												<tr>
													<td><?php echo $i + 1; ?></td>
													<td><?php echo htmlspecialchars($s['regNo'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['firstName'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['middleName'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['lastName'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['gender'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['department'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['level'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['contactNo'] ?? ''); ?></td>
													<td><?php echo htmlspecialchars($s['email'] ?? ''); ?></td>
													<td>
														<button class="btn btn-primary btn-sm edit-student-btn"
															data-id="<?php echo (int)$s['id']; ?>">
															<i class="fas fa-edit me-1"></i>Edit
														</button>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Edit Student Modal -->
		<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form id="editStudentForm" enctype="multipart/form-data">
							<input type="hidden" id="editUserId" name="userId">
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Registration Number</label>
									<input type="text" class="form-control" id="editRegNo" name="regNo" required>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Gender</label>
									<select class="form-control" id="editGender" name="gender" required>
										<option value="Male">Male</option>
										<option value="Female">Female</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">First Name</label>
									<input type="text" class="form-control" id="editFirstName" name="firstName" required>
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Middle Name</label>
									<input type="text" class="form-control" id="editMiddleName" name="middleName">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Last Name</label>
									<input type="text" class="form-control" id="editLastName" name="lastName" required>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Department</label>
									<input type="text" class="form-control" id="editDepartment" name="department">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Level</label>
									<select class="form-control" id="editLevel" name="level">
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
								<div class="col-md-6 mb-3">
									<label class="form-label">Contact Number</label>
									<input type="text" class="form-control" id="editContactNo" name="contactNo" required>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Parent Phone</label>
									<input type="text" class="form-control" id="editParentPhone" name="parentPhone">
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Email</label>
									<input type="email" class="form-control" id="editEmail" name="email" required>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Profile Image</label>
									<input type="file" class="form-control" id="editUserImage" name="userImage" accept="image/*">
									<div class="mt-2">
										<img id="editImagePreview" src="" alt="Preview" style="max-width: 80px; max-height: 80px; border-radius: 50%; display: none;">
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-warning" id="resetPasswordBtn">
							<i class="fas fa-key me-1"></i> Reset Password
						</button>
						<div>
							<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
							<button type="button" class="btn btn-primary" id="saveStudentBtn">Save Changes</button>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
	<!--**********************************
		Content body end
	***********************************-->

	<?php include 'includes/footer.php'; ?>

	<script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
	<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
	<script>
	function initListStudent() {
		if (window._listStudentInit) return;
		window._listStudentInit = true;

		// Initialise DataTables on the already-populated table (server-rendered rows)
		var userTable = $('#userTable').DataTable({
			pageLength: 25,
			order: [[2, 'asc']], // sort by First Name
			language: { search: 'Search students:' }
		});

		$('#searchInput').on('keyup', function() {
			userTable.search(this.value).draw();
		});

		// Edit button — fetch full record (including image) on demand
		$('#userTable').on('click', '.edit-student-btn', function() {
			// Use attr() instead of data() — DataTables re-renders rows and can
			// break jQuery's .data() cache on the original elements.
			var id = $(this).attr('data-id');
			fetch('php/fetch_user_single.php?id=' + id)
				.then(function(res) { return res.json(); })
				.then(function(resp) {
					console.log('fetch_user_single response:', resp);
					if (resp.status !== 'success') {
						if (window.PT) { PT.error(resp.message || 'Could not load student details.'); }
						return;
					}
					var user = resp.user;
					$('#editUserId').val(user.id);
					$('#editRegNo').val(user.regNo);
					$('#editFirstName').val(user.firstName);
					$('#editMiddleName').val(user.middleName);
					$('#editLastName').val(user.lastName);
					$('#editGender').val(user.gender);
					$('#editDepartment').val(user.department || '');
					$('#editLevel').val(user.level || '');
					$('#editContactNo').val(user.contactNo);
					$('#editParentPhone').val(user.parentPhone || '');
					$('#editEmail').val(user.email);

					if (user.userImage) {
						$('#editImagePreview').attr('src', 'data:image/jpeg;base64,' + user.userImage).show();
					} else {
						$('#editImagePreview').hide();
					}
					$('#editUserImage').val('');

					var modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
					modal.show();
				})
				.catch(function(err) {
					console.error('Edit fetch error:', err);
					if (window.PT) { PT.error('Error loading student details.'); }
				});
		});

		$('#editUserImage').on('change', function() {
			var file = this.files[0];
			if (file) {
				var reader = new FileReader();
				reader.onload = function(e) {
					$('#editImagePreview').attr('src', e.target.result).show();
				};
				reader.readAsDataURL(file);
			}
		});

		// Save Changes
		$('#saveStudentBtn').on('click', function() {
			var formData = new FormData();
			formData.append('userId', $('#editUserId').val());
			formData.append('regNo', $('#editRegNo').val());
			formData.append('firstName', $('#editFirstName').val());
			formData.append('middleName', $('#editMiddleName').val());
			formData.append('lastName', $('#editLastName').val());
			formData.append('gender', $('#editGender').val());
			formData.append('department', $('#editDepartment').val());
			formData.append('level', $('#editLevel').val());
			formData.append('contactNo', $('#editContactNo').val());
			formData.append('parentPhone', $('#editParentPhone').val());
			formData.append('email', $('#editEmail').val());

			var imageFile = $('#editUserImage')[0].files[0];
			if (imageFile) {
				formData.append('userImage', imageFile);
			}

			fetch('php/update_user_full.php', {
				method: 'POST',
				body: formData
			})
			.then(function(res) { return res.json(); })
			.then(function(response) {
				if (response.status === 'success') {
					bootstrap.Modal.getInstance(document.getElementById('editStudentModal')).hide();
					if (window.PT) { PT.success('Student updated! Refreshing list...'); }
					// Reload page to get fresh server-rendered data
					setTimeout(function() { location.reload(); }, 1000);
				} else {
					if (window.PT) { PT.error(response.message || 'Update failed'); }
				}
			})
			.catch(function() {
				if (window.PT) { PT.error('Error updating student. Please try again.'); }
			});
		});

		// Reset Password
		$('#resetPasswordBtn').on('click', function() {
			var email = $('#editEmail').val();
			var studentName = ($('#editFirstName').val() + ' ' + $('#editLastName').val()).trim() || 'this student';
			if (!email) {
				if (window.PT) { PT.error('No email address found for this student.'); }
				return;
			}
			if (!confirm('Reset password for ' + studentName + '?\n\nTheir new password will be: welcome')) {
				return;
			}
			var btn = $(this);
			btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Resetting...');
			var formData = new FormData();
			formData.append('email', email);
			formData.append('type', 'user');
			fetch('php/reset.php', { method: 'POST', body: formData })
				.then(function(res) { return res.json(); })
				.then(function(resp) {
					if (resp.success) {
						if (window.PT) { PT.success('Password reset to "welcome" successfully!'); }
					} else {
						if (window.PT) { PT.error(resp.error || 'Password reset failed.'); }
					}
				})
				.catch(function() {
					if (window.PT) { PT.error('Error resetting password. Please try again.'); }
				})
				.finally(function() {
					btn.prop('disabled', false).html('<i class="fas fa-key me-1"></i> Reset Password');
				});
		});
	}

	$(document).ready(initListStudent);
	document.addEventListener('pt:content-loaded', initListStudent);
	</script>

</body>
</html>
