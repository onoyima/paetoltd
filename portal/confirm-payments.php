<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + confirm_payment permission
pt_require_page('confirm_payment');

include 'php/fetch_admin_info.php';
require_once __DIR__ . '/php/academic_helper.php';

$sessions = pt_all_sessions();
$hostels = pt_all_hostels();
$selSession = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$selHostel = isset($_GET['hostel_id']) ? (int)$_GET['hostel_id'] : 0;

$pageTitle = 'Confirm Payments';
$pageHeader = 'Dashboard';
?>
<?php include 'includes/head.php'; ?>
<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="content-body" id="pt-content">
	<!-- row -->
	<div class="container-fluid">
		<div class="d-flex align-items-center mb-4 flex-wrap">
			<h3 class="me-auto">Payment Lists</h3>
		</div>
		<div class="row mb-3 g-3">
			<div class="col-sm-5 col-xl-3">
				<label class="form-label">Academic Session</label>
				<select id="filterSession" class="default-select form-control">
					<option value="0">All Sessions</option>
					<?php foreach ($sessions as $s): ?>
						<option value="<?php echo (int)$s['id']; ?>" <?php echo $selSession === (int)$s['id'] ? 'selected' : ''; ?>>
							<?php echo htmlspecialchars($s['name']); ?><?php echo $s['is_active'] ? ' (Active)' : ''; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-sm-5 col-xl-3">
				<label class="form-label">Hostel</label>
				<select id="filterHostel" class="default-select form-control">
					<option value="0">All Hostels</option>
					<?php foreach ($hostels as $h): ?>
						<option value="<?php echo (int)$h['id']; ?>" <?php echo $selHostel === (int)$h['id'] ? 'selected' : ''; ?>>
							<?php echo htmlspecialchars($h['name']); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ($selSession || $selHostel): ?>
				<div class="col-sm-2 col-xl-3 d-flex align-items-end">
					<a href="confirm-payments.php" class="btn btn-secondary light btn-sm">Clear Filters</a>
				</div>
			<?php endif; ?>
		</div>
		<div class="row">
			<div class="col-xl-12">
				<div class="table-responsive">
					<?php
					include 'php/fetch_payment.php';
					if (!empty($userPayments)):
						?>
						<table class="table display mb-4 dataTablesCard job-table table-responsive-xl card-table"
							id="example5">
							<thead>
								<tr>
									<th>S/N</th>
									<th>First Name</th>
									<th>Last Name</th>
									<th>Email</th>
									<th>Phone Number</th>
									<th>Payers Name</th>
									<th>Bank Name</th>
									<th>Session</th>
									<th>Hostel</th>
									<th>Assign</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php $serialNumber = 1;
								foreach ($userPayments as $row): ?>
									<tr>
										<td><?= $serialNumber ?></td>
										<td><?= htmlspecialchars($row["firstName"]) ?></td>
										<td><?= htmlspecialchars($row["lastName"]) ?></td>
										<td><?= htmlspecialchars($row["email"]) ?></td>
										<td><?= htmlspecialchars($row["contactNo"]) ?></td>
										<td><?= htmlspecialchars($row["payers_name"]) ?></td>
										<td><?= htmlspecialchars($row["bankName"]) ?></td>
										<td><?= htmlspecialchars($row["session_name"] ?? '—') ?></td>
										<td><?= htmlspecialchars($row["hostel_name"] ?? '—') ?></td>
										<td>
											<button class="btn btn-success view-payment"
												data-userid="<?= $row['id'] ?>">Assign</button>
										</td>
										<td>
											<button class="btn btn-danger">Reject</button>
										</td>
									</tr>
									<?php $serialNumber++; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else: ?>
						<p>No user payments found.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
