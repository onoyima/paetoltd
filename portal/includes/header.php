<!--**********************************
	Main wrapper start
***********************************-->
<div id="main-wrapper">

	<!-- Preloader with Paetos logo -->
	<div id="preloader">
		<div class="pt-pre-brand">
			<img class="pt-pre-logo" src="images/paetoa.png" alt="Pa-etos Logo" width="60" height="60">
			<div class="pt-spinner"></div>
		</div>
	</div>

	<!--**********************************
		Nav header start
***********************************-->
	<div class="nav-header">
		<a href="admin-dashboard.php" class="brand-logo">




			<img class="logo-abbr" src="images/paetoa.svg" width="134" height="48">

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
							<span class="text-danger text-bold"> <?php echo htmlspecialchars($pageHeader ?? 'Dashboard'); ?></span>
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