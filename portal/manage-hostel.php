<?php
require_once __DIR__ . '/php/rbac.php';

pt_require_page('manage_hostel');

include 'php/fetch_admin_info.php';
require_once __DIR__ . '/php/academic_helper.php';

$hostels = pt_all_hostels();
$activeSession = pt_active_session();
$sessionId = pt_active_session_id();

// All categories (server-side preload for the Add Room dropdown, so it works
// immediately on page load without an extra async fetch).
$allCategories = array();
if (isset($conn)) {
    $catRes = $conn->query("SELECT c.id, c.hostel_id, h.name AS hostel_name, c.room_type, c.rate
                            FROM room_category c
                            LEFT JOIN hostel h ON h.id = c.hostel_id
                            ORDER BY c.hostel_id ASC, c.id ASC");
    if ($catRes) {
        while ($catRow = $catRes->fetch_assoc()) {
            $allCategories[] = $catRow;
        }
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

// Per-hostel stats for the active session (computed server-side)
$hostelStats = array();
foreach ($hostels as $h) {
    $hid = (int)$h['id'];
    $rooms = 0;
    $beds = 0;
    $categories = 0;
    $assigned = 0;

    if (isset($conn)) {
        $r = $conn->query("SELECT COUNT(*) c, COALESCE(SUM(full_capacity),0) b FROM room WHERE hostel_id = $hid");
        if ($r) {
            $row = $r->fetch_assoc();
            $rooms = (int)$row['c'];
            $beds = (int)$row['b'];
        }
        $r = $conn->query("SELECT COUNT(*) c FROM room_category WHERE hostel_id = $hid");
        if ($r) { $categories = (int)$r->fetch_assoc()['c']; }

        if ($sessionId > 0) {
            $st = $conn->prepare("SELECT COUNT(*) c FROM assign_room WHERE hostel_id = ? AND session_id = ? AND matric_no IS NOT NULL");
            if ($st) {
                $st->bind_param('ii', $hid, $sessionId);
                $st->execute();
                $assigned = (int)$st->get_result()->fetch_assoc()['c'];
                $st->close();
            }
        }
    }

    $hostelStats[$hid] = array(
        'name' => $h['name'],
        'address' => isset($h['address']) ? $h['address'] : '',
        'status' => isset($h['status']) ? $h['status'] : 'active',
        'rooms' => $rooms,
        'beds' => $beds,
        'categories' => $categories,
        'assigned' => $assigned,
        'available' => max(0, $beds - $assigned),
    );
}

$pageTitle = 'Manage Hostel';
$pageHeader = 'Manage Hostel';
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
					<div class="alert alert-success rounded-0">
						<i class="fas fa-calendar-check me-2"></i>
						Active session: <strong><?php echo htmlspecialchars($activeSession['name']); ?></strong>
						<?php if (pt_can('manage_session')): ?>
							<a href="manage-session.php" class="alert-link float-sm-end">Manage Session</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="row">
				<div class="col-12">
					<div class="alert alert-warning rounded-0">
						<i class="fas fa-exclamation-triangle me-2"></i>
						No session is currently active. Assignments below reflect the last known data.
						<?php if (pt_can('manage_session')): ?>
							<a href="manage-session.php" class="alert-link">Manage Session</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<ul class="nav nav-pills" id="hostelTabs" role="tablist">
							<li class="nav-item" role="presentation">
								<a class="nav-link active" id="hostel-all-tab" data-bs-toggle="pill" data-bs-target="#pane-all" role="tab" aria-controls="pane-all" aria-selected="true">All Hostels</a>
							</li>
							<?php foreach ($hostels as $h): ?>
								<?php $color = pt_hostel_color($hostelColors, (int)$h['id']); ?>
								<li class="nav-item" role="presentation">
									<a class="nav-link pt-hostel-tab" id="hostel-tab-<?php echo (int)$h['id']; ?>" data-bs-toggle="pill" data-bs-target="#pane-<?php echo (int)$h['id']; ?>" role="tab" aria-controls="pane-<?php echo (int)$h['id']; ?>" aria-selected="false" style="border:1px solid <?php echo $color; ?>;color:<?php echo $color; ?>;">
										<i class="fas fa-building me-1"></i><?php echo htmlspecialchars($h['name']); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>

						<div class="tab-content pt-3">
							<div class="tab-pane fade show active" id="pane-all" role="tabpanel">
								<div class="row">
									<?php foreach ($hostels as $h): ?>
										<?php $sid = (int)$h['id']; $s = $hostelStats[$sid]; $color = pt_hostel_color($hostelColors, $sid); ?>
										<div class="col-xl-6 col-lg-6 col-md-12 mb-3">
											<div class="card pt-hostel-card h-100" style="border-top:4px solid <?php echo $color; ?>;">
												<div class="card-body">
													<div class="d-flex justify-content-between align-items-start">
														<div>
															<span class="badge <?php echo ($s['status'] === 'active') ? 'badge-success' : 'badge-secondary'; ?> rounded-pill"><?php echo htmlspecialchars(ucfirst($s['status'])); ?></span>
															<h4 class="mt-2 mb-0"><?php echo htmlspecialchars($s['name']); ?></h4>
															<small class="text-muted"><?php echo htmlspecialchars($s['address']); ?></small>
														</div>
														<div class="pt-hostel-avatar" style="background:<?php echo $color; ?>;"><?php echo htmlspecialchars(strtoupper(substr($s['name'], 0, 1))); ?></div>
													</div>
													<hr>
													<div class="row text-center">
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['rooms']; ?></h3>
															<span class="text-muted">Rooms</span>
														</div>
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['beds']; ?></h3>
															<span class="text-muted">Beds</span>
														</div>
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['assigned']; ?></h3>
															<span class="text-muted">Assigned</span>
														</div>
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['available']; ?></h3>
															<span class="text-muted">Available</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
							<?php foreach ($hostels as $h): ?>
								<?php $sid = (int)$h['id']; $s = $hostelStats[$sid]; $color = pt_hostel_color($hostelColors, $sid); ?>
								<div class="tab-pane fade" id="pane-<?php echo $sid; ?>" role="tabpanel">
									<div class="row">
										<div class="col-xl-6 col-lg-6 col-md-12 mb-3">
											<div class="card pt-hostel-card h-100" style="border-top:4px solid <?php echo $color; ?>;">
												<div class="card-body">
													<div class="d-flex justify-content-between align-items-start">
														<div>
															<span class="badge <?php echo ($s['status'] === 'active') ? 'badge-success' : 'badge-secondary'; ?> rounded-pill"><?php echo htmlspecialchars(ucfirst($s['status'])); ?></span>
															<h4 class="mt-2 mb-0"><?php echo htmlspecialchars($s['name']); ?></h4>
															<small class="text-muted"><?php echo htmlspecialchars($s['address']); ?></small>
														</div>
														<div class="pt-hostel-avatar" style="background:<?php echo $color; ?>;"><?php echo htmlspecialchars(strtoupper(substr($s['name'], 0, 1))); ?></div>
													</div>
													<hr>
													<div class="row text-center">
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['rooms']; ?></h3>
															<span class="text-muted">Rooms</span>
														</div>
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['beds']; ?></h3>
															<span class="text-muted">Beds</span>
														</div>
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['assigned']; ?></h3>
															<span class="text-muted">Assigned</span>
														</div>
														<div class="col-3">
															<h3 class="mb-0"><?php echo $s['available']; ?></h3>
															<span class="text-muted">Available</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-xl-6 col-lg-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">Add Room</h4>
					</div>
					<div class="card-body">
						<div class="basic-form">
							<form id="addRoomForm" data-ajax="true">
								<div class="row">
									<div class="mb-3 col-md-6">
										<label class="form-label">Hostel</label>
										<select id="addRoomHostel" class="default-select form-control wide">
											<option value="0" selected>All Hostels / Select Hostel</option>
											<?php foreach ($hostels as $h): ?>
												<option value="<?php echo (int)$h['id']; ?>"><?php echo htmlspecialchars($h['name']); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="mb-3 col-md-6">
										<label class="form-label">Room Number</label>
										<input type="text" class="form-control" id="roomNumber" placeholder="Room Number">
									</div>
									<div class="mb-3 col-md-6">
										<label class="form-label">Room Type</label>
										<select id="roomType" class="default-select form-control wide">
											<option selected value="0">Choose...</option>
										</select>
									</div>
									<div class="mb-3 col-md-6">
										<label class="form-label">Capacity</label>
										<input type="number" class="form-control" id="capacity" placeholder="Capacity">
									</div>
								</div>
								<button type="submit" class="btn btn-primary">Add Room</button>
							</form>
						</div>
						<hr>
					</div>
				</div>
			</div>
			<div class="col-xl-6 col-lg-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">Add Room Category</h4>
					</div>
					<div class="card-body">
						<div class="basic-form">
							<form id="addCategoryForm" data-ajax="true">
								<div class="row">
									<div class="mb-3 col-md-6">
										<label class="form-label">Hostel</label>
										<select id="addCategoryHostel" class="default-select form-control wide">
											<option value="0" selected>All Hostels / Select Hostel</option>
											<?php foreach ($hostels as $h): ?>
												<option value="<?php echo (int)$h['id']; ?>"><?php echo htmlspecialchars($h['name']); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="mb-3 col-md-6">
										<label class="form-label">Name</label>
										<input type="text" class="form-control" id="categoryName" placeholder="Name" required>
									</div>
									<div class="mb-3 col-md-6">
										<label class="form-label">Rate</label>
										<input type="text" class="form-control" id="categoryRate" placeholder="300000" required>
									</div>
								</div>
								<button type="submit" class="btn btn-primary">Add Room Category</button>
							</form>
						</div>
					</div>
					<hr>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-xl-7 col-lg-12">
				<div class="card" id="view_room">
					<div class="card-header">
						<h4 class="card-title"><i class="fas fa-door-open me-2 text-primary"></i>View Rooms</h4>
					</div>
					<div class="card-body">
						<div class="table-responsive pt-table-wrap">
							<table class="table display mb-4 dataTablesCard job-table card-table" id="roomTable">
								<thead>
									<tr>
										<th class="text-center">S/N</th>
										<th>Room No.</th>
										<th>Category</th>
										<th class="text-center">Capacity</th>
										<th class="text-center">Available</th>
										<th class="pt-col-hostel">Hostel</th>
										<th class="text-center">Actions</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-5 col-lg-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title"><i class="fas fa-tags me-2 text-primary"></i>Room Categories</h4>
					</div>
					<div class="card-body">
						<div class="table-responsive pt-table-wrap">
							<table class="table display mb-4 dataTablesCard job-table card-table" id="roomDetailsTable">
								<thead>
									<tr>
										<th class="text-center">S/N</th>
										<th>Category</th>
										<th>Rate</th>
										<th class="pt-col-hostel">Hostel</th>
										<th class="text-center">Actions</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Confirm Delete Modal -->
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
	var currentHostelId = 0;
	var ptConfirmAction = null;
	var ptRoomCategories = <?php echo json_encode($allCategories); ?>;

	function esc(s) {
		return String(s ?? '').replace(/[&<>"']/g, function (c) {
			return { '&': '&', '<': '<', '>': '>', '"': '"', "'": '\'' }[c];
		});
	}

	function hostelIdFromTarget(target) {
		if (!target) return 0;
		var m = String(target).replace(/^#/, '').match(/^pane-(\d+)$/);
		return m ? parseInt(m[1], 10) : 0;
	}

	function getHostelUrl(baseUrl) {
		return baseUrl + '?hostel_id=' + currentHostelId;
	}

	function toggleHostelColumn() {
		$('.pt-col-hostel').toggle(currentHostelId === 0);
	}

	function populateRoomTypeDropdown() {
		var hostel = parseInt($('#addRoomHostel').val() || 0, 10) || 0;
		var select = $('#roomType');
		select.empty();
		select.append('<option selected value="0">Choose...</option>');
		(ptRoomCategories || []).forEach(function (category) {
			if (hostel > 0 && parseInt(category.hostel_id, 10) !== hostel) return;
			var label = category.room_type + (hostel === 0 ? ' (' + (category.hostel_name || 'Hostel ' + category.hostel_id) + ')' : '');
			select.append($('<option></option>').val(category.id).text(label));
		});
		if (jQuery.fn.selectpicker) {
			select.selectpicker('refresh');
		}
	}

	function displayRooms(categories, rooms) {
		var tableBody = $('#roomTable tbody');
		tableBody.empty();

		var categoriesMap = {};
		categories.forEach(function (category) {
			categoriesMap[category.id] = category.room_type;
		});

		if (!rooms.length) {
			return;
		}

		rooms.forEach(function (room, index) {
			var categoryOptions = Object.entries(categoriesMap).map(function (entry) {
				var id = entry[0];
				var type = entry[1];
				return '<option value="' + esc(id) + '" ' + (room.category_id == id ? 'selected' : '') + '>' + esc(type) + '</option>';
			}).join('');
			var hostelCell = (currentHostelId === 0)
				? '<td class="pt-col-hostel">' + esc(room.hostel_name || '—') + '</td>'
				: '<td class="pt-col-hostel" style="display:none;"></td>';
			var row = `
				<tr data-room-id="${esc(room.id)}" data-hostel-id="${esc(room.hostel_id)}">
					<td class="text-center">${index + 1}</td>
					<td contenteditable="true" class="editable">${esc(room.room_number)}</td>
					<td>
						<select class="form-control form-control-sm editable">
							${categoryOptions}
						</select>
					</td>
					<td contenteditable="true" class="editable text-center">${esc(room.full_capacity)}</td>
					<td contenteditable="true" class="editable text-center">${esc(room.available_space)}</td>
					${hostelCell}
					<td class="text-center text-nowrap">
						<button class="btn btn-primary btn-sm px-2 me-1 edit-button" title="Edit"><i class="fas fa-pencil-alt"></i></button>
						<button class="btn btn-success btn-sm px-2 me-1 update-button" style="display:none;" title="Save"><i class="fas fa-check"></i></button>
						<button class="btn btn-danger btn-sm px-2 delete-button" title="Delete"><i class="fas fa-trash-alt"></i></button>
					</td>
				</tr>
			`;
			tableBody.append(row);
		});
	}

	function displayCategories(categories) {
		var tableBody = $('#roomDetailsTable tbody');
		tableBody.empty();

		if (!categories.length) {
			return;
		}

		categories.forEach(function (category, index) {
			var hostelCell = (currentHostelId === 0)
				? '<td class="pt-col-hostel">' + esc(category.hostel_name || '—') + '</td>'
				: '<td class="pt-col-hostel" style="display:none;"></td>';
			var row = `
				<tr data-category-id="${esc(category.id)}" data-hostel-id="${esc(category.hostel_id)}">
					<td class="text-center">${index + 1}</td>
					<td contenteditable="true" class="editable">${esc(category.room_type)}</td>
					<td contenteditable="true" class="editable">${esc(category.rate)}</td>
					${hostelCell}
					<td class="text-center text-nowrap">
						<button class="btn btn-primary btn-sm px-2 me-1 edit-button" title="Edit"><i class="fas fa-pencil-alt"></i></button>
						<button class="btn btn-success btn-sm px-2 me-1 update-button" style="display:none;" title="Save"><i class="fas fa-check"></i></button>
						<button class="btn btn-danger btn-sm px-2 delete-button" title="Delete"><i class="fas fa-trash-alt"></i></button>
					</td>
				</tr>
			`;
			tableBody.append(row);
		});
	}

	function destroyTables() {
		['#roomTable', '#roomDetailsTable'].forEach(function (sel) {
			var $t = $(sel);
			if (!$t.length) return;
			if (jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable($t)) {
				$t.DataTable().destroy();
			}
		});
	}

	function initTables() {
		[
			{ sel: '#roomTable', zeroRecords: 'No rooms found for this hostel yet.' },
			{ sel: '#roomDetailsTable', zeroRecords: 'No room categories found for this hostel yet.' }
		].forEach(function (spec) {
			var $t = $(spec.sel);
			if (!$t.length) return;
			$t.DataTable({
				pageLength: 10,
				lengthMenu: [5, 10, 25, 50],
				searching: true,
				lengthChange: true,
				info: true,
				columnDefs: [
					{ orderable: false, targets: -1 }
				],
				language: {
					search: '',
					searchPlaceholder: 'Search...',
					lengthMenu: '_MENU_',
					zeroRecords: spec.zeroRecords
				}
			});
		});
	}

	function initDataTables() {
		if (!window.jQuery || !jQuery.fn) return;
		if (!jQuery.fn.DataTable) {
			// The shell reloads jQuery (global.min.js bundles 3.7.x) after the page's own
			// scripts run, so a static <script> tag would bind to the wrong instance.
			var s = document.createElement('script');
			s.src = 'vendor/datatables/js/jquery.dataTables.min.js';
			s.onload = initTables;
			s.onerror = function () { /* DataTables unavailable; plain table still shows */ };
			document.body.appendChild(s);
			return;
		}
		initTables();
	}

	function loadAllData() {
		toggleHostelColumn();

		var roomWrap = $('#roomTable').closest('.pt-table-wrap')[0];
		var catWrap = $('#roomDetailsTable').closest('.pt-table-wrap')[0];

		// Drop any live DataTables first so the skeleton rows render cleanly
		destroyTables();
		if (window.PT) {
			PT.tableLoading(roomWrap, true);
			PT.tableLoading(catWrap, true);
		}

		$.when(
			$.getJSON(getHostelUrl('php/room_category.php')),
			$.getJSON(getHostelUrl('php/room.php'))
		).done(function (categoriesResponse, roomsResponse) {
			var categories = categoriesResponse[0];
			var rooms = roomsResponse[0];
			if (window.PT) {
				PT.tableLoading(roomWrap, false);
				PT.tableLoading(catWrap, false);
			}
			displayRooms(categories, rooms);
			displayCategories(categories);
			populateRoomTypeDropdown();
			initDataTables();
		}).fail(function () {
			if (window.PT) {
				PT.tableLoading(roomWrap, false);
				PT.tableLoading(catWrap, false);
				PT.error('Could not load hostel data.');
			}
		});
	}

	// Tab switching: All Hostels or a specific hostel
	function ptSetupManageHostel() {
		$('#hostelTabs a').on('shown.bs.tab', function (e) {
			currentHostelId = hostelIdFromTarget($(e.target).attr('data-bs-target'));
			if (currentHostelId > 0) {
				$('#addRoomHostel').val(String(currentHostelId));
				$('#addCategoryHostel').val(String(currentHostelId));
				if (jQuery.fn.selectpicker) {
					$('#addRoomHostel, #addCategoryHostel').selectpicker('refresh');
				}
			}
			populateRoomTypeDropdown();
			loadAllData();
		});

		// Room type dropdown follows the selected hostel in the Add Room form
		$('#addRoomHostel').on('change', function () {
			populateRoomTypeDropdown();
		});

		$('#addRoomForm').on('submit', submitAddRoom);
		$('#addCategoryForm').on('submit', submitAddCategory);

		// Row edit/update/delete handlers (delegation scoped to the swapped-in tables)
		$('#roomTable').on('click', '.edit-button', editRoomRow);
		$('#roomTable').on('click', '.update-button', updateRoomRow);
		$('#roomTable').on('click', '.delete-button', deleteRoomRow);
		$('#roomDetailsTable').on('click', '.edit-button', editCategoryRow);
		$('#roomDetailsTable').on('click', '.update-button', updateCategoryRow);
		$('#roomDetailsTable').on('click', '.delete-button', deleteCategoryRow);

		// Confirm modal delete button
		$('#ptConfirmDeleteBtn').on('click', function () {
			var action = ptConfirmAction;
			ptConfirmAction = null;
			bootstrap.Modal.getOrCreateInstance(document.getElementById('ptConfirmModal')).hide();
			if (action) { action(); }
		});

		loadAllData();
	}

	function editRoomRow() {
		var row = $(this).closest('tr');
		row.find('.editable').attr('contenteditable', 'true');
		row.find('.edit-button').hide();
		row.find('.update-button').show();
	}

	function updateRoomRow() {
		var row = $(this).closest('tr');
		var roomId = row.data('room-id');
		var roomHostelId = row.data('hostel-id') || currentHostelId;
		var roomNumber = row.find('td').eq(1).text();
		var roomCategory = row.find('select').val();
		var fullCapacity = row.find('td').eq(3).text();
		var availableSpace = row.find('td').eq(4).text();

		if (!roomNumber.trim() || !roomCategory || parseInt(fullCapacity, 10) < 1) {
			if (window.PT) { PT.error('Room number, category and a valid capacity are required.'); }
			return;
		}

		$.ajax({
			url: 'php/update_room.php',
			method: 'POST',
			dataType: 'json',
			data: {
				id: roomId,
				hostel_id: roomHostelId,
				room_number: roomNumber,
				category_id: roomCategory,
				full_capacity: fullCapacity,
				available_space: availableSpace
			},
			success: function (response) {
				if (response.status === 'success') {
					row.find('.editable').attr('contenteditable', 'false');
					row.find('.edit-button').show();
					row.find('.update-button').hide();
					if (window.PT) { PT.success(response.message || 'Room details updated successfully!'); }
				} else {
					if (window.PT) { PT.error('Error updating room details: ' + response.message); }
				}
			},
			error: function () {
				if (window.PT) { PT.error('Could not update room details. Please try again.'); }
			}
		});
	}

	function editCategoryRow() {
		var row = $(this).closest('tr');
		row.find('.editable').attr('contenteditable', 'true');
		row.find('.edit-button').hide();
		row.find('.update-button').show();
	}

	function updateCategoryRow() {
		var row = $(this).closest('tr');
		var categoryId = row.data('category-id');
		var categoryHostelId = row.data('hostel-id') || currentHostelId;
		var roomType = row.find('td').eq(1).text();
		var rate = row.find('td').eq(2).text();

		if (!roomType.trim() || !rate.trim() || isNaN(parseFloat(rate))) {
			if (window.PT) { PT.error('Category name and a valid rate are required.'); }
			return;
		}

		$.ajax({
			url: 'php/update_room_category.php',
			method: 'POST',
			dataType: 'json',
			data: {
				id: categoryId,
				hostel_id: categoryHostelId,
				room_type: roomType,
				rate: rate
			},
			success: function (response) {
				if (response.status === 'success') {
					row.find('.editable').attr('contenteditable', 'false');
					row.find('.edit-button').show();
					row.find('.update-button').hide();
					if (window.PT) { PT.success(response.message || 'Category details updated successfully!'); }
				} else {
					if (window.PT) { PT.error('Error updating category details: ' + response.message); }
				}
			},
			error: function () {
				if (window.PT) { PT.error('Could not update category details. Please try again.'); }
			}
		});
	}

	function openConfirmModal(title, message, onConfirm) {
		$('#ptConfirmTitle').text(title);
		$('#ptConfirmMessage').text(message);
		ptConfirmAction = onConfirm;
		bootstrap.Modal.getOrCreateInstance(document.getElementById('ptConfirmModal')).show();
	}

	function deleteRoomRow() {
		var row = $(this).closest('tr');
		var roomId = row.data('room-id');
		var roomHostelId = row.data('hostel-id') || currentHostelId;
		var roomNumber = row.find('td').eq(1).text().trim();

		openConfirmModal(
			'Delete Room?',
			'Are you sure you want to delete room "' + (roomNumber || roomId) + '"? This action cannot be undone.',
			function () {
				$.ajax({
					url: 'php/delete_room.php',
					method: 'POST',
					dataType: 'json',
					data: { id: roomId, hostel_id: roomHostelId },
					success: function (response) {
						if (response.status === 'success') {
							if (window.PT) { PT.success(response.message || 'Room deleted successfully!'); }
							loadAllData();
						} else {
							if (window.PT) { PT.error(response.message || 'Failed to delete room'); }
						}
					},
					error: function () {
						if (window.PT) { PT.error('Could not delete room. Please try again.'); }
					}
				});
			}
		);
	}

	function deleteCategoryRow() {
		var row = $(this).closest('tr');
		var categoryId = row.data('category-id');
		var categoryHostelId = row.data('hostel-id') || currentHostelId;
		var categoryName = row.find('td').eq(1).text().trim();

		openConfirmModal(
			'Delete Category?',
			'Are you sure you want to delete category "' + (categoryName || categoryId) + '"? This action cannot be undone.',
			function () {
				$.ajax({
					url: 'php/delete_category.php',
					method: 'POST',
					dataType: 'json',
					data: { id: categoryId, hostel_id: categoryHostelId },
					success: function (response) {
						if (response.status === 'success') {
							if (window.PT) { PT.success(response.message || 'Room category deleted successfully!'); }
							loadAllData();
						} else {
							if (window.PT) { PT.error(response.message || 'Failed to delete room category'); }
						}
					},
					error: function () {
						if (window.PT) { PT.error('Could not delete room category. Please try again.'); }
					}
				});
			}
		);
	}

	// ---- Add Room ----
	function submitAddRoom(event) {
		event.preventDefault();

		var roomNumber = $('#roomNumber').val();
		var roomType = $('#roomType').val();
		var capacity = $('#capacity').val();
		var hostelId = $('#addRoomHostel').val();

		if (!hostelId || hostelId === '0') {
			if (window.PT) { PT.error('Please select a hostel first.'); }
			return;
		}
		if (!roomNumber.trim()) {
			if (window.PT) { PT.error('Room number is required.'); }
			return;
		}
		if (!roomType || roomType === '0') {
			if (window.PT) { PT.error('Please choose a room category.'); }
			return;
		}
		if (!capacity || parseInt(capacity, 10) < 1) {
			if (window.PT) { PT.error('Capacity must be at least 1.'); }
			return;
		}

		$.ajax({
			url: 'php/add_room.php',
			method: 'POST',
			dataType: 'json',
			data: {
				hostel_id: hostelId,
				roomNumber: roomNumber,
				roomType: roomType,
				capacity: capacity
			},
			success: function (response) {
				if (response.status === 'success') {
					if (window.PT) { PT.success(response.message || 'Room added successfully!'); }
				$('#addRoomForm')[0].reset();
				$('#addRoomHostel').val(String(hostelId));
				if (jQuery.fn.selectpicker) {
					$('#addRoomHostel').selectpicker('refresh');
				}
				populateRoomTypeDropdown();
				loadAllData();
				} else {
					if (window.PT) { PT.error(response.message || 'Failed to add room'); }
				}
			},
			error: function () {
				if (window.PT) { PT.error('Could not add room. Please try again.'); }
			}
		});
	}

	// ---- Add Room Category ----
	function submitAddCategory(event) {
		event.preventDefault();

		var categoryName = $('#categoryName').val();
		var categoryRate = $('#categoryRate').val();
		var hostelId = $('#addCategoryHostel').val();

		if (!hostelId || hostelId === '0') {
			if (window.PT) { PT.error('Please select a hostel first.'); }
			return;
		}
		if (!categoryName.trim()) {
			if (window.PT) { PT.error('Category name is required.'); }
			return;
		}
		if (!categoryRate.trim() || isNaN(parseFloat(categoryRate))) {
			if (window.PT) { PT.error('A valid rate is required.'); }
			return;
		}

		$.ajax({
			url: 'php/add_category.php',
			method: 'POST',
			dataType: 'json',
			data: {
				hostel_id: hostelId,
				categoryName: categoryName,
				categoryRate: categoryRate
			},
			success: function (response) {
				if (response.status === 'success') {
					if (window.PT) { PT.success(response.message || 'Room category added successfully!'); }
					$('#addCategoryForm')[0].reset();
					$('#addCategoryHostel').val(String(hostelId));
					if (jQuery.fn.selectpicker) {
						$('#addCategoryHostel').selectpicker('refresh');
					}
					loadAllData();
				} else {
					if (window.PT) { PT.error(response.message || 'Failed to add room category'); }
				}
			},
			error: function () {
				if (window.PT) { PT.error('Could not add room category. Please try again.'); }
			}
		});
	}

	// Initial load: runs on direct page load and after AJAX navigation swaps content in.
	// DOMContentLoaded covers direct loads (jQuery is present by then); pt:content-loaded
	// covers AJAX swaps via pt-nav.js. Each execution binds to fresh swapped-in elements.
	document.addEventListener('DOMContentLoaded', ptSetupManageHostel);
	document.addEventListener('pt:content-loaded', ptSetupManageHostel);
</script>

</div>
<!--**********************************
	Content body end
***********************************-->

<?php include 'includes/footer.php'; ?>
