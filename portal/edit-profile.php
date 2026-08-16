<?php
session_start();

// Check if user is logged in and session is active
if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
	header("Location: login.html");
	exit();
}

// Extend session timeout upon activity
$_SESSION['timeout'] = time() + 1800;

include 'php/config.php';

$user_id = (int)$_SESSION['user_id'];

$students_info = array();
$stmt = $conn->prepare("SELECT id, regNo, firstName, middleName, lastName, gender, contactNo, email, userImage FROM userregistration WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
	$students_info = $result->fetch_assoc();
}
$stmt->close();
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
			<a href="dashboard.php" class="brand-logo">
				<img class="logo-abbr" src="images/paetoa.svg" width="134" height="48" alt="Pa-etos Hostel Accommodation">
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
								<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Edit Profile
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
									<?php if (!empty($students_info['userImage'])) : ?>
										<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image" alt="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image">
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
								<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image" alt="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image">
							<?php endif; ?>
							<div class="d-flex align-items-center sidebar-info">
								<div>
									<span class="font-w400 d-block"><?php echo htmlspecialchars(($students_info['firstName'] ?? '') . ' ' . ($students_info['lastName'] ?? '')); ?></span>
									<small class="text-end font-w400"><?php echo htmlspecialchars($students_info['email'] ?? ''); ?></small>
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
					<li><a href="check-room.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-046-home"></i>
							<span class="nav-text">Check Status</span>
						</a>
					</li>

				</ul>

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

			</div>
		</div>
		<!--**********************************
			Sidebar end
		***********************************-->

		<div class="content-body" id="pt-content">
			<!-- row -->
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-3 col-lg-4">
						<div class="card card-bx author-profile m-b30">
							<div class="card-body">
								<div class="p-5">
									<div class="author-profile">
										<div class="author-media">
											<?php if (!empty($students_info['userImage'])) : ?>
												<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>" title="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image" alt="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image">
											<?php endif; ?>
											<div class="upload-link" title="" data-bs-toggle="tooltip" data-placement="right" data-original-title="update">
												<input type="file" class="update-flie" id="passportUpload" accept="image/*">
												<i class="fa fa-camera"></i>
											</div>
										</div>
										<div class="author-info">
											<h6 class="title"><?php echo htmlspecialchars(($students_info['firstName'] ?? '') . ' ' . ($students_info['lastName'] ?? '')); ?></h6>
											<span><?php echo htmlspecialchars($students_info['regNo'] ?? ''); ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-9 col-lg-8">
						<div class="card card-bx m-b30">
							<div class="card-header">
								<h4 class="card-title">Edit Profile Details</h4>
							</div>
							<form class="profile-form" id="editProfileForm" data-ajax>
								<div class="card-body">
									<div class="row">
										<div class="col-sm-6 m-b30">
											<label class="form-label">First Name</label>
											<input type="text" class="form-control" name="firstName" value="<?php echo htmlspecialchars($students_info['firstName'] ?? ''); ?>" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Middle Name</label>
											<input type="text" class="form-control" name="middleName" value="<?php echo htmlspecialchars($students_info['middleName'] ?? ''); ?>">
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Last Name</label>
											<input type="text" class="form-control" name="lastName" value="<?php echo htmlspecialchars($students_info['lastName'] ?? ''); ?>" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Gender</label>
											<select class="form-control" name="gender">
												<option value="Male" <?php echo ($students_info['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
												<option value="Female" <?php echo ($students_info['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
											</select>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Reg Number</label>
											<input type="text" class="form-control" name="regNo" value="<?php echo htmlspecialchars($students_info['regNo'] ?? ''); ?>" readonly>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Phone Number</label>
											<input type="text" class="form-control" name="contactNo" value="<?php echo htmlspecialchars($students_info['contactNo'] ?? ''); ?>" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Email Address</label>
											<input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($students_info['email'] ?? ''); ?>" required>
										</div>
									</div>
								</div>
								<div class="card-footer">
									<button class="btn btn-primary" type="submit" id="saveProfileBtn">Save Changes</button>
								</div>
							</form>
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
				<p>All rights reserved &copy; <?php echo date('Y'); ?> <a href="#" target="_blank">BMXCODERS</a></p>
			</div>
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

	<script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
	<script src="js/demo.js"></script>
	<!-- <script src="js/styleSwitcher.js"></script> -->

	<script>
		var editForm = document.getElementById('editProfileForm');
		var saveBtn = document.getElementById('saveProfileBtn');

		if (editForm) {
			editForm.addEventListener('submit', async function(event) {
				event.preventDefault();
				if (window.PT) window.PT.btnLoading(saveBtn, true);

				var userData = {
					regNo: editForm.querySelector('[name="regNo"]').value,
					firstName: editForm.querySelector('[name="firstName"]').value,
					middleName: editForm.querySelector('[name="middleName"]').value,
					lastName: editForm.querySelector('[name="lastName"]').value,
					gender: editForm.querySelector('[name="gender"]').value,
					contactNo: editForm.querySelector('[name="contactNo"]').value,
					email: editForm.querySelector('[name="email"]').value
				};

				try {
					var response = await fetch('php/update_user.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'userId=<?php echo $user_id; ?>&userData[regNo]=' + encodeURIComponent(userData.regNo) +
							'&userData[firstName]=' + encodeURIComponent(userData.firstName) +
							'&userData[middleName]=' + encodeURIComponent(userData.middleName) +
							'&userData[lastName]=' + encodeURIComponent(userData.lastName) +
							'&userData[gender]=' + encodeURIComponent(userData.gender) +
							'&userData[contactNo]=' + encodeURIComponent(userData.contactNo) +
							'&userData[email]=' + encodeURIComponent(userData.email)
					});
					var result = await response.json();

					if (result.status === 'success') {
						if (window.PT) window.PT.success('Profile updated successfully.', 'Profile Updated');
					} else {
						if (window.PT) window.PT.error(result.message || 'Failed to update profile.', 'Update Failed');
					}
				} catch (error) {
					if (window.PT) window.PT.error('Error: ' + error.message, 'Update Failed');
				} finally {
					if (window.PT) window.PT.btnLoading(saveBtn, false);
				}
			});
		}

		var passportInput = document.getElementById('passportUpload');
		if (passportInput) {
			passportInput.addEventListener('change', async function() {
				var file = this.files[0];
				if (!file) return;

				var uploadLink = this.closest('.upload-link');
				if (uploadLink) uploadLink.classList.add('pt-loading');

				var formData = new FormData();
				formData.append('userImage', file);

				try {
					var response = await fetch('php/update_passport.php', {
						method: 'POST',
						body: formData
					});
					var text = await response.text();

					if (text.trim().toLowerCase().indexOf('success') !== -1) {
						if (window.PT) window.PT.success('Passport photo updated.', 'Photo Updated');
						setTimeout(function() { window.location.reload(); }, 1200);
					} else if (text.trim().toLowerCase().indexOf('not logged in') !== -1) {
						if (window.PT) window.PT.error('Session expired. Please log in again.', 'Session Expired');
					} else {
						if (window.PT) window.PT.error(text || 'Failed to upload photo.', 'Upload Failed');
					}
				} catch (error) {
					if (window.PT) window.PT.error('Error: ' + error.message, 'Upload Failed');
				} finally {
					if (uploadLink) uploadLink.classList.remove('pt-loading');
				}
			});
		}
	</script>

	<script src="vendor/toastr/js/toastr.min.js"></script>
	<script src="js/paetos.js"></script>
	<script src="js/pt-nav.js"></script>
</body>

</html>
