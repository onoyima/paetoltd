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
require_once 'php/config.php'; // Include database configuration
require_once __DIR__ . '/php/academic_helper.php';

// Fetch user details
$students_info = fetch_user_details();
$user_payments = fetch_user_payments();

// Get active session
$activeSession = pt_active_session();
$sessionId = $activeSession ? (int)$activeSession['id'] : 0;

// Check if user has room allocation
$room_allocation = null;
$has_room = false;

if ($sessionId > 0 && !empty($students_info['regNo'])) {
    try {
        $stmt = $conn->prepare("SELECT id, student_name, matric_no, department, level, student_number, parent_number, room_bunk, created_at, updated_at FROM assign_room WHERE matric_no = ? AND session_id = ? LIMIT 1");
        $stmt->bind_param('si', $students_info['regNo'], $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $room_allocation = $result->fetch_assoc();
            $has_room = true;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error checking room allocation: " . $e->getMessage());
    }
}

// Determine button status
$button_text = $has_room ? "View Room Allocation" : "Book Room";
$button_link = $has_room ? "#" : "book-hostel.php";
$room_status = $has_room ? "Room Allocated: " . $room_allocation['room_bunk'] : "You don't have Room yet";
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
	<link href="vendor/toastr/css/toastr.min.css" rel="stylesheet">
	<link href="css/paetos.css" rel="stylesheet">
	<script src="vendor/toastr/css/toastr.min.css" rel="stylesheet">
	<link href="css/paetos.css" rel="stylesheet">
	<script>(function(){try{var t=localStorage.getItem('pt_theme');if(t!=='dark'&&t!=='light'){t=(typeof getCookie==='function'&&getCookie('version')==='dark')?'dark':'light'}document.documentElement.setAttribute('data-pt-theme',t)}catch(e){document.documentElement.setAttribute('data-pt-theme','light')}})();</script>
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
			<a href="#" class="brand-logo">
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
								<?php echo $students_info['firstName'] ?? 'Guest'; ?> Dashboard
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
											<path id="Path_20" data-name="Path 20" d="M22.571,15.8V13.066a8.5,8.5,0,0,0-7.714-8.455V2.857a.857.857,0,0,0-1.714,0V4.611a8.5,8.5,0,0,0-7.714,8.455V15.8A4.293,4.293,0,0,0,2,20a2.574,2.574,0,0,0,2.571,2.571H9.8a4.286,4.286,0,0,0,8.4,0h5.23A2.574,2.574,0,0,0,26,20,4.293,4.293,0,0,0,22.571,15.8ZM7.143,13.066a6.789,6.789,0,0,1,6.78-6.78h.154a6.789,6.789,0,0,1,6.78,6.78v2.649H7.143ZM14,24.286a2.567,2.567,0,0,1-2.413-1.714h4.827A2.567,2.567,0,0,1,14,24.286Zm9.429-3.429H4.571A.858.858,0,0,1,3.714,20a2.574,2.574,0,0,1,2.571-2.571H21.714A2.574,2.574,0,0,1,24.286,20a.858.858,0,0,1-.857.857Z" />
										</g>
									</svg>
									<span class="badge light text-white bg-primary rounded-circle">0</span>
								</a>
							</li>

							<li class="nav-item dropdown header-profile">
								<a class="nav-link" href="#;" role="button" data-bs-toggle="dropdown">
									<?php if (!empty($students_info['userImage'])) : ?>
										<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image" alt="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image">
									<?php endif; ?>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="profile.php" class="dropdown-item ai-icon">
										<svg id="icon-user2" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
											<circle cx="12" cy="7" r="4"></circle>
										</svg>
										<span class="ms-2">Profile </span>
									</a>
									<a href="php/logout.php" class="dropdown-item ai-icon">
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
					<a class="nav-link " href="#;" role="button" data-bs-toggle="dropdown">
						<div class="header-info2 d-flex align-items-center">
							<?php if (!empty($students_info['userImage'])) : ?>
								<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image" alt="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image">
							<?php endif; ?>
							<div class="d-flex align-items-center sidebar-info">
								<div>
									<span class="font-w400 d-block"><?php echo $students_info['firstName'] ?? 'Guest'; ?> <?php echo $students_info['middleName'] ?? 'Guest'; ?> <?php echo $students_info['lastName'] ?? 'Guest'; ?></span>
									<small class="text-end font-w400"><?php echo $students_info['email'] ?? 'Guest'; ?></small>
								</div>
								<i class="fas fa-chevron-down"></i>
							</div>
						</div>
					</a>
					<div class="dropdown-menu dropdown-menu-end">
						<a href="profile.php" class="dropdown-item ai-icon ">
							<svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
								<circle cx="12" cy="7" r="4"></circle>
							</svg>
							<span class="ms-2">Profile </span>
						</a>
						<a href="php/logout.php" class="dropdown-item ai-icon">
							<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
								<polyline points="16 17 21 12 16 7"></polyline>
								<line x1="21" y1="12" x2="9" y2="12"></line>
							</svg>
							<span class="ms-2">Logout </span>
						</a>
					</div>
				</div>
				<ul class="metismenu" id="menu">
					<li><a href="dashboard.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-025-dashboard"></i>
							<span class="nav-text">Dashboard</span>
						</a>
					</li>
					<li><a href="book-hostel.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">Book Room</span>
						</a>
					</li>
					<li class="mm-active"><a href="check-room.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">Check Room</span>
						</a>
					</li>

					<li><a class="has-arrow" href="javascript:void()" aria-expanded="false">
							<i class="flaticon-093-waving"></i>
							<span class="nav-text">Complain</span>
						</a>
						<ul aria-expanded="false">
							<li>
								<a class="" href="javascript:void(0)" onclick="paymentsendWhatsApp()" aria-expanded="false">
									<i class="flaticon-093-waving"></i>
									<span class="nav-text">Payment Issues</span>
								</a>
							</li>
							<li>
								<a class="" href="tel:+2348033300519" aria-expanded="false">
									<i class="flaticon-093-waving"></i>
									<span class="nav-text">Call Admin</span>
								</a>
							</li>
							<li>
								<a class="" href="javascript:void(0)" onclick="sendWhatsApp()" aria-expanded="false">
									<i class="flaticon-093-waving"></i>
									<span class="nav-text">WhatsApp Admin</span>
								</a>
							</li>
						</ul>
					</li>

					<li><a href="change-password.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-046-setting"></i>
							<span class="nav-text">Password Reset</span>
						</a>
					</li>

					<div class="pt-sidebar-foot">
						<div class="pt-sidebar-item" role="button" aria-label="Toggle dark mode">
							<i class="fas fa-moon"></i>
							<span class="nav-text">Dark Mode</span>
							<label class="pt-switch">
								<input type="checkbox" id="pt-theme-switch">
								<span class="pt-switch-slider"></span>
							</label>
						</div>
						<a href="php/logout.php" class="pt-sidebar-item pt-sidebar-logout" aria-label="Logout">
							<i class="fas fa-sign-out-alt"></i>
							<span class="nav-text">Logout</span>
						</a>
					</div>
				</ul>
			</div>
		</div>
		<!--**********************************
			Sidebar end
		***********************************-->

		<div class="content-body">
			<!-- row -->
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-6">
						<div class="row">
							<div class="col-xl-12">
								<div class="card">
									<div class="card-body">
										<div class="row ">
											<div class="card">
												<div class="card-body text-center ai-icon text-primary">
													<?php if ($has_room): ?>
														<svg id="check-icon" class="my-2" viewBox="0 0 24 24" width="80" height="40" stroke="currentColor" stroke-width="1" fill="none" stroke-linecap="round" stroke-linejoin="round">
															<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
															<polyline points="22,4 12,14.01 9,11.01"></polyline>
														</svg>
													
														<button type="button" class="btn btn-success btn-block" id="viewRoomBtn">View My Room Allocation</button>
														<button type="button" class="btn btn-primary btn-block mt-2" id="printDirectBtn">Print Allocation Slip</button>
													<?php else: ?>
														<svg id="rocket-icon" class="my-2" viewBox="0 0 24 24" width="80" height="80" stroke="currentColor" stroke-width="1" fill="none" stroke-linecap="round" stroke-linejoin="round">
															<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
															<line x1="3" y1="6" x2="21" y2="6"></line>
														</svg>
														<h4 class="my-2"><?php echo $room_status; ?></h4>
														<button type="button" class="btn btn-success btn-block" id="checkRoomBtn">Check My Room Allocation</button>
													<?php endif; ?>
												</div>

												<!-- Room Check Modal for non-allocated users -->
												<?php if (!$has_room): ?>
												<div class="modal fade" id="roomCheckModal" tabindex="-1" role="dialog" aria-labelledby="roomCheckModalLabel" aria-hidden="true">
													<div class="modal-dialog modal-lg" role="document">
														<div class="modal-content">
															<div class="modal-header">
																<h5 class="modal-title" id="roomCheckModalLabel">Check Room Allocation</h5>
																<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
															</div>
															<div class="modal-body">
																<div id="roomCheckForm">
																	<h6 class="mb-3">Enter your details to check room allocation:</h6>
																	<form id="studentSearchForm">
																		<div class="form-group mb-3">
																			<label for="searchInput">Matric Number, Phone Number, or Parent's Number:</label>
																			<input type="text" class="form-control" id="searchInput" placeholder="Enter your matric number, phone number, or parent's number" required>
																		</div>
																		<div class="text-center">
																			<button type="submit" class="btn btn-primary">Search Room</button>
																		</div>
																	</form>
																</div>
																<div id="roomResult" style="display: none;">
																	<div id="roomDetails"></div>
																	<div class="text-center mt-3">
																		<button type="button" class="btn btn-success" id="printAllocationBtn">Print Allocation Slip</button>
																		<button type="button" class="btn btn-secondary" id="searchAgainBtn">Search Again</button>
																	</div>
																</div>
																<div id="noRoomFound" style="display: none;">
																	<div class="alert alert-warning text-center">
																		<h6>Student not found or no room allocated</h6>
																		<p>Please check your details and try again, or contact the admin.</p>
																		<div class="mt-3">
																			<button class="btn btn-success me-2" onclick="window.open('tel:+2348033300519')">Call Admin</button>
																			<button class="btn btn-primary" onclick="sendWhatsApp()">WhatsApp Admin</button>
																		</div>
																	</div>
																	<div class="text-center mt-3">
																		<button type="button" class="btn btn-secondary" id="searchAgainBtn2">Search Again</button>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<?php endif; ?>

												<!-- Room Details Modal for allocated users -->
												<?php if ($has_room): ?>
												<div class="modal fade" id="roomDetailsModal" tabindex="-1" role="dialog" aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
													<div class="modal-dialog modal-lg" role="document">
														<div class="modal-content">
															<div class="modal-header">
																<h5 class="modal-title" id="roomDetailsModalLabel">Your Room Allocation</h5>
																<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
															</div>
															<div class="modal-body">
																<div class="card">
																	<div class="card-header bg-success text-white">
																		<h5 class="mb-0">Room Allocation Details</h5>
																	</div>
																	<div class="card-body">
																	    
																		<div class="row">
																			<div class="col-md-6">
																				<p><strong>Student Name:</strong> <?php echo $room_allocation['student_name'] ?? 'N/A'; ?></p>
																				<p><strong>Matric Number:</strong> <?php echo $room_allocation['matric_no'] ?? 'N/A'; ?></p>
																				<p><strong>Department:</strong> <?php echo $room_allocation['department'] ?? 'N/A'; ?></p>
																				<p><strong>Level:</strong> <?php echo $room_allocation['level'] ?? 'N/A'; ?></p>
																			</div>
																			<div class="col-md-6">
																				<p><strong>Room/Bunk:</strong> <?php echo $room_allocation['room_bunk'] ?? 'N/A'; ?></p>
																				<p><strong>Phone Number:</strong> <?php echo $room_allocation['student_number'] ?? 'N/A'; ?></p>
																				<p><strong>Parent Number:</strong> <?php echo $room_allocation['parent_number'] ?? 'N/A'; ?></p>
																				<p><strong>Allocation Date:</strong> <?php echo $room_allocation['created_at'] ? date('M d, Y', strtotime($room_allocation['created_at'])) : 'N/A'; ?></p>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="text-center mt-3">
																	<button type="button" class="btn btn-success" id="printModalBtn">Print Allocation Slip</button>
																</div>
															</div>
														</div>
													</div>
												</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xl-6">
						<div class="row">
							<div class="col-xl-12">
								<div class="card">
									<div class="card-body">
										<div class="row ">
											<div class="col-xl-8 col-xxl-7 col-sm-7">
												<div class="update-profile d-flex">
													<?php if (!empty($students_info['userImage'])) : ?>
														<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image" alt="<?php echo $students_info['firstName'] ?? 'Guest'; ?> Image">
													<?php endif; ?>
													<div class="ms-4">
														<h3 class="mb-0">
															<?php echo $students_info['lastName'] ?? 'Guest'; ?>
															<?php echo $students_info['middleName'] ?? 'Guest'; ?>
															<?php echo $students_info['lastName'] ?? 'Guest'; ?></h3>
														<span class="text-primary d-block mb-xl-3 mb-1"><?php echo $students_info['regNo'] ?? 'Guest'; ?>
														</span>
													
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-xxl-5 col-sm-5 sm-mt-auto mt-3 text-sm-end">
												<a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
											</div>
										</div>
										<div class="row mt-4 align-items-center">
											<div class="col-xl-6 col-sm-6">
												
												<div>
												
												</div>
											</div>
											<div class="col-xl-6 col-sm-6">
												
												<div>
												
												</div>
											</div>
										</div>
										<div class="row mt-4 align-items-center">
											<h3 class="">Phone: <span><?php echo htmlspecialchars($students_info['contactNo'] ?? ''); ?></span></h3>
											<h3 class="">Email: <span><?php echo htmlspecialchars($students_info['email'] ?? ''); ?></span></h3>
											<h3 class="">Gender: <span><?php echo htmlspecialchars($students_info['gender'] ?? ''); ?></span></h3>
											<h3 class="">Department: <span><?php echo htmlspecialchars($students_info['department'] ?? ''); ?></span></h3>
											<h3 class="">Level: <span><?php echo htmlspecialchars($students_info['level'] ?? ''); ?></span></h3>
											<h3 class="">Parent Phone: <span><?php echo htmlspecialchars($students_info['parentPhone'] ?? ''); ?></span></h3>
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
		function sendWhatsApp() {
			const message = "I have an issue with hostel allocation, please I need your help.";
			const encodedMessage = encodeURIComponent(message);
			window.open(`https://wa.me/2348033300519?text=${encodedMessage}`, '_blank');
		}

		function paymentsendWhatsApp() {
			const message = "I have an issue with Payment, please I need your help.";
			const encodedMessage = encodeURIComponent(message);
			window.open(`https://wa.me/2348033300519?text=${encodedMessage}`, '_blank');
		}

		// PHP room allocation data for JavaScript
		const userRoomAllocation = <?php echo $has_room ? json_encode($room_allocation) : 'null'; ?>;

		<?php if ($has_room): ?>
		// For users with room allocation
		document.getElementById('viewRoomBtn').addEventListener('click', function() {
			$('#roomDetailsModal').modal('show');
		});

		document.getElementById('printDirectBtn').addEventListener('click', function() {
			if (userRoomAllocation) {
				printAllocationSlip(userRoomAllocation);
			}
		});

		document.getElementById('printModalBtn').addEventListener('click', function() {
			if (userRoomAllocation) {
				printAllocationSlip(userRoomAllocation);
			}
		});
		<?php else: ?>
		// For users without room allocation - keep original search functionality
		document.getElementById('checkRoomBtn').addEventListener('click', function() {
			$('#roomCheckModal').modal('show');
		});

		// Student search form submission
		document.getElementById('studentSearchForm').addEventListener('submit', function(event) {
			event.preventDefault();

			const searchInput = document.getElementById('searchInput').value.trim();
			if (!searchInput) {
				alert('Please enter your matric number, phone number, or parent\'s number');
				return;
			}

			// Show loading state
			const submitBtn = this.querySelector('button[type="submit"]');
			const originalText = submitBtn.textContent;
			submitBtn.textContent = 'Searching...';
			submitBtn.disabled = true;

			// Create form data
			const formData = new FormData();
			formData.append('search_query', searchInput);

			// Search for student room allocation
			fetch('php/search_room_allocation.php', {
					method: 'POST',
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					submitBtn.textContent = originalText;
					submitBtn.disabled = false;

					if (data.success && data.student) {
						// Show room details
						displayRoomDetails(data.student);
					} else {
						// Show no room found
						showNoRoomFound();
					}
				})
				.catch(error => {
					console.error('Error:', error);
					submitBtn.textContent = originalText;
					submitBtn.disabled = false;
					showNoRoomFound();
				});
		});

		function displayRoomDetails(student) {
			const roomDetails = document.getElementById('roomDetails');
			roomDetails.innerHTML = `
				<div class="card">
					<div class="card-header bg-success text-white">
						<h5 class="mb-0">Room Allocation Details</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
							
								<p><strong>Student Name:</strong> ${student.student_name || 'N/A'}</p>
								<p><strong>Matric Number:</strong> ${student.matric_no || 'N/A'}</p>
								<p><strong>Department:</strong> ${student.department || 'N/A'}</p>
								<p><strong>Level:</strong> ${student.level || 'N/A'}</p>
							</div>
							<div class="col-md-6">
								<p><strong>Room/Bunk:</strong> ${student.room_bunk || 'N/A'}</p>
								<p><strong>Phone Number:</strong> ${student.student_number || 'N/A'}</p>
								<p><strong>Parent Number:</strong> ${student.parent_number || 'N/A'}</p>
								<p><strong>Allocation Date:</strong> ${student.updated_at ? new Date(student.updated_at).toLocaleDateString() : 'N/A'}</p>
							</div>
						</div>
					</div>
				</div>
			`;

			// Store student data for printing
			window.currentStudentData = student;

			// Hide form and show result
			document.getElementById('roomCheckForm').style.display = 'none';
			document.getElementById('roomResult').style.display = 'block';
			document.getElementById('noRoomFound').style.display = 'none';
		}

		function showNoRoomFound() {
			document.getElementById('roomCheckForm').style.display = 'none';
			document.getElementById('roomResult').style.display = 'none';
			document.getElementById('noRoomFound').style.display = 'block';
		}

		function resetModal() {
			document.getElementById('roomCheckForm').style.display = 'block';
			document.getElementById('roomResult').style.display = 'none';
			document.getElementById('noRoomFound').style.display = 'none';
			document.getElementById('searchInput').value = '';
		}

		// Search again buttons
		document.getElementById('searchAgainBtn').addEventListener('click', resetModal);
		document.getElementById('searchAgainBtn2').addEventListener('click', resetModal);

		// Print allocation slip from search
		document.getElementById('printAllocationBtn').addEventListener('click', function() {
			if (window.currentStudentData) {
				printAllocationSlip(window.currentStudentData);
			}
		});

		// Reset modal when it's closed
		$('#roomCheckModal').on('hidden.bs.modal', function() {
			resetModal();
		});
		<?php endif; ?>
		
		<?php
$userImageBase64 = !empty($students_info['userImage']) 
    ? base64_encode($students_info['userImage']) 
    : null;
?>

		// Common print function for both allocated and searched students
		function printAllocationSlip(student) {
			const printWindow = window.open('', '_blank', 'width=800,height=800');
			const printContent = `
		
				<!DOCTYPE html>
				<html>
				<head>
					<title>Room Allocation Slip</title>
					<style>
						body { 
							font-family: Arial, sans-serif; 
							margin: 20px; 
							line-height: 1.6;
						}
						.header { 
							text-align: center; 
							margin-bottom: 30px; 
							border-bottom: 2px solid #28a745;
							padding-bottom: 20px;
						}
						
						.title { 
							color: #28a745; 
							margin: 10px 0; 
							font-size: 24px;
							font-weight: bold;
						}
						.subtitle {
							color: #666;
							font-size: 18px;
							margin: 5px 0;
						}
						.details { 
							margin: 30px 0; 
							background: #f8f9fa;
							padding: 20px;
							border-radius: 8px;
						}
						.detail-row { 
							margin: 15px 0; 
							display: flex; 
							border-bottom: 1px solid #dee2e6;
							padding-bottom: 8px;
						}
						.detail-label { 
							font-weight: bold; 
							width: 180px; 
							color: #495057;
						}
						.detail-value {
							flex: 1;
							color: #212529;
						}
						.room-highlight {
							background: #28a745;
							color: white;
							padding: 15px;
							border-radius: 8px;
							text-align: center;
							margin: 20px 0;
							font-size: 20px;
							font-weight: bold;
						}
						.footer { 
							margin-top: 40px; 
							text-align: center; 
							font-size: 12px; 
							color: #666; 
							border-top: 1px solid #dee2e6;
							padding-top: 20px;
						}
						.qr-placeholder {
							width: 100px;
							height: 100px;
							border: 2px dashed #ccc;
							margin: 20px auto;
							display: flex;
							align-items: center;
							justify-content: center;
							font-size: 12px;
							color: #666;
						}
						@media print {
							body { margin: 0; }
							.no-print { display: none; }
						}
					</style>
				</head>
				<body>
					<div class="header">
						<div class="logo">
						<center>
						<img class="logo-auth" src="images/paetoa.png" alt="Logo"  style="width:120px; height:140px;">
							</center>
						</div>
						<h1 class="title">PA-ETOS HOSTEL ACCOMMODATION</h1>
						<h2 class="subtitle">Room Allocation Slip</h2>
					
					</div>
					
					<div class="room-highlight">
						ALLOCATED ROOM: ${student.room_bunk || 'N/A'}
					</div>
						
					
		<div style="border:1px solid #ccc; padding:15px; border-radius:10px; max-width:600px; margin:auto; font-family:Arial, sans-serif; background:#fff;">
    
    <!-- Header -->
   

    <!-- Body -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
        
        <!-- Student Details -->
        <div style="flex:1;">
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Student Name:</span>
                <span style="color:#444;">${student.student_name || 'N/A'}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Matric Number:</span>
                <span style="color:#444;">${student.matric_no || 'N/A'}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Department:</span>
                <span style="color:#444;">${student.department || 'N/A'}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Level:</span>
                <span style="color:#444;">${student.level || 'N/A'}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Room/Bunk:</span>
                <span style="font-weight:bold; color:#006600;">${student.room_bunk || 'N/A'}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Student Phone:</span>
                <span style="color:#444;">0${student.student_number || 'N/A'}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Parent Phone:</span>
                <span style="color:#444;">0${student.parent_number || 'N/A'}</span>
            </div>
            <div style="margin-bottom:8px;">
                <span style="font-weight:bold; min-width:140px; display:inline-block; color:#333;">Allocation Date:</span>
                <span style="color:#444;">
                    ${student.updated_at ? new Date(student.updated_at).toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric'
                    }) : 'N/A'}
                </span>
            </div>
        </div>

        <!-- Passport -->
        <div style="margin-left:20px;">
            <?php if (!empty($students_info['userImage'])) : ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" 
                     style="width:140px; height:160px; border:1px solid #aaa; border-radius:8px; object-fit:cover;"/>
            <?php endif; ?>
        </div>
    </div>
</div>

					
					<div style="margin: 30px 0; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
						<h4 style="margin: 0 0 10px 0; color: #856404;">Important Notes:</h4>
						<ul style="margin: 0; padding-left: 20px; color: #856404;">
							<li>This slip must be presented during hostel check-in</li>
							<li>Keep this document safe for the entire academic session</li>
							<li>Report any discrepancies immediately to the hostel management</li>
						<li>Room keys will be issued upon presentation of this slip </li> 
						</ul>
					</div>
					
					<div class="footer">
						<p><strong>Pa-etos Hostel Accommodation</strong></p>
					
						<p style="margin-top: 15px; font-style: italic;">This is an official room allocation slip generated on ${new Date().toLocaleString()}</p>
				
					</div>
					
				<div class="no-print" style="text-align: center; margin-top: 30px; padding: 20px; border-top: 2px solid #28a745;">
						<button onclick="window.print()" style="padding: 12px 30px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 16px; margin-right: 10px; cursor: pointer;">🖨️ Print Slip</button>
						<button onclick="window.close()" style="padding: 12px 30px; background: #6c757d; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">✖️ Close</button>
					</div> 
				</body>
				</html>
			`;

			printWindow.document.write(printContent);
			printWindow.document.close();
		}
	</script>

	<!--**********************************
		Footer start
	***********************************-->
	<div class="footer">
		<div class="copyright">
			<p>All rights reserved &copy; <?php echo date('Y'); ?> <a href="#" target="_blank"></a></p>
		</div>
	</div>

	</div>

	<!--**********************************
	Scripts
	***********************************-->
	<script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js"></script>

	<script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
	<script src="js/demo.js"></script>

	<script>
		jQuery(document).ready(function() {
			setTimeout(function() {
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

		// Function to fetch the button status
		function fetchButtonStatus() {
			$.ajax({
				url: 'php/button_status.php',
				type: 'GET',
				dataType: 'json',
				success: function(response) {
					console.log('Button status fetched:', response);
					$('#dynamic-button').text(response.button_text);
					$('#dynamic-button').attr('href', response.button_link);
				},
				error: function(xhr, status, error) {
					console.error('Error fetching button status:', error);
				}
			});
		}

		// Function to start periodic checking
		function startPeriodicCheck() {
			fetchButtonStatus();
			setInterval(fetchButtonStatus, 10000);
		}

		// Start periodic check when document is ready
		$(document).ready(function() {
			startPeriodicCheck();
		});
	</script>
	<script src="vendor/toastr/js/toastr.min.js"></script>
	<script src="js/paetos.js"></script>
	<script src="js/pt-nav.js"></script>
</body>

</html>