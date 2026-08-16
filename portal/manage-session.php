<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + manage_session permission
pt_require_page('manage_session');

include 'php/fetch_admin_info.php';

require_once __DIR__ . '/php/academic_helper.php';
$activeSession = pt_active_session();
$sessions = pt_all_sessions();

$pageTitle = 'Manage Session';
$pageHeader = 'Manage Session';
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

		<?php if ($activeSession): ?>
			<div class="row">
				<div class="col-12">
					<div class="alert alert-success rounded-0">
						<i class="fas fa-calendar-check me-2"></i>
						Active session: <strong><?php echo htmlspecialchars($activeSession['name']); ?></strong>
						<span class="text-muted">(activated
							<?php echo htmlspecialchars($activeSession['activated_at'] ?? 'n/a'); ?>)</span>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="row">
				<div class="col-12">
					<div class="alert alert-warning rounded-0">
						<i class="fas fa-exclamation-triangle me-2"></i>
						No session is currently active. Student bookings and payments are <strong>closed</strong>
						until you activate a session.
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">Create New Session</h4>
					</div>
					<div class="card-body">
						<form id="pt-session-create" class="row g-3">
							<div class="col-sm-6">
								<label class="form-label">Session Name <span
										class="text-danger">*</span></label>
								<input type="text" class="form-control" name="name" placeholder="e.g. 2026/2027"
									required>
							</div>
							<div class="col-sm-6 d-flex align-items-end">
								<button type="submit" class="btn btn-primary btn-sm">Create Session</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">All Sessions</h4>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th>S/N</th>
										<th>Session</th>
										<th>Status</th>
										<th>Activated At</th>
										<th>Created At</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($sessions as $i => $s): ?>
										<tr>
											<td><?php echo $i + 1; ?></td>
											<td><?php echo htmlspecialchars($s['name']); ?></td>
											<td>
												<?php if ($s['is_active']): ?>
													<span class="badge badge-success rounded-pill">Active</span>
												<?php else: ?>
													<span class="badge badge-secondary rounded-pill">Inactive</span>
												<?php endif; ?>
											</td>
											<td><?php echo htmlspecialchars($s['activated_at'] ?? '—'); ?></td>
											<td><?php echo htmlspecialchars($s['created_at'] ?? '—'); ?></td>
											<td>
												<?php if (!$s['is_active']): ?>
													<button type="button"
														class="btn btn-success btn-sm pt-session-activate"
														data-id="<?php echo (int) $s['id']; ?>">Activate</button>
												<?php else: ?>
													<span class="text-muted">—</span>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
</div>

<script>
	function esc(s) {
		return String(s ?? '').replace(/[&<>"']/g, function (c) {
			return { '&': '&', '<': '<', '>': '>', '"': '"', "'": ''' }[c];
		});
	}

	function ptSessionCall(url, data, done) {
		$.post(url, data, function (res) {
			if (res.status === 'success') {
				if (window.toastr) { toastr.success(res.message); }
				if (done) { done(); }
			} else {
				if (window.toastr) { toastr.error(res.message || 'Request failed'); }
			}
		}, 'json').fail(function () {
			if (window.toastr) { toastr.error('Request failed. Please try again.'); }
		});
	}

	// Execute immediately (PTNav runs scripts after AJAX swap)
	$('#pt-session-create').on('submit', function (e) {
		e.preventDefault();
		var name = $(this).find('input[name="name"]').val().trim();
		if (!name) { return; }
		ptSessionCall('php/session_api.php', { action: 'create', name: name }, function () {
			if (window.PTNav) { PTNav.refresh(); } else { location.reload(); }
		});
	});

	$(document).on('click', '.pt-session-activate', function () {
		var id = $(this).data('id');
		var name = $(this).closest('tr').find('td').eq(1).text().trim();
		if (!window.confirm('Activate session "' + name + '"? All other sessions will be closed.')) { return; }
		ptSessionCall('php/session_api.php', { action: 'activate', id: id }, function () {
			if (window.PTNav) { PTNav.refresh(); } else { location.reload(); }
		});
	});
</script>

</div>

<?php include 'includes/footer.php'; ?>