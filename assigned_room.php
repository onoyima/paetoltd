

<!--**********************************
			Sidebar start
		***********************************-->
		 <?php include 'admin-sidebar.php'?>
		<!--**********************************
			Sidebar end
		***********************************-->
        <!--**********************************
            Content body end
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles"></div>
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
                                <div class="table-responsive">
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
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Content will be dynamically loaded here via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button id="downloadCSV" class="btn btn-primary">Download CSV</button>
                                <button id="downloadFilteredExcel" class="btn btn-warning">Download Filtered Excel</button>
                                <button id="printTable" class="btn btn-secondary">Print Table</button>
                                <button id="uploadCSVBtn" class="btn btn-success">Upload CSV</button>
								 <button id="downloadCSVTemplate" class="btn btn-info">Download CSV Template</button>
                                
                            </div>
                            
                            <!-- CSV Upload Modal -->
                            <div class="modal fade" id="csvUploadModal" tabindex="-1" role="dialog" aria-labelledby="csvUploadModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="csvUploadModalLabel">Upload CSV File</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="csvUploadForm" enctype="multipart/form-data">
                                                <div class="form-group">
                                                    <label for="csvFile">Select CSV File</label>
                                                    <input type="file" class="form-control-file" id="csvFile" name="csv_file" accept=".csv" required>
                                                </div>
                                                <div class="alert alert-info">
                                                    <p>CSV file should have the following columns:</p>
                                                    <ol>
                                                        <li>Student Name</li>
                                                        <li>Matric No</li>
                                                        <li>Department</li>
                                                        <li>Parent Number</li>
                                                        <li>Level</li>
                                                        <li>Student Number</li>
                                                       
                                                    </ol>
                                                    <p>Room Bunk is used as the unique identifier and cannot be changed.</p>
                                                     <!-- <button type="button" class="btn btn-primary" id="submitCSV">Upload</button> -->
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
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
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

        <!--**********************************
            Footer start
        ***********************************-->
        <?php include 'footer.php'?>
        <!--**********************************
            Footer end
        ***********************************-->
    </div>

    <!--**********************************
    Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>
    <!-- Apex Chart -->
    <script src="vendor/apexchart/apexchart.js"></script>
    <script src="vendor/chartjs/chart.bundle.min.js"></script>
    <!-- Chart piety plugin files -->
    <script src="vendor/peity/jquery.peity.min.js"></script>
    <!-- Dashboard 1 -->
    <script src="js/dashboard/dashboard-1.js"></script>
    <script src="vendor/owl-carousel/owl.carousel.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script src="js/demo.js"></script>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Add console log to debug
        console.log('Fetching data from fetch_assigned_room.php');
        $.ajax({
            url: 'php/fetch_assigned_room.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('Data received:', data);
                if (data.status === 'success') {
                    var users = data.users;
                    var tableBody = $('#userTable tbody');
                    tableBody.empty(); // Clear existing rows
                    
                    if (users.length === 0) {
                        tableBody.append('<tr><td colspan="9" class="text-center">No assigned rooms found</td></tr>');
                        return;
                    }

                users.forEach(function(user, index) {
                    var row = `
                        <tr>
                           <td>${index + 1}</td>
                            <td>${user.student_name}</td>
                            <td>${user.matric_no}</td>
                            <td>${user.department}</td>
                            <td>${user.parent_number}</td>
                            <td>${user.level}</td>
                            <td>${user.student_number}</td>
                            <td>${user.room_bunk}</td>
                            <td>
                                <button class="btn btn-primary btn-sm update-btn" data-id="${user.sn}" 
                                data-student-name="${user.student_name}" 
                                data-matric-no="${user.matric_no}" 
                                data-department="${user.department}" 
                                data-parent-number="${user.parent_number}" 
                                data-level="${user.level}" 
                                data-student-number="${user.student_number}" 
                                data-room-bunk="${user.room_bunk}">Update</button>
                               
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            } else {
                console.error('Error:', data.message);
                $('#userTable tbody').html('<tr><td colspan="9" class="text-center">Error: ' + data.message + '</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.log('Response Text:', xhr.responseText);
            $('#userTable tbody').html('<tr><td colspan="9" class="text-center">Error loading data. Please try again.</td></tr>');
        }
    });
        
        // Handle revoke button click
        $(document).on('click', '.revoke-btn', function() {
            if (confirm('Are you sure you want to revoke this room assignment?')) {
                var studentId = $(this).data('id');
                $.ajax({
                    url: 'php/revoke_room.php',
                    type: 'POST',
                    data: { id: studentId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Room assignment revoked successfully!');
                            // Reload the table
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred. Please try again.');
                        console.error(error);
                    }
                });
            }
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
            $('#updateStudentModal').modal('show');
        });
        
        // Handle save changes button click
        $('#saveStudentChanges').on('click', function() {
            var formData = $('#updateStudentForm').serialize();
            
            $.ajax({
                url: 'php/update_student_room.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        $('#updateStudentModal').modal('hide');
                        // Reload the table
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred during CSV upload. Please try again.\n\nStatus: ' + status + '\nError: ' + error);
                    console.error('CSV Upload AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText);
                }
            });
        });
        
        // Handle CSV upload button click
        $('#uploadCSVBtn').on('click', function() {
            $('#csvUploadModal').modal('show');
        });
        
        // Handle CSV submit button click
        $('#submitCSV').on('click', function() {
            var formData = new FormData($('#csvUploadForm')[0]);
            
            $.ajax({
                url: 'php/upload_csv.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        $('#csvUploadModal').modal('hide');
                        // Reload the table
                        location.reload();
                    } else {
                        var errorMsg = 'Error: ' + response.message;
                        if (response.errors && response.errors.length > 0) {
                            errorMsg += '\n\nDetails:\n' + response.errors.join('\n');
                        }
                        alert(errorMsg);
                        console.error('CSV Upload Error:', response);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred. Please try again.');
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
        }

        csv.push(row.join(","));
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
    var csv = [];
    var rows = document.querySelectorAll("#userTable tr");

    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");

        // Skip first and last column
        for (var j = 1; j < cols.length - 1; j++) {
            row.push(cols[j].innerText);
        }

        csv.push(row.join(","));
    }

    // Create a CSV Blob
    var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
    var downloadLink = document.createElement("a");

    downloadLink.download = "students_data.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";

    document.body.appendChild(downloadLink);
    downloadLink.click();
}



        function printTable() {
            var printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Print Table</title>');
            printWindow.document.write('<link rel="stylesheet" href="css/style.css">'); // Include CSS if needed
            printWindow.document.write('</head><body >');
            printWindow.document.write(document.querySelector('#userTable').outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }

        function downloadFilteredExcel() {
            // Get only visible (filtered) rows
            var visibleRows = [];
            var headers = [];
            
            // Get table headers
            $('#userTable thead tr th').each(function(index) {
                if (index < 8) { // Exclude the Action column
                    headers.push($(this).text().trim());
                }
            });
            
            // Get visible row data
            $('#userTable tbody tr:visible').each(function() {
                var rowData = [];
                $(this).find('td').each(function(index) {
                    if (index < 8) { // Exclude the Action column
                        rowData.push($(this).text().trim());
                    }
                });
                if (rowData.length > 0) {
                    visibleRows.push(rowData);
                }
            });
            
            if (visibleRows.length === 0) {
                alert('No data to export. Please check your filters.');
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

        $('#downloadCSV').on('click', downloadCSV);
		 $('#downloadCSVTemplate').on('click', downloadCSVTemplate);
         $('#downloadFilteredExcel').on('click', downloadFilteredExcel);
        $('#printTable').on('click', printTable);
        
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
        $('#searchName, #searchMatric, #searchDepartment, #searchLevel, #searchStudentNumber, #searchRoomBunk').on('keyup', function() {
            filterTable();
        });
        
        // Clear all filters
         $('#clearFilters').on('click', function() {
             $('#searchName, #searchMatric, #searchDepartment, #searchLevel, #searchStudentNumber, #searchRoomBunk').val('');
             $('#userTable tbody tr').show();
         });
     });
    </script>
</body>

</html>
