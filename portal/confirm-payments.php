<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + confirm_payment permission
pt_require_page('confirm_payment');

include 'php/fetch_admin_info.php';
require_once __DIR__ . '/php/academic_helper.php';

$sessions = pt_all_sessions();
$hostels = pt_all_hostels();
$activeSession = pt_active_session();
$activeSessionId = $activeSession ? (int)$activeSession['id'] : 0;
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
				<div class="form-control" style="background: #f0f0f0; cursor: default;">
					<?php echo $activeSession ? htmlspecialchars($activeSession['name']) . ' (Active)' : 'No Active Session'; ?>
				</div>
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
											<?php if (isset($row['status']) && strcasecmp($row['status'], 'Assigned') === 0): ?>
												<button class="btn btn-primary" disabled>Assigned</button>
											<?php else: ?>
												<button class="btn btn-success view-payment"
													data-userid="<?= $row['id'] ?>"
													data-paymentid="<?= (int)$row['payment_id'] ?>"
													data-hostelid="<?= (int)$row['hostel_id'] ?>"
													data-sessionid="<?= (int)$row['session_id'] ?>">Assign</button>
											<?php endif; ?>
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
						<div class="alert alert-info d-flex align-items-center">
							<i class="fas fa-inbox me-2"></i>
							<span>No user payments found<?php echo $selHostel ? ' for the selected hostel' : ''; ?>. New payments will appear here once students upload their receipts.</span>
						</div>
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

