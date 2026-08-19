<?php
// Sidebar partial - requires: pt_can(), pt_role_label(), $admin array (from fetch_admin_info.php)
// Usage: include 'includes/sidebar.php';
?>
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
								class="font-w400 d-block"><?php echo htmlspecialchars($admin['username'] ?? ''); ?></span>
							<small
								class="text-end font-w400"><?php echo htmlspecialchars($admin['email'] ?? ''); ?> &middot; <?php echo htmlspecialchars(pt_role_label($_SESSION['role'] ?? '')); ?></small>
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
			<!-- <?php if (pt_can('manage_hostel')): ?>
				<li><a class="" href="manage-hostel.php" aria-expanded="false">
						<i class="fas fa-building"></i>
						<span class="nav-text"> Manage Hostel</span>
					</a>

				</li>
			<?php endif; ?> -->
			<?php if (pt_can('assign_room')): ?>
				<li><a class="" href="assigned_room.php" aria-expanded="false">
						<i class="fas fa-bed"></i>
						<span class="nav-text"> Assign Room</span>
					</a>

				</li>
			<?php endif; ?>
			<?php if (pt_can('confirm_payment')): ?>
				<li><a class="" href="confirm-payments.php" aria-expanded="false">
						<i class="fas fa-money-check-alt"></i>
						<span class="nav-text"> Confirm Payment</span>
					</a>

				</li>
			<?php endif; ?>
			<?php if (pt_can('list_student')): ?>
				<li><a class="" href="list-student.php" aria-expanded="false">
						<i class="fas fa-users"></i>
						<span class="nav-text"> List Student</span>
					</a>

				</li>
			<?php endif; ?>
			<?php if (pt_can('manage_session')): ?>
				<li><a class="" href="manage-session.php" aria-expanded="false">
						<i class="fas fa-calendar-alt"></i>
						<span class="nav-text"> Manage Session</span>
					</a>

				</li>
			<?php endif; ?>
			<?php if (pt_can('reset_password')): ?>
				<li><a class="" href="password-reset.php" aria-expanded="false">
						<i class="fas fa-key"></i>
						<span class="nav-text"> Reset Password</span>
					</a>

				</li>
			<?php endif; ?>

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