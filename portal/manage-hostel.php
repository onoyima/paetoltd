<?php
require_once __DIR__ . '/php/rbac.php';

pt_require_page('manage_hostel');

include 'php/fetch_admin_info.php';
require_once __DIR__ . '/php/academic_helper.php';
$hostels = pt_all_hostels();

$pageTitle = 'Manage Hostel';
$pageHeader = 'Dashboard';
?>
<?php include 'includes/head.php'; ?>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!--**************************************
    Content body start
***********************************-->
<div class="content-body" id="pt-content">
	<div class="container-fluid">
		<div class="row page-titles">
			<div class="col-md-6">
				<h4>Hostel</h4>
				<ul class="nav nav-pills flex-column flex-sm-start mb-3" id="hostelTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link active" id="hostel-all-tab" data-bs-toggle="tab" data-bs-target="#hostel-all" role="tab" aria-controls="hostel-all" aria-selected="true">All Hostels</a>
					</li>
					<?php foreach ($hostels as $h): ?>
						<li class="nav-item" role="presentation">
							<a class="nav-link" id="hostel-<?php echo (int)$h['id']; ?>-tab" data-bs-toggle="tab" data-bs-target="#hostel-<?php echo (int)$h['id']; ?>" role="tab" aria-controls="hostel-<?php echo (int)$h['id']; ?>" aria-selected="false"><?php echo htmlspecialchars($h['name']); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<!-- row -->
		<div class="row">
			
			<div class="col-xl-6 col-lg-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">Add Room</h4>
					</div>
					<div class="card-body">
						<div class="basic-form">
							<form id="addRoomForm">
								<div class="row">
									<div class="mb-3 col-md-6">
										<label class="form-label">Room Number</label>
										<input type="text" class="form-control" id="roomNumber" placeholder="Room Number">
									</div>
									<div class="mb-3 col-md-6">
										<label class="form-label">Room Type</label>
										<select id="roomType" class="default-select form-control wide">
											<option selected>Choose...</option>
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
							<form id="addCategoryForm">
								<div class="row">
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
					<HR>
				</div>
			</div>
			<div class="col-xl-7 col-lg-12">
				<div class="card" id="view_room">
					<div class="card-header">
						<h4 class="card-title">View Room</h4>
					</div>
					<div class="card-body">
						<div class="basic-form">
							<table class="table display mb-4 dataTablesCard  card-table" id="roomTable" >
								<thead>
									<tr>
										<th>S/N</th>
										<th>Room No.</th>
										<th> Category</th>
										<th> Capacity</th>
										<th>Available</th>
										<th>Actions</th>
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
						<h4 class="card-title"> Room Category</h4>
					</div>
					<div class="card-body">
						<div class="basic-form">
							<table class="table display mb-4 dataTablesCard  card-table" id="roomDetailsTable">
								<thead>
									<tr>
										<th>S/N</th>
										<th> Category</th>
										<th> Rate</th>
										<th>Actions</th>
										
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

