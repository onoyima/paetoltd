<?php
session_start(); // Resume session

// Check if user is logged in and session is active
if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
	// Redirect to login page if not logged in or session expired
	header("Location: login.html");
	exit();
}

// Extend session timeout upon activity
$_SESSION['timeout'] = time() + 1800; // 30 minutes timeout extension

include 'php/fetch_user_details.php'; // Include the function file

// Fetch user details
$students_info = fetch_user_details();
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
			<a href="dashboard.php" class="brand-logo">



				<img class="logo-abbr" src="images/paetoa.svg" width="134.01" height="48.365"
					viewBox="0 0 134.01 48.365">
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
								<?php echo $students_info['firstName'] ?? 'Guest'; ?> Profile Page
							</div>

						</div>
						<ul class="navbar-nav header-right">

							<li class="nav-item dropdown notification_dropdown">
								<a class="nav-link bell dlab-theme-mode p-0" href="#;">
									<i id="icon-light" class="fas fa-sun"></i>
									<i id="icon-dark" class="fas fa-moon"></i>

								</a>
							</li>

							<li class="nav-item dropdown notification_dropdown">
								<a class="nav-link" href="#;" role="button" data-bs-toggle="dropdown">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
										<g data-name="Layer 2" transform="translate(-2 -2)">
											<path id="Path_20" data-name="Path 20"
												d="M22.571,15.8V13.066a8.5,8.5,0,0,0-7.714-8.455V2.857a.857.857,0,0,0-1.714,0V4.611a8.5,8.5,0,0,0-7.714,8.455V15.8A4.293,4.293,0,0,0,2,20a2.574,2.574,0,0,0,2.571,2.571H9.8a4.286,4.286,0,0,0,8.4,0h5.23A2.574,2.574,0,0,0,26,20,4.293,4.293,0,0,0,22.571,15.8ZM7.143,13.066a6.789,6.789,0,0,1,6.78-6.78h.154a6.789,6.789,0,0,1,6.78,6.78v2.649H7.143ZM14,24.286a2.567,2.567,0,0,1-2.413-1.714h4.827A2.567,2.567,0,0,1,14,24.286Zm9.429-3.429H4.571A.858.858,0,0,1,3.714,20a2.574,2.574,0,0,1,2.571-2.571H21.714A2.574,2.574,0,0,1,24.286,20a.858.858,0,0,1-.857.857Z" />
										</g>
									</svg>
									<span class="badge light text-white bg-primary rounded-circle">0</span>
								</a>

							</li>


							<li class="nav-item dropdown header-profile">
								<a class="nav-link" href="#;" role="button" data-bs-toggle="dropdown">
									<?php if (!empty($students_info['userImage'])): ?>
										<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>"
											title="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image"
											alt="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image">
									<?php endif; ?>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="profile.php" class="dropdown-item ai-icon">
										<svg id="icon-user2" xmlns="http://www.w3.org/2000/svg" class="text-primary"
											width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
											stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
											<circle cx="12" cy="7" r="4"></circle>
										</svg>
										<span class="ms-2">Profile </span>
									</a>

									<a href="php/logout.php" class="dropdown-item ai-icon">
										<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18"
											height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
											stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
					<a class="nav-link " href="#;" role="button" data-bs-toggle="dropdown">
						<div class="header-info2 d-flex align-items-center">
							<!-- <img src="images/profile/pic1.jpg" alt=""> -->
							<?php if (!empty($students_info['userImage'])): ?>
								<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>"
									title="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image"
									alt="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image">
							<?php endif; ?>
							<div class="d-flex align-items-center sidebar-info">
								<div>
									<span
										class="font-w400 d-block"><?php echo $students_info['firstName'] ?? 'Guest'; ?></span>
									<small
										class="text-end font-w400"><?php echo $students_info['email'] ?? 'Guest'; ?></small>
								</div>
								<i class="fas fa-chevron-down"></i>
							</div>

						</div>
					</a>
					<div class="dropdown-menu dropdown-menu-end">
						<a href="profile.php" class="dropdown-item ai-icon ">
							<svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18"
								viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
								stroke-linecap="round" stroke-linejoin="round">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
								<circle cx="12" cy="7" r="4"></circle>
							</svg>
							<span class="ms-2">Profile </span>
						</a>

						<a href="php/logout.php" class="dropdown-item ai-icon">
							<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18"
								viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
								stroke-linecap="round" stroke-linejoin="round">
								<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
								<polyline points="16 17 21 12 16 7"></polyline>
								<line x1="21" y1="12" x2="9" y2="12"></line>
							</svg>
							<span class="ms-2">Logout </span>
						</a>
					</div>
				</div>
				<ul class="metismenu" id="menu">
					<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
							<i class="flaticon-025-dashboard"></i>
							<span class="nav-text">Dashboard</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="book-hostel.php">Upload Proof</a></li>
							<li><a href="status.php">Check Status</a></li>

						</ul>

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
			Content body start
		***********************************-->
		<div class="content-body">
			<div class="container-fluid">
				<div class="row page-titles ">

				</div>
				<!-- row -->
				<div class="row">
					<div class="col-xl-3 col-lg-4">
						<div class="clearfix">
							<div class="card card-bx author-profile m-b30">
								<div class="card-body">
									<div class="p-5">
										<div class="author-profile">
											<div class="author-media">
												<?php if (!empty($students_info['userImage'])): ?>
													<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>"
														title="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image"
														alt="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image">
												<?php endif; ?>
												<div class="upload-link" title="" data-bs-toggle="tooltip"
													data-placement="right" data-original-title="update">
													<input type="file" class="update-flie">
													<i class="fa fa-camera"></i>
												</div>
											</div>
											<div class="author-info">
												<h6 class="title"><?php echo $students_info['firstName'] ?? 'Guest'; ?>
													<?php echo $students_info['middleName'] ?? 'Guest'; ?>
													<?php echo $students_info['lastName'] ?? 'Guest'; ?></h6>
												<span><?php echo $students_info['regNo']; ?></span>
											</div>
										</div>
									</div>

								</div>

							</div>
						</div>
					</div>
					<div class="col-xl-9 col-lg-8">
						<div class="card  card-bx m-b30">
							<div class="card-header">
								<h4 class="card-title">BOOKING HOSTEL INFORMATION UPLOAD</h4>
							</div>
							<div id="messageBox" class="">
                                <!-- Error or success messages will be displayed here -->
                            </div>

							<form class="profile-form" onsubmit="event.preventDefault(); submitPaymentInfo();" enctype="multipart/form-data">
							<div id="messageBox" class="">
                                <!-- Error or success messages will be displayed here -->
                            </div>
							<div class="card-body">
									<div class="row">
										<div class="col-sm-6 m-b30">
											<label class="form-label">First Name</label>
											<input type="text" class="form-control"
												value="<?php echo $students_info['firstName']; ?>" readonly>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Last Name</label>
											<input type="text" class="form-control"
												value="<?php echo $students_info['lastName']; ?>" readonly>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Bank Name</label>
											<input type="text" class="form-control" name="bankName"
												placeholder="Your bank Name" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Payer Name</label>
											<input type="text" class="form-control" name="payers_name"
												placeholder="Name on the Account" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label" for="paymentInfo">Payment Receipt</label>
											<input type="file" class="form-control" name="paymentInfo" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Matriculation Number</label>
											<input type="text" class="form-control"
												value="<?php echo $students_info['regNo']; ?>" readonly>
										</div>
										<div class="col-sm-6 m-b30">
											<input type="hidden" class="form-control" name="id"
												value="<?php echo $students_info['id']; ?>" readonly>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button class="btn btn-primary" type="submit">SUBMIT</button>
								</div>
							</form>


						</div>
					</div>
				</div>
			</div>
		</div>

	</div>


	<!--**********************************
			Footer start
		***********************************-->
	<div class="footer">
		<div class="copyright">
			<p>Copyright © Designed &amp; Developed by <a href="#" target="_blank">ClintonCode</a> 2024</p>
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
	<!-- <script src="js/styleSwitcher.js"></script> -->


	<script>
    // Select the message box element
    const messageBox = document.getElementById('messageBox');

    // Function to display message in the message box
    function showMessage(message, type) {
        messageBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    }

    // Handle form submission
    const form = document.querySelector('.profile-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const formData = new FormData(this);

        try {
            const response = await fetch('php/payment_info.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();

            if (result.success) {
                showMessage(result.success, 'success');
                // Clear form fields or do additional tasks on success
            } else if (result.error) {
                showMessage(result.error, 'danger');
            }
        } catch (error) {
            showMessage('Error: ' + error.message, 'danger');
        }
    });
</script>



</body>


</html>