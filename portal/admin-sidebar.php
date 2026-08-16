<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
require_once __DIR__ . '/php/rbac.php';

// Valid admin session required (any role)
pt_require_page();

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
	<link href="vendor/toastr/css/toastr.min.css" rel="stylesheet">
	<link href="css/paetos.css" rel="stylesheet">
	<script>(function () { try { var t = localStorage.getItem('pt_theme'); if (t !== 'dark' && t !== 'light') { t = (typeof getCookie === 'function' && getCookie('version') === 'dark') ? 'dark' : 'light' } document.documentElement.setAttribute('data-pt-theme', t) } catch (e) { document.documentElement.setAttribute('data-pt-theme', 'light') } })();</script>

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
				<img class="logo-abbr" src="images/paetoa.svg" width="134" height="48"
					alt="Pa-etos Hostel Accommodation">
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
								<span class="text-danger text-bold"> Dashboard</span>
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
											<svg id="icon-user2" xmlns="http://www.w3.org/2000/svg" class="text-primary"
												width="18" height="18" viewBox="0 0 24 24" fill="none"
												stroke="currentColor" stroke-width="2" stroke-linecap="round"
												stroke-linejoin="round">
												<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
												<circle cx="12" cy="7" r="4"></circle>
											</svg>
											<span class="ms-2">Profile </span>
										</a>

										<a href="php/admin_logout.php" class="dropdown-item ai-icon">
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
					<a class="nav-link " href="admin-dashboard.php" role="button" data-bs-toggle="dropdown">
						<div class="header-info2 d-flex align-items-center">
							<img src="images/veritas.png" alt="">

							<div class="d-flex align-items-center sidebar-info">
								<div>
									<span
										class="font-w400 d-block"><?php echo htmlspecialchars($admin['username']); ?></span>
									<small class="text-end font-w400"><?php echo htmlspecialchars($admin['email']); ?>
										&middot;
										<?php echo htmlspecialchars(pt_role_label($_SESSION['role'])); ?></small>
								</div>
								<i class="fas fa-chevron-down"></i>
							</div>

						</div>
					</a>

				</div>
				<ul class="metismenu" id="menu">
					<li><a class="" href="admin-dashboard.php" data-pt-nav aria-expanded="false">
							<i class="flaticon-025-dashboard"></i>
							<span class="nav-text">Dashboard</span>
						</a>


					</li>
					<?php if (pt_can('manage_hostel')): ?>
						<li><a class="" href="manage-hostel.php" data-pt-nav aria-expanded="false">
								<i class="flaticon-046-home"></i>
								<span class="nav-text"> Manage Hostel</span>
							</a>

						</li>
					<?php endif; ?>
					<?php if (pt_can('assign_room')): ?>
						<li><a class="" href="assigned_room.php" data-pt-nav aria-expanded="false">
								<i class="flaticon-046-home"></i>
								<span class="nav-text"> Assign Room</span>
							</a>

						</li>
					<?php endif; ?>
					<?php if (pt_can('confirm_payment')): ?>
						<li><a class="" href="confirm-payments.php" data-pt-nav aria-expanded="false">
								<i class="flaticon-046-home"></i>
								<span class="nav-text"> Confirm Payment</span>
							</a>

						</li>
					<?php endif; ?>
					<?php if (pt_can('list_student')): ?>
						<li><a class="" href="list-student.php" data-pt-nav aria-expanded="false">
								<i class="flaticon-046-home"></i>
								<span class="nav-text"> List Student</span>
							</a>

						</li>
					<?php endif; ?>
					<?php if (pt_can('view_hostel')): ?>
						<li><a class="" href="manage-hostel.php#view_room" data-pt-nav aria-expanded="false">
								<i class="flaticon-046-home"></i>
								<span class="nav-text"> View Hostel</span>
							</a>

						</li>
					<?php endif; ?>
					<?php if (pt_can('manage_session')): ?>
						<li><a class="" href="manage-session.php" data-pt-nav aria-expanded="false">
								<i class="fas fa-calendar-alt"></i>
								<span class="nav-text"> Manage Session</span>
							</a>

						</li>
					<?php endif; ?>
					<?php if (pt_can('reset_password')): ?>
						<li><a class="" href="password-reset.php" data-pt-nav aria-expanded="false">
								<i class="flaticon-046-home"></i>
								<span class="nav-text"> Reset Password</span>
							</a>

						</li>
					<?php endif; ?>

					<!-- <li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
							<i class="flaticon-093-waving"></i>
							<span class="nav-text">Complain</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="#">Payment Issue</a></li>
							<li><a href="#">Repair Issues</a></li>

						</ul>
					</li> -->

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
					<a href="php/admin_logout.php" class="pt-sidebar-item pt-sidebar-logout" aria-label="Logout">
						<i class="fas fa-sign-out-alt"></i>
						<span class="nav-text">Logout</span>
					</a>
				</div>

			</div>
		</div>
		<!--**********************************
			Sidebar end
		***********************************-->
		<?php
		ob_end_flush();
		?>