<script>
		var currentHostelId = 0;
		var currentTab = 'all';

		$('#hostelTabs a').on('click', function() {
			currentTab = $(this).attr('id').replace('hostel-', '').replace('-tab', '');
			currentHostelId = currentTab === 'all' ? 0 : parseInt(currentTab);
			loadAllData();
		});

		function getHostelUrl(baseUrl) {
			return baseUrl + '?hostel_id=' + currentHostelId;
		}

		function fetchRoomCategories() {
			return $.getJSON(getHostelUrl('php/room_category.php'));
		}

		function fetchRooms() {
			return $.getJSON(getHostelUrl('php/room.php'));
		}

		function esc(s) {
			return String(s ?? '').replace(/[&<>"']/g, function(c) {
				return {'&': '&', '<': '<', '>': '>', '"': '"', "'": '''}[c];
			});
		}

		function displayRooms(categories, rooms) {
			var tableBody = $('#roomTable tbody');
			tableBody.empty();
			
			var categoriesMap = {};
			categories.forEach(function(category) {
				categoriesMap[category.id] = category.room_type;
			});

			rooms.forEach(function(room, index) {
				var roomCategory = categoriesMap[room.category_id];
				var categoryOptions = Object.entries(categoriesMap).map(function(entry) {
					var id = entry[0];
					var type = entry[1];
					return '<option value="' + esc(id) + '" ' + (room.category_id == id ? 'selected' : '') + '>' + esc(type) + '</option>';
				}).join('');
				var row = `
					<tr data-room-id="${esc(room.id)}" data-hostel-id="${esc(room.hostel_id)}">
						<td>${index + 1}</td>
						<td contenteditable="true" class="editable">${esc(room.room_number)}</td>
						<td>
							<select class="form-control editable">
								${categoryOptions}
							</select>
						</td>
						<td contenteditable="true" class="editable">${esc(room.full_capacity)}</td>
						<td contenteditable="true" class="editable">${esc(room.available_space)}</td>
						<td>
							<button class="btn btn-primary edit-button">Edit</button>
							<button class="btn btn-success update-button" style="display:none;">Update</button>
						</td>
					</tr>
				`;
				tableBody.append(row);
			});

			tableBody.on('click', '.edit-button', function() {
				var row = $(this).closest('tr');
				row.find('.editable').attr('contenteditable', 'true');
				row.find('.edit-button').hide();
				row.find('.update-button').show();
			});

			tableBody.on('click', '.update-button', function() {
				var row = $(this).closest('tr');
				var roomId = row.data('room-id');
				var roomHostelId = row.data('hostel-id') || currentHostelId;
				var roomNumber = row.find('td').eq(1).text();
				var roomCategory = row.find('select').val();
				var fullCapacity = row.find('td').eq(3).text();
				var availableSpace = row.find('td').eq(4).text();

				$.ajax({
					url: 'php/update_room.php',
					method: 'POST',
					data: {
						id: roomId,
						hostel_id: roomHostelId,
						room_number: roomNumber,
						category_id: roomCategory,
						full_capacity: fullCapacity,
						available_space: availableSpace
					},
					success: function(response) {
						if (response.status === 'success') {
							row.find('.editable').attr('contenteditable', 'false');
							row.find('.edit-button').show();
							row.find('.update-button').hide();
							if (window.PT) window.PT.success('Room details updated successfully!');
						} else {
							if (window.PT) window.PT.error('Error updating room details: ' + response.message);
						}
					},
					error: function(error) {
						console.error('Error updating room details:', error);
					}
				});
			});
		}

		function loadAllData() {
			$.when(fetchRoomCategories(), fetchRooms()).done(function(categoriesResponse, roomsResponse) {
				var categories = categoriesResponse[0];
				var rooms = roomsResponse[0];
				displayRooms(categories, rooms);
				populateRoomTypeDropdown(categories);
			}).fail(function(error) {
				console.error('Error fetching room data:', error);
			});

			loadCategoriesTable();
		}

		function populateRoomTypeDropdown(categories) {
			var roomTypeSelect = $('#roomType');
			var currentVal = roomTypeSelect.val();
			roomTypeSelect.empty();
			roomTypeSelect.append('<option selected>Choose...</option>');
			categories.forEach(function(category) {
				var option = $('<option></option>').val(category.id).text(category.room_type);
				roomTypeSelect.append(option);
			});
			if (currentVal) roomTypeSelect.val(currentVal);
		}

		// Initial load
		loadAllData();
</script>


<script>
		var currentHostelId = 0;
		var currentTab = 'all';

		$('#hostelTabs a').on('click', function() {
			currentTab = $(this).attr('id').replace('hostel-', '').replace('-tab', '');
			currentHostelId = currentTab === 'all' ? 0 : parseInt(currentTab);
			loadCategoriesTable();
		});

		function getHostelUrl(baseUrl) {
			return baseUrl + '?hostel_id=' + currentHostelId;
		}

		function loadCategoriesTable() {
			$.getJSON(getHostelUrl('php/room_category.php'), function(data) {
				var tableBody = $('#roomDetailsTable tbody');
				tableBody.empty();
				
				data.forEach(function(category, index) {
					var row = `
						<tr data-category-id="${esc(category.id)}" data-hostel-id="${esc(category.hostel_id)}">
							<td>${index + 1}</td>
							<td contenteditable="true" class="editable">${esc(category.room_type)}</td>
							<td contenteditable="true" class="editable">${esc(category.rate)}</td>
							<td>
								<button class="btn btn-primary edit-button">Edit</button>
								<button class="btn btn-success update-button" style="display:none;">Update</button>
							</td>
						</td>
					`;
					tableBody.append(row);
				});

				tableBody.on('click', '.edit-button', function() {
					var row = $(this).closest('tr');
					row.find('.editable').attr('contenteditable', 'true');
					row.find('.edit-button').hide();
					row.find('.update-button').show();
				});

				tableBody.on('click', '.update-button', function() {
					var row = $(this).closest('tr');
					var categoryId = row.data('category-id');
					var categoryHostelId = row.data('hostel-id') || currentHostelId;
					var roomType = row.find('td').eq(1).text();
					var rate = row.find('td').eq(2).text();

					$.ajax({
						url: 'php/update_room_category.php',
						method: 'POST',
						data: {
							id: categoryId,
							hostel_id: categoryHostelId,
							room_type: roomType,
							rate: rate
						},
						success: function(response) {
							if (response.status === 'success') {
								row.find('.editable').attr('contenteditable', 'false');
								row.find('.edit-button').show();
								row.find('.update-button').hide();
								if (window.PT) window.PT.success('Category details updated successfully!');
							} else {
								if (window.PT) window.PT.error('Error updating category details: ' + response.message);
							}
						},
						error: function(error) {
							console.error('Error updating category details:', error);
						}
					});
				});
			}).fail(function(error) {
				console.error('Error fetching room categories:', error);
			});
		}

		// Initial load
		loadCategoriesTable();
</script>


<script>
		var currentHostelId = $('#hostelSelector').val();

		$('#hostelSelector').on('change', function() {
			currentHostelId = $(this).val();
			populateRoomTypeDropdown();
		});

		function getHostelUrl(baseUrl) {
			return baseUrl + '?hostel_id=' + currentHostelId;
		}

		// Fetch room categories and populate the dropdown
		function populateRoomTypeDropdown() {
			$.getJSON(getHostelUrl('php/room_category.php'), function(data) {
				var roomTypeSelect = $('#roomType');
				roomTypeSelect.empty();
				roomTypeSelect.append('<option selected>Choose...</option>');
				
				data.forEach(function(category) {
					var option = $('<option></option>').val(category.id).text(category.room_type);
					roomTypeSelect.append(option);
				});
			}).fail(function(error) {
				console.error('Error fetching room categories:', error);
			});
		}

		// Initial load
		populateRoomTypeDropdown();

		// Handle form submission
		$('#addRoomForm').submit(function(event) {
			event.preventDefault();
			
			var roomNumber = $('#roomNumber').val();
			var roomType = $('#roomType').val();
			var capacity = $('#capacity').val();

			$.ajax({
				url: 'php/add_room.php',
				method: 'POST',
				data: {
					hostel_id: currentHostelId,
					roomNumber: roomNumber,
					roomType: roomType,
					capacity: capacity
				},
				success: function(response) {
					if (response.status === 'success') {
						if (window.PT) window.PT.success(response.message || 'Room added successfully!');
						$('#addRoomForm')[0].reset();
						loadAllData();
					} else {
						if (window.PT) window.PT.error(response.message || 'Failed to add room');
					}
				},

				error: function(error) {
					console.error('Error adding room:', error);
				}
			});
		});
</script>

<script>
		var currentHostelId = $('#hostelSelector').val();

		$('#hostelSelector').on('change', function() {
			currentHostelId = $(this).val();
		});

		// Handle form submission
		$('#addCategoryForm').submit(function(event) {
			event.preventDefault();

			var categoryName = $('#categoryName').val();
			var categoryRate = $('#categoryRate').val();

			$.ajax({
				url: 'php/add_category.php',
				method: 'POST',
				data: {
					hostel_id: currentHostelId,
					categoryName: categoryName,
					categoryRate: categoryRate
				},
				success: function(response) {
					if (response.status === 'success') {
						if (window.PT) window.PT.success(response.message || 'Room category added successfully!');
						$('#addCategoryForm')[0].reset();
						loadCategoriesTable();
					} else {
						if (window.PT) window.PT.error(response.message || 'Failed to add room category');
					}
				},
				error: function(error) {
					console.error('Error adding room category:', error);
				}
			});
		});
</script>

<?php include 'includes/footer.php'; ?>
