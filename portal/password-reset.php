<?php
require_once __DIR__ . '/php/rbac.php';

// Admin session + reset_password permission
pt_require_page('reset_password');

include 'php/fetch_admin_info.php';

$pageTitle = 'Reset Password';
$pageHeader = 'Dashboard';
?>
<?php include 'includes/head.php'; ?>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Content body start -->
<div class="content-body" id="pt-content">
	<div class="container-fluid">
		<!-- row -->
		<div class="row">
			<div class="col-xl-12 col-lg-12">
				<div class="card card-bx m-b30">
					<div class="card-header">
						<h4 class="card-title">RESET PASSWORD</h4>
					</div>
					<div id="messageBox" class="d-none">
						<!-- Error or success messages will be displayed here -->
					</div>

					<form class="password-reset-form" onsubmit="event.preventDefault(); resetPassword();" enctype="multipart/form-data">
						<div class="card-body">
							<div class="row">
								<div class="col-12">
									<label class="form-label" for="email">Email</label>
									<input type="email" id="email" name="email" class="form-control" required>
								</div>
								<div class="col-12 mt-3">
									<label class="form-label" for="type">Reset Type</label>
									<select id="type" name="type" class="form-control" required>
										<option value="admin">Admin</option>
										<option value="user">User</option>
									</select>
								</div>
							</div>
						</div>
						<div class="card-footer">
							<button class="btn btn-primary" type="submit">RESET PASSWORD</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	function resetPassword() {
		var form = document.querySelector('.password-reset-form');
		var btn = form.querySelector('button[type="submit"]');
		var formData = new FormData(form);
		PT.btnLoading(btn, true);

		fetch('php/reset.php', {
			method: 'POST',
			body: formData
		})
			.then(response => response.json())
			.then(data => {
				PT.btnLoading(btn, false);
				if (data.success) {
					PT.success(data.success, 'Password Reset');
					form.reset();
				} else {
					PT.error(data.error || 'Unable to reset password.', 'Password Reset');
				}
			})
			.catch(error => {
				PT.btnLoading(btn, false);
				PT.error('Network error. Please try again.', 'Password Reset');
				console.error('Error:', error);
			});
	}

</script>

</div>

<?php include 'includes/footer.php'; ?>
