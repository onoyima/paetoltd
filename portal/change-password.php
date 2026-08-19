<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout']) || $_SESSION['timeout'] < time()) {
	header("Location: login.html");
	exit();
}

$_SESSION['timeout'] = time() + 1800;

include 'php/fetch_user_details.php';
require_once __DIR__ . '/php/academic_helper.php';

$students_info = fetch_user_details();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Change Password - Paetos Portal</title>
	<link rel="shortcut icon" type="image/png" href="images/paetoa.png">
	<link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link class="main-css" href="css/style.css" rel="stylesheet">
	<link href="vendor/toastr/css/toastr.min.css" rel="stylesheet">
	<link href="css/paetos.css" rel="stylesheet">
	<script>(function(){try{var t=localStorage.getItem('pt_theme');if(t!=='dark'&&t!=='light'){t=(typeof getCookie==='function'&&getCookie('version')==='dark')?'dark':'light'}document.documentElement.setAttribute('data-pt-theme',t)}catch(e){document.documentElement.setAttribute('data-pt-theme','light')}})();</script>
</head>

<body>
	<div id="preloader">
		<div class="pt-pre-brand">
			<img class="pt-pre-logo" src="images/paetoa.png" alt="Pa-etos Logo" width="70" height="70">
			<div class="pt-spinner"></div>
		</div>
	</div>

	<div id="main-wrapper">
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

		<div class="header">
			<div class="header-content">
				<nav class="navbar navbar-expand">
					<div class="collapse navbar-collapse justify-content-between">
						<div class="header-left">
							<div class="dashboard_bar">
								<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Change Password
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
									<?php if (!empty($students_info['userImage'])): ?>
										<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>"
											title="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image"
											alt="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image">
									<?php endif; ?>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="profile.php" class="dropdown-item ai-icon">
										<svg id="icon-user2" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
											<circle cx="12" cy="7" r="4"></circle>
										</svg>
										<span class="ms-2">Profile</span>
									</a>
									<a href="php/logout.php" class="dropdown-item ai-icon">
										<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
											<polyline points="16 17 21 12 16 7"></polyline>
											<line x1="21" y1="12" x2="9" y2="12"></line>
										</svg>
										<span class="ms-2">Logout</span>
									</a>
								</div>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</div>

		<div class="dlabnav">
			<div class="dlabnav-scroll">
				<div class="dropdown header-profile2 ">
					<a class="nav-link " href="#;" role="button" data-bs-toggle="dropdown">
						<div class="header-info2 d-flex align-items-center">
							<?php if (!empty($students_info['userImage'])): ?>
								<img src="data:image/jpeg;base64,<?php echo base64_encode($students_info['userImage']); ?>"
									title="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image"
									alt="<?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?> Image">
							<?php endif; ?>
							<div class="d-flex align-items-center sidebar-info">
								<div>
									<span class="font-w400 d-block"><?php echo htmlspecialchars($students_info['firstName'] ?? 'Guest'); ?></span>
									<small class="text-end font-w400"><?php echo htmlspecialchars($students_info['email'] ?? 'Guest'); ?></small>
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
							<span class="ms-2">Profile</span>
						</a>
						<a href="php/logout.php" class="dropdown-item ai-icon">
							<svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
								<polyline points="16 17 21 12 16 7"></polyline>
								<line x1="21" y1="12" x2="9" y2="12"></line>
							</svg>
							<span class="ms-2">Logout</span>
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

		<div class="content-body" id="pt-content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-8">
						<div class="card card-bx m-b30">
							<div class="card-header">
								<h4 class="card-title">CHANGE PASSWORD</h4>
							</div>
							<div class="card-body">
								<form id="changePasswordForm">
									<div class="row">
										<div class="col-sm-6 m-b30">
											<label class="form-label">Current Password <span class="text-danger">*</span></label>
											<input type="password" class="form-control" name="currentPassword" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">New Password <span class="text-danger">*</span></label>
											<input type="password" class="form-control" name="newPassword" minlength="6" required>
										</div>
										<div class="col-sm-6 m-b30">
											<label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
											<input type="password" class="form-control" name="confirmPassword" minlength="6" required>
										</div>
									</div>
								</form>
							</div>
							<div class="card-footer">
								<button class="btn btn-primary" type="button" id="changePasswordBtn">UPDATE PASSWORD</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="footer">
			<div class="copyright">
				<p>All rights reserved &copy; <?php echo date('Y'); ?> <a href="#" target="_blank">BMXCODERS</a></p>
			</div>
		</div>
	</div>

	<script src="vendor/global/global.min.js"></script>
	<script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
	<script src="js/demo.js"></script>

	<script>
		document.getElementById('changePasswordBtn').addEventListener('click', function() {
			var form = document.getElementById('changePasswordForm');
			var btn = this;
			var formData = {
				currentPassword: form.currentPassword.value.trim(),
				newPassword: form.newPassword.value.trim(),
				confirmPassword: form.confirmPassword.value.trim()
			};

			if (!formData.currentPassword || !formData.newPassword || !formData.confirmPassword) {
				if (window.PT) PT.error('All fields are required.', 'Change Password');
				return;
			}
			if (formData.newPassword !== formData.confirmPassword) {
				if (window.PT) PT.error('New passwords do not match.', 'Change Password');
				return;
			}
			if (formData.newPassword.length < 6) {
				if (window.PT) PT.error('New password must be at least 6 characters.', 'Change Password');
				return;
			}

			if (window.PT) PT.btnLoading(btn, true);

			fetch('php/change_password.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(formData)
			})
			.then(function(res) { return res.json(); })
			.then(function(data) {
				if (window.PT) PT.btnLoading(btn, false);
				if (data.status === 'success') {
					if (window.PT) PT.success(data.message, 'Change Password');
					form.reset();
				} else {
					if (window.PT) PT.error(data.message, 'Change Password');
				}
			})
			.catch(function() {
				if (window.PT) PT.btnLoading(btn, false);
				if (window.PT) PT.error('Network error. Please try again.', 'Change Password');
			});
		});
	</script>

	<script src="vendor/toastr/js/toastr.min.js"></script>
	<script src="js/paetos.js"></script>
	<script src="js/pt-nav.js"></script>
</body>

</html>
