<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + assign_room permission
pt_require_page('assign_room');

include 'php/fetch_admin_info.php';
require_once __DIR__ . '/php/academic_helper.php';
$hostels = pt_all_hostels();

$pageTitle = 'Assigned Rooms';
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
			<div class="col-md-6">
				<h4>Assign Room</h4>
				<ul class="nav nav-pills flex-column flex-sm-start mb-3" id="assignHostelTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link active" id="assign-hostel-all-tab" data-bs-toggle="tab" data-bs-target="#assign-hostel-all" role="tab" aria-controls="assign-hostel-all" aria-selected="true">All Hostels</a>
					</li>
					<?php foreach ($hostels as $h): ?>
						<li class="nav-item" role="presentation">
							<a class="nav-link" id="assign-hostel-<?php echo (int)$h['id']; ?>-tab" data-bs-toggle="tab" data-bs-target="#assign-hostel-<?php echo (int)$h['id']; ?>" role="tab" aria-controls="assign-hostel-<?php echo (int)$h['id']; ?>" aria-selected="false"><?php echo htmlspecialchars($h['name']); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">List of Assigned Rooms</h4>
					</div>
					<div class="card-footer">
						<button id="downloadCSV" class="btn btn-primary">Download CSV</button>
						<button id="printTable" class="btn btn-secondary">Print Table</button>
						<button id="uploadCSVBtn" class="btn btn-success">Upload CSV</button>
						<button id="downloadCSVTemplate" class="btn btn-info">Download CSV Template</button>
						
					</div>
					<div class="card-body">
						<!-- Search and Filter Section -->
						<div class="row mb-3">
							<div class="col-md-6">
								<h5>Search & Filter Students</h5>
							</div>
							<div class="col-md-6">
							   <button id="downloadFilteredExcel" class="btn btn-warning">Download Filtered Excel</button>
							</div>
							
						</div>
						<div class="row mb-2">
							<div class="col-md-2">
								<input type="text" class="form-control" id="searchName" placeholder=" Name">
							</div>
							<div class="col-md-2">
								<input type="text" class="form-control" id="searchMatric" placeholder=" Matric No">
							</div>
							<div class="col-md-2">
								<input type="text" class="form-control" id="searchDepartment" placeholder=" Department">
							</div>
							<div class="col-md-2">
								<input type="text" class="form-control" id="searchLevel" placeholder=" Level">
							</div>
							<div class="col-md-2">
								<input type="text" class="form-control" id="searchStudentNumber" placeholder=" Student Number">
							</div>
							<div class="col-md-2">
								<input type="text" class="form-control" id="searchRoomBunk" placeholder=" Room Bunk">
							</div>
						</div>
						<div class="row mb-2">
							<div class="col-md-2">
								<button type="button" class="btn btn-primary" id="clearFilters">Clear All Filters</button>
							</div>
						</div>
						<hr>
						<div class="table-responsive pt-table-wrap">
							<table class="table" id="userTable">
								<thead>
									<tr>
										<th>Serial Number</th>
										<th>Student Name</th>
										<th>Matric No</th>
										<th>Department</th>
										<th>Parent Number</th>
										<th>Level</th>
										<th>Student Number</th>
										<th>Room Bunk</th>
										<th>Hostel</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<!-- Content will be dynamically loaded here via JavaScript -->
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- CSV Upload Modal -->
					<div class="modal fade" id="csvUploadModal" tabindex="-1" role="dialog" aria-labelledby="csvUploadModalLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="csvUploadModalLabel">Upload CSV File</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<form id="csvUploadForm" enctype="multipart/form-data">
										<div class="form-group">
											<label for="csvFile">Select CSV File</label>
											<input type="file" class="form-control-file" id="csvFile" name="csv_file" accept=".csv" required>
										</div>
										<div class="form-group">
											<label for="hostelSelector">Hostel</label>
											<select id="hostelSelector" name="hostel_id" class="default-select form-control" required>
												<option value="0">All Hostels</option>
												<?php 
												$hostels = pt_all_hostels(); 
												foreach ($hostels as $h): 
												?>
												<option value="<?php echo (int)$h['id']; ?>"><?php echo htmlspecialchars($h['name']); ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="form-group">
											<label for="sessionSelector">Academic Session</label>
											<select id="sessionSelector" name="session_id" class="default-select form-control" required>
												<?php 
												$sessions = pt_all_sessions(); 
												foreach ($sessions as $s): 
												?>
												<option value="<?php echo (int)$s['id']; ?>" <?php echo !empty($s['is_active']) ? 'selected' : ''; ?>>
													<?php echo htmlspecialchars($s['name']); ?><?php echo !empty($s['is_active']) ? ' (Active)' : ''; ?>
												</option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class="alert alert-info">
											<p>CSV file should have the following columns (8 columns total):</p>
											<ol>
												<li>Student Name</li>
												<li>Matric No</li>
												<li>Department</li>
												<li>Parent Number</li>
												<li>Level</li>
												<li>Student Number</li>
												<li>Room Bunk</li>
												<li>Bed Space (e.g., A1, B2, C1)</li>
											</ol>
											<p>Room Bunk is used as the unique identifier and cannot be changed.</p>
											 <!-- <button type="button" class="btn btn-primary" id="submitCSV">Upload</button> -->
										</div>
									</form>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									<button type="button" class="btn btn-primary" id="submitCSV">Upload</button>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Update Student Modal -->
					<div class="modal fade" id="updateStudentModal" tabindex="-1" role="dialog" aria-labelledby="updateStudentModalLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="updateStudentModalLabel">Update Student Information</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<form id="updateStudentForm">
										<input type="hidden" id="studentId" name="id">
										<div class="form-group">
											<label for="studentName">Student Name</label>
											<input type="text" class="form-control" id="studentName" name="student_name" required>
										</div>
										<div class="form-group">
											<label for="matricNo">Matric No</label>
											<input type="text" class="form-control" id="matricNo" name="matric_no" required>
										</div>
										<div class="form-group">
											<label for="department">Department</label>
											<input type="text" class="form-control" id="department" name="department" required>
										</div>
										<div class="form-group">
											<label for="parentNumber">Parent Number</label>
											<input type="text" class="form-control" id="parentNumber" name="parent_number" required>
										</div>
										<div class="form-group">
											<label for="level">Level</label>
											<input type="text" class="form-control" id="level" name="level" required>
										</div>
										<div class="form-group">
											<label for="studentNumber">Student Number</label>
											<input type="text" class="form-control" id="studentNumber" name="student_number" required>
										</div>
										<div class="form-group">
											<label for="roomBunk">Room Bunk</label>
											<input type="text" class="form-control" id="roomBunk" name="room_bunk" readonly>
											<small class="form-text text-muted">Room Bunk cannot be changed.</small>
										</div>
									</form>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									<button type="button" class="btn btn-primary" id="saveStudentChanges">Save Changes</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, function(c) {
        return {'&': '&', '<': '<', '>': '>', '"': '"', "'": '''}[c];
    });
}

// Hostel id -> name map for table display
var hostelMap = {};
<?php foreach ($hostels as $h): ?>
    hostelMap[<?php echo (int)$h['id']; ?>] = <?php echo json_encode($h['name']); ?>;
<?php endforeach; ?>


// Execute immediately (PTNav runs scripts after AJAX swap)
var tableWrap = $('#userTable').closest('.pt-table-wrap')[0];
PT.tableLoading(tableWrap, true);

console.log('AJAX init: tableWrap found = ' + (tableWrap !== undefined));

// Initial hostel selection - get first hostel or 0 for all
var currentHostelId = 0;
var currentTab = 'all';

// Initialize hostel tabs from sidebar
$('#assignHostelTabs a').on('click', function() {
    currentTab = $(this).attr('id').replace('assign-hostel-', '').replace('-tab', '');
    currentHostelId = currentTab === 'all' ? 0 : parseInt(currentTab);
    fetchAssignedRooms();
});

function getAssignHostelUrl(baseUrl) {
    return baseUrl + '?hostel_id=' + currentHostelId;
}

// Fetch assigned rooms with hostel + session filtering
function fetchAssignedRooms() {
    var sessionId = $('#sessionSelector').length ? parseInt($('#sessionSelector').val()) : 0;
    console.log('Fetching data from fetch_assigned_room.php with hostel_id=' + currentHostelId + ', session_id=' + sessionId);
    $.ajax({
        url: 'php/fetch_assigned_room.php',
        type: 'GET',
        dataType: 'json',
        data: { hostel_id: currentHostelId, session_id: sessionId },
        success: function(data) {
            console.log('AJAX success: status = ' + data.status + ', users length = ' + (data.users ? data.users.length : 0));
            PT.tableLoading(tableWrap, false);
            if (data.status === 'success') {
                var users = data.users;
                var tableBody = $('#userTable tbody');
                tableBody.empty(); // Clear existing rows
                
                // Always display data - show the rows
                if (users && users.length > 0) {
                    users.forEach(function(user, index) {
                        var row = `
                            <tr>
                               <td>${index + 1}</td>
                                <td>${esc(user.student_name)}</td>
                                <td>${esc(user.matric_no)}</td>
                                <td>${esc(user.department)}</td>
                                <td>${esc(user.parent_number)}</td>
                                <td>${esc(user.level)}</td>
                                <td>${esc(user.student_number)}</td>
                                <td>${esc(user.room_bunk)}</td>
                                <td>${esc(hostelMap[user.hostel_id] || '')}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm update-btn" data-id="${esc(user.sn)}" 
                                    data-student-name="${esc(user.student_name)}" 
                                    data-matric-no="${esc(user.matric_no)}" 
                                    data-department="${esc(user.department)}" 
                                    data-parent-number="${esc(user.parent_number)}" 
                                    data-level="${esc(user.level)}" 
                                    data-student-number="${esc(user.student_number)}" 
                                    data-room-bunk="${esc(user.room_bunk)}">Update</button>
                                    
                                </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                    // Show info about row count
                    if (window.console) {
                        console.log('Displayed ' + users.length + ' assigned rooms');
                    }
                } else {
                    tableBody.append('<tr><td colspan="10" class="text-center">No assigned rooms found</td></tr>');
                }
            } else {
                console.error('Error:', data.message);
                $('#userTable tbody').html('<tr><td colspan="10" class="text-center">Error: ' + esc(data.message) + '</td></tr>');
            }
        }
    error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
        console.log('Response Text:', xhr.responseText);
        PT.tableLoading(tableWrap, false);
        $('#userTable tbody').html('<tr><td colspan="10" class="text-center">Error loading data. Please try again.</td></tr>');
    }
});
};

// Handle revoke button click
$(document).on('click', '.revoke-btn', function() {
        var studentId = $(this).data('id');
        PT.confirm('Are you sure you want to revoke this room assignment?').then(function(ok) {
            if (!ok) return;
            var btn = this;
            PT.btnLoading(this, true);
            $.ajax({
                url: 'php/revoke_room.php',
                type: 'POST',
                data: { id: studentId },
                dataType: 'json',
                success: function(response) {
                    PT.btnLoading(btn, false);
                    if (response.status === 'success') {
                        PT.success(response.message || 'Room assignment revoked successfully!');
                        setTimeout(function(){ location.reload(); }, 900);
                    } else {
                        PT.error('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    PT.btnLoading(btn, false);
                    PT.error('An error occurred. Please try again.');
                    console.error(error);
                }
            });
        }.bind(this));
    });
    
    // Handle update button click
    $(document).on('click', '.update-btn', function() {
        var studentId = $(this).data('id');
        var studentName = $(this).data('student-name');
        var matricNo = $(this).data('matric-no');
        var department = $(this).data('department');
        var parentNumber = $(this).data('parent-number');
        var level = $(this).data('level');
        var studentNumber = $(this).data('student-number');
        var roomBunk = $(this).data('room-bunk');
        
        // Populate the modal with student data
        $('#studentId').val(studentId);
        $('#studentName').val(studentName);
        $('#matricNo').val(matricNo);
        $('#department').val(department);
        $('#parentNumber').val(parentNumber);
        $('#level').val(level);
        $('#studentNumber').val(studentNumber);
        $('#roomBunk').val(roomBunk);
        
        // Show the modal
        var updateModal = new bootstrap.Modal(document.getElementById('updateStudentModal'));
        updateModal.show();
    });
    
    // Handle save changes button click
    $(document).on('click', '#saveStudentChanges', function() {
        var formData = $('#updateStudentForm').serialize();
        var btn = this;
        PT.btnLoading(btn, true);

        $.ajax({
            url: 'php/update_student_room.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                PT.btnLoading(btn, false);
                if (response.status === 'success') {
                    PT.success(response.message);
                    bootstrap.Modal.getInstance(document.getElementById('updateStudentModal'))?.hide();
                    // Reload the table
                    setTimeout(function(){ location.reload(); }, 900);
                } else {
                    PT.error('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                PT.btnLoading(btn, false);
                PT.error('An error occurred during update. Please try again.');
                console.error('Update AJAX Error:', status, error);
                console.log('Response Text:', xhr.responseText);
            }
        });
    });
    
// Handle CSV upload button click
$(document).on('click', '#uploadCSVBtn', function() {
    // Default the upload hostel selector to the currently selected tab
    if (currentHostelId > 0) {
        $('#hostelSelector').val(String(currentHostelId));
    }
    var csvModal = new bootstrap.Modal(document.getElementById('csvUploadModal'));
    csvModal.show();
});

// Re-fetch when the session selector changes
$(document).on('change', '#sessionSelector', function() {
    fetchAssignedRooms();
});
    
    // Handle CSV submit button click
    $(document).on('click', '#submitCSV', function() {
        var formData = new FormData($('#csvUploadForm')[0]);
        var btn = this;
        PT.btnLoading(btn, true);

        $.ajax({
            url: 'php/upload_csv.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                PT.btnLoading(btn, false);
                if (response.status === 'success') {
                    PT.success(response.message);
                    bootstrap.Modal.getInstance(document.getElementById('csvUploadModal'))?.hide();
                    // Reload the table
                    setTimeout(function(){ location.reload(); }, 900);
                } else {
                    var errorMsg = response.message;
                    if (response.errors && response.errors.length > 0) {
                        errorMsg += '<br>Details:<ul>' + response.errors.map(function(e){ return '<li>' + e + '</li>'; }).join('') + '</ul>';
                    }
                    PT.error(errorMsg);
                    console.error('CSV Upload Error:', response);
                }
            },
            error: function(xhr, status, error) {
                PT.btnLoading(btn, false);
                PT.error('An error occurred. Please try again.');
                console.error(error);
            }
        });
    });

function downloadCSV() {
    var csv = [];
    var rows = document.querySelectorAll("#userTable tr");

    for (var i = 0; i < rows.length; i++) {
        var row = [];
        var cols = rows[i].querySelectorAll("td, th");

        // Loop until the second-to-last column
        for (var j = 0; j < cols.length - 1; j++) {
            row.push(cols[j].innerText);
        }        csv.push(row.join(","));
    }

    // Create a CSV Blob
    var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
    var downloadLink = document.createElement("a");

    // File name
    downloadLink.download = "students_data.csv";

    // Create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Make sure that the link is not displayed
    downloadLink.style.display = "none";

    // Add the link to the DOM
    document.body.appendChild(downloadLink);

    // Click the link
    downloadLink.click();
}

	function downloadCSVTemplate() {
    var hostelId = currentHostelId || 0;
    var url = 'download_csv_template.php?hostel_id=' + hostelId;
    
    // Create a temporary link and trigger download
    var downloadLink = document.createElement("a");
    downloadLink.href = url;
    downloadLink.download = 'room_assignment_template_hostel_' + hostelId + '.csv';
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}


function printTable() {
    var printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print Table</title>');
    printWindow.document.write('<link rel="stylesheet" href="css/style.css">'); // Include CSS if needed
    printWindow.document.write('</head><body >');
    printWindow.document.write(document.querySelector('#userTable').outerHTML);
    printWindow.document.close();
    printWindow.print();
}

function downloadFilteredExcel() {
    // Get only visible (filtered) rows
    var visibleRows = [];
    var headers = [];
    
    // Get table headers
    $('#userTable thead tr th').each(function(index) {
        if (index < 9) { // Exclude the Action column
            headers.push($(this).text().trim());
        }
    });
    
    // Get visible row data
    $('#userTable tbody tr:visible').each(function() {
        var rowData = [];
        $(this).find('td').each(function(index) {
            if (index < 9) { // Exclude the Action column
                rowData.push($(this).text().trim());
            }
        });
        if (rowData.length > 0) {
            visibleRows.push(rowData);
        }
    });
    
    if (visibleRows.length === 0) {
        PT.warning('No data to export. Please check your filters.');
        return;
    }
    
    // Create Excel-compatible CSV content
    var csvContent = "\uFEFF"; // BOM for Excel UTF-8 support
    
    // Add headers
    csvContent += headers.join(',') + '\n';
    
    // Add data rows
    visibleRows.forEach(function(row) {
        // Escape commas and quotes in data
        var escapedRow = row.map(function(cell) {
            var cellStr = String(cell);
            if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                return '"' + cellStr.replace(/"/g, '""') + '"';
            }
            return cellStr;
        });
        csvContent += escapedRow.join(',') + '\n';
    });
    
    // Create and download file
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    
    if (link.download !== undefined) {
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'filtered_students_data.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

$(document).on('click', '#downloadCSV', downloadCSV);
	$(document).on('click', '#downloadCSVTemplate', downloadCSVTemplate);
	$(document).on('click', '#downloadFilteredExcel', downloadFilteredExcel);
	$(document).on('click', '#printTable', printTable);
    
    // Search and Filter functionality
    function filterTable() {
        var searchName = $('#searchName').val().toLowerCase();
        var searchMatric = $('#searchMatric').val().toLowerCase();
        var searchDepartment = $('#searchDepartment').val().toLowerCase();
        var searchLevel = $('#searchLevel').val().toLowerCase();
        var searchStudentNumber = $('#searchStudentNumber').val().toLowerCase();
        var searchRoomBunk = $('#searchRoomBunk').val().toLowerCase();
        
        $('#userTable tbody tr').each(function() {
            var row = $(this);
            var studentName = row.find('td:nth-child(2)').text().toLowerCase();
            var matricNo = row.find('td:nth-child(3)').text().toLowerCase();
            var department = row.find('td:nth-child(4)').text().toLowerCase();
            var level = row.find('td:nth-child(6)').text().toLowerCase();
            var studentNumber = row.find('td:nth-child(7)').text().toLowerCase();
            var roomBunk = row.find('td:nth-child(8)').text().toLowerCase();
            
            var showRow = true;
            
            if (searchName && !studentName.includes(searchName)) {
                showRow = false;
            }
            if (searchMatric && !matricNo.includes(searchMatric)) {
                showRow = false;
            }
            if (searchDepartment && !department.includes(searchDepartment)) {
                showRow = false;
            }
            if (searchLevel && !level.includes(searchLevel)) {
                showRow = false;
            }
            if (searchStudentNumber && !studentNumber.includes(searchStudentNumber)) {
                showRow = false;
            }
            if (searchRoomBunk && !roomBunk.includes(searchRoomBunk)) {
                showRow = false;
            }
            
            if (showRow) {
                row.show();
            } else {
                row.hide();
            }
        });
    }
    
    // Add event listeners for search inputs
    $(document).on('keyup', '#searchName, #searchMatric, #searchDepartment, #searchLevel, #searchStudentNumber, #searchRoomBunk', function() {
        filterTable();
    });
    
     // Clear all filters
     $(document).on('click', '#clearFilters', function() {
         $('#searchName, #searchMatric, #searchDepartment, #searchLevel, #searchStudentNumber, #searchRoomBunk').val('');
         $('#userTable tbody tr').show();
     });
</script>

</div>

<?php include 'includes/footer.php'; ?>