<script>
	var activeAssignBtn = null;

	function esc(s) {
		return String(s ?? '').replace(/[&<>"']/g, function (c) {
			return { '&': '&', '<': '<', '>': '>', '"': '"', "'": '\'' }[c];
		});
	}

	// Client-side filtering for hostels using DataTables
	$(document).on('change', '#filterHostel', function () {
		var hostelName = $(this).find('option:selected').text().trim();
		var table = $('#example5').DataTable();
		
		if ($(this).val() == '0') {
			table.column(8).search('').draw();
		} else {
			// Exact match for hostel name
			table.column(8).search('^' + hostelName + '$', true, false).draw();
		}
	});

	$(document).on('click', '.view-payment', function () {
			activeAssignBtn = this;
			const userId = this.getAttribute('data-userid');
			const paymentId = this.getAttribute('data-paymentid') || '';
			const hostelId = this.getAttribute('data-hostelid');
			const sessionId = this.getAttribute('data-sessionid');
			const viewModalEl = document.getElementById('viewModal');
			if (!viewModalEl) return;
			const modalBody = document.querySelector('#viewModal .modal-body');
			var viewModal = bootstrap.Modal.getOrCreateInstance(viewModalEl);
			viewModal.show();
			modalBody.innerHTML = '';
			fetch(`php/fetch_user_d.php?id=${encodeURIComponent(userId)}&pid=${encodeURIComponent(paymentId)}`)
				.then(response => response.json())
				.then(data => {
					if (data.error) {
						modalBody.innerHTML = `<div class="alert alert-danger mb-0">${esc(data.error)}</div>`;
						return;
					}
					if (!data.paymentDate) {
						modalBody.innerHTML = `<div class="alert alert-warning mb-0">No payment record found for this student.</div>`;
						return;
					}
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
						${data.has_proof ? `<div class="mt-3">
							<img src="php/payment_proof.php?pid=${encodeURIComponent(data.payment_id || paymentId)}" width="100%" height="auto" class="img-fluid" alt="Payment receipt">
						</div>` : '<p class="text-muted mt-3">No payment proof attached.</p>'}
						<form id="reservationForm" data-pt-no-overlay="1"> 
							<div class="mb-4">
								<label class="form-label required">Bed Space</label>
								<select id="bedSpace" class="default-select wide form-control solid">
									<option value="">Select bed space</option>
								</select>
								<input type="hidden" id="userId" value="${esc(userId)}">
							</div>
							<button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
						</form>
					`;
					fetchAvailableBunks(hostelId, sessionId);
					attachFormSubmitListener();
				})
				.catch(error => {
					console.error('Error fetching user details:', error);
					modalBody.innerHTML = '<div class="alert alert-danger mb-0">Failed to load payment details.</div>';
				});
	});

	function fetchAvailableBunks(hostelId, sessionId) {
		fetch(`php/fetch_available_bunks.php?hostel_id=${hostelId}&session_id=${sessionId}`)
			.then(response => response.json())
			.then(bunks => {
				const bedSpaceSelect = document.getElementById('bedSpace');
				bedSpaceSelect.innerHTML = '<option value="">Choose...</option>';
				bunks.forEach(bunk => {
					const option = document.createElement('option');
					option.value = bunk.id;
					option.textContent = bunk.room_bunk;
					bedSpaceSelect.appendChild(option);
				});
			})
			.catch(error => {
				console.error('Error fetching available bunks:', error);
			});
	}

	function attachFormSubmitListener() {
		const form = document.querySelector('#reservationForm');
		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();

				const userId = document.getElementById('userId').value;
				const bedSpace = document.getElementById('bedSpace').value;
				const submitBtn = document.getElementById('submit-button');

				function showError(msg, title) {
					if (window.PT && window.PT.error) {
						window.PT.error(msg, title);
					} else if (window.toastr) {
						toastr.error(msg, title);
					} else {
						alert(title ? title + ": " + msg : msg);
					}
				}

				function showSuccess(msg, title) {
					if (window.PT && window.PT.success) {
						window.PT.success(msg, title);
					} else if (window.toastr) {
						toastr.success(msg, title);
					} else {
						alert(title ? title + ": " + msg : msg);
					}
				}

				if (!bedSpace) {
					showError('Please select a bed space', 'Validation Error');
					return;
				}

				const originalBtnText = submitBtn.innerHTML;
				submitBtn.disabled = true;

				fetch('assign_room.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({ userId, bedSpace })
				})
					.then(response => response.json())
					.then(data => {
						if (data.status === 'success') {
							showSuccess(data.message || 'Room assignment successful!', 'Room Assigned');
							
							const modalInstance = bootstrap.Modal.getInstance(document.getElementById('viewModal'));
							if (modalInstance) {
								modalInstance.hide();
							}

							if (activeAssignBtn) {
								activeAssignBtn.className = 'btn btn-primary';
								activeAssignBtn.textContent = 'Assigned';
								activeAssignBtn.disabled = true;
								activeAssignBtn = null;
							}

							// Revert button so it's ready if opened again
							submitBtn.innerHTML = originalBtnText;
							submitBtn.disabled = false;
						} else {
							submitBtn.innerHTML = originalBtnText;
							submitBtn.disabled = false;
							showError(data.message || 'Room assignment failed', 'Assignment Error');
						}
					})
					.catch(error => {
						console.error('Error assigning room:', error);
						submitBtn.innerHTML = originalBtnText;
						submitBtn.disabled = false;
						showError('A network error occurred or server returned invalid JSON. Please try again.', 'Error');
					});
			});
		} else {
			console.error('Form not found or not accessible.');
		}
	}

	// Client-side pagination/search for the payments table.
	// DataTables may be missing from the current jQuery instance after PTNav
	// swaps (the shell's global.min.js bundles its own jQuery), so load it on
	// demand and always destroy a stale instance before re-initialising.
	function initPaymentsTable() {
		if (!window.jQuery || !jQuery.fn) return;
		var doInit = function () {
			var $t = jQuery('#example5');
			if (!$t.length) return;
			if (jQuery.fn.DataTable.isDataTable($t)) { $t.DataTable().destroy(); }
			jQuery('#example5').DataTable({
				pageLength: 25,
				lengthMenu: [10, 25, 50, 100],
				order: [[0, 'asc']],
				language: {
					search: '',
					searchPlaceholder: 'Search payments...',
					lengthMenu: '_MENU_',
					paginate: {
						next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
						previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
					}
				}
			});
		};
		if (!jQuery.fn.DataTable) {
			var s = document.createElement('script');
			s.src = 'vendor/datatables/js/jquery.dataTables.min.js';
			s.onload = doInit;
			s.onerror = function () { /* plain table still shows */ };
			document.body.appendChild(s);
			return;
		}
		doInit();
	}

	if (window.jQuery) { initPaymentsTable(); }
</script>

</div>

<?php include 'includes/footer.php'; ?>