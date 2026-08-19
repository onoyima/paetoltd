<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + list_student permission
pt_require_page('list_student');

include 'php/fetch_admin_info.php';

$pageTitle = 'List Student';
$pageHeader = 'List Student';
?>
<?php include 'includes/head.php'; ?>
<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!--**********************************
	Content body end
***********************************-->

<div class="content-body" id="pt-content">
	<div class="container-fluid">
		<div class="row page-titles">
		</div>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title"> List Student</h4>
					</div>
					<div class="card-body">
						<div class="table-responsive pt-table-wrap">
							<table class="table" id="userTable">
								<thead>
									<tr>
										<th>S/N</th>
										<th>Reg No</th>
										<th>First Name</th>
										<th>Middle Name</th>
										<th>Last Name</th>
										<th>Gender</th>
										<th>Contact No</th>
										<th>Email</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<!-- Content will be dynamically loaded here via JavaScript -->
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div><a href="edit-student.php" class="btn btn-primary btn-sm btn-save col-4">Edit Student</a></div>

		</div>
	</div>
</div>

<script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
<script>
	function esc(s) {
		return String(s ?? '').replace(/[&<>"']/g, function (c) {
			return { '&': '&', '<': '<', '>': '>', '"': '"', "'": '\'' }[c];
		});
	}

	// Execute immediately (PTNav runs scripts after AJAX swap)
	var wrap = document.querySelector('.pt-table-wrap');
	if (window.PT) { window.PT.tableLoading(wrap, true); }
	$.getJSON('php/fetch_user.php', function (data) {
		if (data.status === 'success') {
			var users = data.users;
			var tableBody = $('#userTable tbody');
			tableBody.empty();

			users.forEach(function (user, index) {
				var row = `
					<tr>
						<td>${index + 1}</td>
						<td>${esc(user.regNo)}</td>
						<td>${esc(user.firstName)}</td>
						<td>${esc(user.middleName)}</td>
						<td>${esc(user.lastName)}</td>
						<td>${esc(user.gender)}</td>
						<td>${esc(user.contactNo)}</td>
						<td>${esc(user.email)}</td>
						<td>
							<a href="edit-student.php" class="btn btn-primary btn-sm px-2" title="Edit"><i class="fas fa-pencil-alt"></i> Edit</a>
						</td>
					</tr>
				`;
				tableBody.append(row);
			});

			$('#userTable').DataTable({
			 ordering: true,
			 searching: true,
			 pageLength: 10,
			 lengthMenu: [10, 25, 50, 100],
			 language: { emptyTable: "No students found" }
			});

			if (window.PT) { window.PT.tableLoading(wrap, false); }
		} else {
			console.error('Error:', data.message);
			if (window.PT) { window.PT.tableLoading(wrap, false); }
		}
	}).fail(function (error) {
		console.error('Error fetching user data:', error);
		if (window.PT) { window.PT.tableLoading(wrap, false); }
	});
</script>

</div>

<?php include 'includes/footer.php'; ?>