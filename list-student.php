<?php
session_start();

// Check iffgd usegffdr is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header("Location: admin_login.html"); // Redirect to login page if not logged in or not admin
	exit();
}

include 'php/fetch_admin_info.php'; // Include file to fetch admin info

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

	<!-- Globle CSS -->
	<link class="main-css" href="css/style.css" rel="stylesheet">

</head>

<body>

	<!--*******************
		Preloader start
	********************-->
	<div id="preloader">
		<div class="lds-ripple">
			<div></div>
			<div></div>
		</div>
	</div>
	<!--*******************
		Preloader end
	********************-->

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
				<div><a href="edit-student.php" class="btn btn-primary btn-sm btn-save col-4">Eidt Student</a></div>
				
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

	<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- DataTables JavaScript -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
	<!-- <script src="js/styleSwitcher.js"></script> -->
	<script>
		jQuery(document).ready(function() {
			setTimeout(function() {
				dlabSettingsOptions.version = 'dark';
				new dlabSettings(dlabSettingsOptions);
			}, 1500)
		});

		function JobickCarousel() {
			/*  testimonial one function by = owl.carousel.js */
			jQuery('.front-view-slider').owlCarousel({
				loop: false,
				margin: 30,
				nav: false,
				autoplaySpeed: 3000,
				navSpeed: 3000,
				autoWidth: true,
				paginationSpeed: 3000,
				slideSpeed: 3000,
				smartSpeed: 3000,
				autoplay: false,
				animateOut: 'fadeOut',
				dots: false,
				navText: ['', ''],
				responsive: {
					0: {
						items: 1,

						margin: 10
					},

					480: {
						items: 1
					},

					767: {
						items: 3
					},
					1750: {
						items: 3
					}
				}
			})
		}
		jQuery(window).on('load', function() {
			setTimeout(function() {
				JobickCarousel();
			}, 1000);
		});
	</script>
 <script>
    $(document).ready(function() {
        $.getJSON('php/fetch_user.php', function(data) {
            if (data.status === 'success') {
                var users = data.users;
                var tableBody = $('#userTable tbody'); // Corrected selector for table body
                tableBody.empty(); // Clear existing rows

                users.forEach(function(user, index) {
                    var row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${user.regNo}</td>
                            <td>${user.firstName}</td>
                            <td>${user.middleName}</td>
                            <td>${user.lastName}</td>
                            <td>${user.gender}</td>
                            <td>${user.contactNo}</td>
                            <td>${user.email}</td>
                            <td>
                                <!-- Add actions here if needed -->
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            } else {
                console.error('Error:', data.message);
            }
        }).fail(function(error) {
            console.error('Error fetching user data:', error);
        });
    });
</script>



</body>


</html>