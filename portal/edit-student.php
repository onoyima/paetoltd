<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + manage_hostel permission (editing student records)
pt_require_page('manage_hostel');

include 'php/fetch_admin_info.php';

$pageTitle = 'Edit Student';
$pageHeader = 'Dashboard';
?>
<?php include 'includes/head.php'; ?>
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
						<div class="table-responsive">
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


		</div>
	</div>
</div>

<script>
	// Fetch user data on page load
	fetchUserData();

	// Function to fetch user data from server
	function fetchUserData() {
		$.getJSON('php/fetch_user.php', function (data) {
			if (data.status === 'success') {
				displayUsers(data.users);
			} else {
				console.error('Error:', data.message);
			}
		}).fail(function (error) {
			console.error('Error fetching user data:', error);
		});
	}

	// Function to display users in the table
	function esc(s) {
		return String(s ?? '').replace(/[&<>"']/g, function (c) {
			return { '&': '&', '<': '<', '>': '>', '"': '"', "'": '\'' }[c];
		});
	}

	function displayUsers(users) {
		var tableBody = $('#userTable tbody');
		tableBody.empty(); // Clear existing rows

		users.forEach(function (user, index) {
			var row = `
			<tr data-userid="${esc(user.id)}">
				<td>${index + 1}</td>
				<td><input type="text" class="form-control" value="${esc(user.regNo)}" name="regNo"></td>
				<td><input type="text" class="form-control" value="${esc(user.firstName)}" name="firstName"></td>
				<td><input type="text" class="form-control" value="${esc(user.middleName)}" name="middleName"></td>
				<td><input type="text" class="form-control" value="${esc(user.lastName)}" name="lastName"></td>
				<td><input type="text" class="form-control" value="${esc(user.gender)}" name="gender"></td>
				<td><input type="text" class="form-control" value="${esc(user.contactNo)}" name="contactNo"></td>
				<td><input type="email" class="form-control" value="${esc(user.email)}" name="email"></td>
				<td>
					<button class="btn btn-primary btn-sm btn-save">Edit</button>
				</td>
			</tr>
		`;
			tableBody.append(row);
		});

		// Attach event listener for save button click
		$(document).on('click', '.btn-save', function () {
			var row = $(this).closest('tr');
			var userId = row.attr('data-userid');
			var userData = {
				regNo: row.find('input[name="regNo"]').val(),
				firstName: row.find('input[name="firstName"]').val(),
				middleName: row.find('input[name="middleName"]').val(),
				lastName: row.find('input[name="lastName"]').val(),
				gender: row.find('input[name="gender"]').val(),
				contactNo: row.find('input[name="contactNo"]').val(),
				email: row.find('input[name="email"]').val()
			};

			// Send updated data to server for saving
			$.ajax({
				url: 'php/update_user.php',
				method: 'POST',
				data: { userId: userId, userData: userData },
				dataType: 'json',
				success: function (response) {
					if (response.status === 'success') {
						if (window.PT) window.PT.success('User data updated successfully.');
					} else {
						if (window.PT) window.PT.error('Error updating user data: ' + (response.message || ''));
					}
				},
				error: function (error) {
					if (window.PT) window.PT.error('Error updating user data');
					console.error('Error updating user data:', error);
				}
			});
		});
	}
</script>

</div>

<?php include 'includes/footer.php'; ?>