<?php
require_once __DIR__ . '/php/rbac.php';
pt_require_page('list_student');

include 'php/fetch_admin_info.php';

$pageTitle = 'List Students';
$pageHeader = 'List Students';
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
							<h4 class="card-title">List Students</h4>
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
											<th>Reg No</th>
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
									<tbody></tbody>
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
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary" id="saveStudentBtn">Save Changes</button>
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

		var allUsers = [];
		var userTable;

		userTable = $('#userTable').DataTable({
			data: [],
			columns: [
				{ data: null, render: function(data, type, row, meta) { return meta.row + 1; } },
				{ data: 'regNo' },
				{ data: 'firstName' },
				{ data: 'middleName' },
				{ data: 'lastName' },
				{ data: 'gender' },
				{ data: 'department' },
				{ data: 'level' },
				{ data: 'contactNo' },
				{ data: 'email' },
				{
					data: null,
					orderable: false,
					searchable: false,
					render: function(data) {
						return '<button class="btn btn-primary btn-sm edit-student-btn" data-id="' + data.id + '"><i class="fas fa-edit me-1"></i>Edit</button>';
					}
				}
			],
			pageLength: 25,
			order: [[1, 'asc']],
			language: { search: 'Search students:' }
		});

		fetch('php/fetch_user.php')
			.then(function(res) { return res.json(); })
			.then(function(data) {
				if (data.status === 'success') {
					allUsers = data.users;
					userTable.clear().rows.add(allUsers).draw();
				}
			})
			.catch(function(err) {
				if (window.PT) { PT.error('Failed to load students.'); }
				console.error('Fetch error:', err);
			});

		$('#searchInput').on('keyup', function() {
			userTable.search(this.value).draw();
		});

		$('#userTable').on('click', '.edit-student-btn', function() {
			var id = $(this).data('id');
			var user = allUsers.find(function(u) { return u.id == id; });
			if (!user) return;

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
					fetch('php/fetch_user.php')
						.then(function(res) { return res.json(); })
						.then(function(data) {
							if (data.status === 'success') {
								allUsers = data.users;
								userTable.clear().rows.add(allUsers).draw();
							}
						});
					if (window.PT) { PT.success('Student updated successfully!'); }
				} else {
					if (window.PT) { PT.error(response.message || 'Update failed'); }
				}
			})
			.catch(function() {
				if (window.PT) { PT.error('Error updating student. Please try again.'); }
			});
		});
	}

	$(document).ready(initListStudent);
	document.addEventListener('pt:content-loaded', initListStudent);
	</script>

</body>
</html>
