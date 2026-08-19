<?php
require_once __DIR__ . '/php/rbac.php';
pt_require_page('list_student');

include 'php/fetch_admin_info.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>

	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">


	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- PAGE TITLE HERE -->
	<title>Pa-etos Hostel Accommodation </title>

	<!-- Favicon icon -->
	<link rel="shortcut icon" type="image/png" href="images/paetoa.png">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link class="main-css" href="css/style.css" rel="stylesheet">

	<link class="main-css" href="css/style.css" rel="stylesheet">
	<link href="vendor/toastr/css/toastr.min.css" rel="stylesheet">
	<link href="css/paetos.css" rel="stylesheet">
	<link href="vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
	<script>(function(){try{var t=localStorage.getItem('pt_theme');if(t!=='dark'&&t!=='light'){t='light'}document.documentElement.setAttribute('data-pt-theme',t)}catch(e){document.documentElement.setAttribute('data-pt-theme','light')}})();</script>
</head>

<body>

	<!-- Preloader with Paetos logo -->
	<div id="preloader">
		<div class="pt-pre-brand">
			<img class="pt-pre-logo" src="images/paetoa.png" alt="Pa-etos Logo" width="70" height="70">
			<div class="pt-spinner"></div>
		</div>
	</div>

	<!--**********************************
		Main wrapper start
	***********************************-->
	<div id="main-wrapper">

		<!--**********************************
			Nav header start
		***********************************-->
		<div class="nav-header">
			<a href="admin-dashboard.php" class="brand-logo">



				<img class="logo-abbr" src="images/paetoa.svg" width="134.01" height="48.365" viewBox="0 0 134.01 48.365">
				<g id="Group_38" data-name="Group 38" transform="translate(-133.99 -40.635)">


				</g>
				</svg>

			</a>
			<div class="nav-control">
				<div class="hamburger">
					<span class="line"></span><span class="line"></span><span class="line"></span>
				</div>
			</div>
		</div>
		<!--**********************************
			Nav header end
		***********************************-->

		<div class="header">
			<div class="header-content">
				<nav class="navbar navbar-expand">
					<div class="collapse navbar-collapse justify-content-between">
						<div class="header-left">
							<div class="dashboard_bar">
							 <span class="text-danger text-bold">       Dashboard</span> 
							</div>

						</div>
						<ul class="navbar-nav header-right">

							<li class="nav-item dropdown notification_dropdown">
								<a class="nav-link bell dlab-theme-mode p-0" href="#;">
									<i id="icon-light" class="fas fa-sun"></i>
									<i id="icon-dark" class="fas fa-moon"></i>

								</a>
							</li>



							<li class="nav-item dropdown header-profile">
								<a class="nav-link" href="#;" role="button" data-bs-toggle="dropdown">
									<img src="images/veritas.png" alt="">

									<div class="dropdown-menu dropdown-menu-end">
										<a href="profile.php" class="dropdown-item ai-icon">
											<svg id="icon-user2" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
												<circle cx="12" cy="7" r="4"></circle>
											</svg>
											<span class="ms-2">Profile </span>
										</a>

										<a href="php/admin_logout.php" class="dropdown-item ai-icon">
											<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
												<polyline points="16 17 21 12 16 7"></polyline>
												<line x1="21" y1="12" x2="9" y2="12"></line>
											</svg>
											<span class="ms-2">Logout </span>
										</a>
									</div>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</div>



		<!--**********************************
			Sidebar start
		***********************************-->
		<div class="dlabnav">
			<div class="dlabnav-scroll">
				<div class="dropdown header-profile2 ">
					<a class="nav-link " href="admin-dashboard.php" role="button" data-bs-toggle="dropdown">
						<div class="header-info2 d-flex align-items-center">
							<img src="images/veritas.png" alt="">

							<div class="d-flex align-items-center sidebar-info">
								<div>
									<span class="font-w400 d-block"><?php echo htmlspecialchars($admin['username']); ?></span>
									<small class="text-end font-w400"><?php echo htmlspecialchars($admin['email']); ?></small>
								</div>
								<i class="fas fa-chevron-down"></i>
							</div>

						</div>
					</a>
					
				</div>
				<ul class="metismenu" id="menu">
					<li><a class="" href="admin-dashboard.php" aria-expanded="false">
							<i class="flaticon-025-dashboard"></i>
							<span class="nav-text">Dashboard</span>
						</a>
						

					</li>
					<li><a class="" href="manage-hostel.php" aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">	Manage Hotel</span>
						</a>
						
					</li>
				
					<li><a class="" href="confirm-payments.php" aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">	Confirm Payment</span>
						</a>
						
					</li>
					<li><a class="" href="list-student.php" aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">	List Student</span>
						</a>
						
					</li>
					<li><a class="" href="manage-hostel.php#view_room" aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">	View Hostel</span>
						</a>
						
					</li>
					
					<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
							<i class="flaticon-093-waving"></i>
							<span class="nav-text">Complain</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="#">Payment Issue</a></li>
							<li><a href="#">Repair Issues</a></li>

						</ul>
					</li>

				</ul>

			</div>
		</div>
		<!--**********************************
			Sidebar end
		***********************************-->

		<!--**********************************
			Content body end
		***********************************-->

		<div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles">
            </div>
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
                            <div class="table-responsive">
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


		
		<!--**********************************
			Footer start
		***********************************-->
		<div class="footer">
			<div class="copyright">
					<p>All right reserved <a href="#" target="_blank"> Pa-etos Ltd </a> 2024</p>
			</div>
		</div>



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
	<script src="vendor/datatables/js/jquery.dataTables.min.js"></script>

<script src="vendor/toastr/js/toastr.min.js"></script>
<script src="js/paetos.js"></script>

<script>
    jQuery(document).ready(function() {
        setTimeout(function() {
            new dlabSettings(dlabSettingsOptions);
        }, 1500)
    });
</script>
 <script>
    var allUsers = [];
    var userTable;

    $(document).ready(function() {
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

        // Load data
        $.getJSON('php/fetch_user.php', function(data) {
            if (data.status === 'success') {
                allUsers = data.users;
                userTable.clear().rows.add(allUsers).draw();
            }
        });

        // Search box wired to DataTables
        $('#searchInput').on('keyup', function() {
            userTable.search(this.value).draw();
        });

        // Edit button click
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

        // Image preview on file select
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

        // Save changes
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

            $.ajax({
                url: 'php/update_user_full.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('editStudentModal')).hide();
                        // Reload data
                        $.getJSON('php/fetch_user.php', function(data) {
                            if (data.status === 'success') {
                                allUsers = data.users;
                                userTable.clear().rows.add(allUsers).draw();
                            }
                        });
                        alert('Student updated successfully!');
                    } else {
                        alert('Error: ' + (response.message || 'Update failed'));
                    }
                },
                error: function() {
                    alert('Error updating student. Please try again.');
                }
            });
        });
    });
</script>



</body>


</html>