</div>
<!-- Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="viewModalLabel">Payment Verification</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<!-- Content will be dynamically loaded here via JavaScript -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
				<!-- <button type="button" class="btn btn-primary">Save changes</button> -->
			</div>
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

	// Use event delegation so this works both on initial load and after PTNav AJAX swaps
	$(document).on('change', '#filterSession, #filterHostel', function () {
		var sessionId = $('#filterSession').val();
		var hostelId = $('#filterHostel').val();
		var params = [];
		if (sessionId && sessionId !== '0') { params.push('session_id=' + encodeURIComponent(sessionId)); }
		if (hostelId && hostelId !== '0') { params.push('hostel_id=' + encodeURIComponent(hostelId)); }
		var qs = params.length ? '?' + params.join('&') : '';
		var url = 'confirm-payments.php' + qs;
		if (window.PTNav && PTNav.navigate) { PTNav.navigate(url); } else { window.location.href = url; }
	});

	$(document).on('click', '.view-payment', function () {
			const userId = this.getAttribute('data-userid');
			const viewModalEl = document.getElementById('viewModal');
			if (!viewModalEl) return;
			var viewModal = bootstrap.Modal.getOrCreateInstance(viewModalEl);
			viewModal.show();
			fetch(`php/fetch_user_d.php?id=${userId}`)
				.then(response => response.json())
				.then(data => {
					const modalBody = document.querySelector('#viewModal .modal-body');
					modalBody.innerHTML = `
						<ul class="list-group mt-3">
							<li class="list-group-item"><strong>First Name:</strong> ${esc(data.firstName)}</li>
							<li class="list-group-item"><strong>Last Name:</strong> ${esc(data.lastName)}</li>
							<li class="list-group-item"><strong>Email:</strong> ${esc(data.email)}</li>
							<li class="list-group-item"><strong>Contact Number:</strong> ${esc(data.contactNo)}</li>
							<li class="list-group-item"><strong>Payer's Name:</strong> ${esc(data.payers_name)}</li>
							<li class="list-group-item"><strong>Bank Name:</strong> ${esc(data.bankName)}</li>
							<li class="list-group-item"><strong>Payment Date:</strong> ${esc(data.paymentDate)}</li>
						</ul>
						${data.paymentInfo ? `<div class="mt-3">
							<img src="data:image/jpeg;base64,${esc(data.paymentInfo)}" width="100%" height="auto" class="img-fluid">
						</div>` : '<p class="text-muted mt-3">No payment proof attached.</p>'}
						<form id="reservationForm"> 
							<div class="mb-4">
								<label class="form-label required">Select Room Category</label>
								<select id="roomCategory" class="default-select wide form-control solid">
									<option>Select room category</option>
								</select>
							</div>
							<div class="mb-4">
								<label class="form-label required">Select Room Number</label>
								<select id="roomNumber" class="default-select wide form-control solid">
									<option>Select room number</option>
								</select>
							</div>
							<div class="mb-4">
								<label class="form-label required">Bed Space</label>
								<input type="text" id="bedSpace" class="form-control solid" placeholder="Enter Bed Space">
								<input type="hidden" id="userId" value="${esc(userId)}">
							</div>
							<button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
						</form>
					`;
					fetchRoomCategories();
					attachFormSubmitListener();
				})
				.catch(error => {
					console.error('Error fetching user details:', error);
					const modalBody = document.querySelector('#viewModal .modal-body');
					modalBody.textContent = 'Failed to load payment details.';
				});
	});

	function fetchRoomCategories() {
		fetch('php/fetch_room_categories.php')
			.then(response => response.json())
			.then(categories => {
				const roomCategorySelect = document.getElementById('roomCategory');
				roomCategorySelect.innerHTML = '<option selected>Choose...</option>';
				categories.forEach(category => {
					const option = document.createElement('option');
					option.value = category.id;
					option.textContent = category.room_type;
					roomCategorySelect.appendChild(option);
				});
				roomCategorySelect.addEventListener('change', function () {
					fetchRoomsByCategory(this.value);
				});
			})
			.catch(error => {
				console.error('Error fetching room categories:', error);
			});
	}

	function fetchRoomsByCategory(categoryId) {
		fetch(`php/fetch_rooms.php?category_id=${categoryId}`)
			.then(response => response.json())
			.then(rooms => {
				const roomNumberSelect = document.getElementById('roomNumber');
				roomNumberSelect.innerHTML = '<option selected>Choose...</option>';
				rooms.forEach(room => {
					const option = document.createElement('option');
					option.value = room.id;
					option.textContent = `${room.room_number} - (Available space: ${room.available_space})`;
					roomNumberSelect.appendChild(option);
				});
			})
			.catch(error => {
				console.error('Error fetching rooms:', error);
			});
	}

	function attachFormSubmitListener() {
		const form = document.querySelector('#reservationForm');
		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();

				const userId = document.getElementById('userId').value;
				const roomCategory = document.getElementById('roomCategory').value;
				const roomNumber = document.getElementById('roomNumber').value;
				const bedSpace = document.getElementById('bedSpace').value;

				fetch('assign_room.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({ userId, roomCategory, roomNumber, bedSpace })
				})
					.then(response => response.json())
					.then(data => {
						if (data.status === 'success') {
							if (window.PT) window.PT.success(data.message || 'Room assignment successful!', 'Room Assigned');
							setTimeout(function () {
								if (window.PTNav && PTNav.refresh) { PTNav.refresh(); } else { location.reload(); }
							}, 1200);
						} else {
							if (window.PT) window.PT.error(data.message || 'Room assignment failed', 'Assignment Error');
						}
					})
					.catch(error => {
						console.error('Error assigning room:', error);
					});
			});
		} else {
			console.error('Form not found or not accessible.');
		}
	}

	// Client-side pagination/search for the payments table (DataTables lib is already loaded on this page)
	if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
		var dtEl = document.getElementById('example5');
		if (dtEl && !jQuery.fn.DataTable.isDataTable(dtEl)) {
			jQuery('#example5').DataTable({
				pageLength: 25,
				lengthMenu: [10, 25, 50, 100],
				order: [[0, 'asc']]
			});
		}
	}
</script>

</div>

<?php include 'includes/footer.php'; ?>