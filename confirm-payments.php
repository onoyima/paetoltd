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

        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
                <div class="d-flex align-items-center mb-4 flex-wrap">
                    <h3 class="me-auto">Payment Lists</h3>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <?php
                            include 'php/fetch_payment.php';
                            if (!empty($userPayments)) :
                            ?>
                                <table class="table display mb-4 dataTablesCard job-table table-responsive-xl card-table" id="example5">
                                    <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Email</th>
                                            <th>Phone Number</th>
                                            <th>Payers Name</th>
                                            <th>Bank Name</th>
                                            <th>Assign</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $serialNumber = 1; foreach ($userPayments as $row) : ?>
                                            <tr>
                                                <td><?= $serialNumber ?></td>
                                                <td><?= htmlspecialchars($row["firstName"]) ?></td>
                                                <td><?= htmlspecialchars($row["lastName"]) ?></td>
                                                <td><?= htmlspecialchars($row["email"]) ?></td>
                                                <td><?= htmlspecialchars($row["contactNo"]) ?></td>
                                                <td><?= htmlspecialchars($row["payers_name"]) ?></td>
                                                <td><?= htmlspecialchars($row["bankName"]) ?></td>
                                                <td>
                                                    <button class="btn btn-success view-payment" data-bs-toggle="modal" data-bs-target="#viewModal" data-userid="<?= $row['id'] ?>">Assign</button>
                                                </td>
                                                <td>
                                                    <button class="btn btn-danger">Reject</button>
                                                </td>
                                            </tr>
                                            <?php $serialNumber++; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else : ?>
                                <p>No user payments found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content body end -->
        <!-- Footer start -->
        <div class="footer">
			<div class="copyright">
					<p>All right reserved <a href="#" target="_blank"> Pa-etos Ltd </a> 2024</p>
			</div>
		</div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Payment Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be dynamically loaded here via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                </div>
            </div>
        </div>
    </div>
    <!-- Required JS scripts -->
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="js/dlabnav-init.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewButtons = document.querySelectorAll('.view-payment');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-userid');
                    fetch(`php/fetch_user_d.php?id=${userId}`)
                        .then(response => response.json())
                        .then(data => {
                            const modalBody = document.querySelector('#viewModal .modal-body');
                            modalBody.innerHTML = `
                                <ul class="list-group mt-3">
                                    <li class="list-group-item"><strong>First Name:</strong> ${data.firstName}</li>
                                    <li class="list-group-item"><strong>Last Name:</strong> ${data.lastName}</li>
                                    <li class="list-group-item"><strong>Email:</strong> ${data.email}</li>
                                    <li class="list-group-item"><strong>Contact Number:</strong> ${data.contactNo}</li>
                                    <li class="list-group-item"><strong>Payer's Name:</strong> ${data.payers_name}</li>
                                    <li class="list-group-item"><strong>Bank Name:</strong> ${data.bankName}</li>
                                    <li class="list-group-item"><strong>Payment Date:</strong> ${data.paymentDate}</li>
                                </ul>
                                <div class="mt-3">
                                    <img src="data:image/jpeg;base64,${data.paymentInfo}" width="100%" height="auto" class="img-fluid">
                                </div>
                                <form id="reservationForm"> 
                                    <div class="mb-4">
                                        <label class="form-label required">Select Room Category</label>
                                        <select id="roomCategory" class="default-select wide form-control solid">
                                            <option>Select room category</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label required">Select Room Number</label>
                                        <select id="roomNumber" class="default-select wide form-control solid">
                                            <option>Select room number</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label required">Bed Space</label>
                                        <input type="text" id="bedSpace" class="form-control solid" placeholder="Enter Bed Space">
                                        <input type="hidden" id="userId" value="${userId}">
                                    </div>
                                    <button type="submit" id="submit-button" class="btn btn-primary">Submit</button>
                                </form>
                            `;
                            fetchRoomCategories();
                            attachFormSubmitListener();
                        })
                        .catch(error => {
                            console.error('Error fetching user details:', error);
                            const modalBody = document.querySelector('#viewModal .modal-body');
                            modalBody.innerHTML = `<p>${error.message}</p>`;
                        });
                });
            });
        });

        function fetchRoomCategories() {
            fetch('fetch_room_categories.php')
                .then(response => response.json())
                .then(categories => {
                    const roomCategorySelect = document.getElementById('roomCategory');
                    roomCategorySelect.innerHTML = '<option selected>Choose...</option>';
                    categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.room_type;
                        roomCategorySelect.appendChild(option);
                    });
                    roomCategorySelect.addEventListener('change', function() {
                        fetchRoomsByCategory(this.value);
                    });
                })
                .catch(error => {
                    console.error('Error fetching room categories:', error);
                });
        }

        function fetchRoomsByCategory(categoryId) {
            fetch(`fetch_rooms.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(rooms => {
                    const roomNumberSelect = document.getElementById('roomNumber');
                    roomNumberSelect.innerHTML = '<option selected>Choose...</option>';
                    rooms.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.id;
                        option.textContent = `${room.room_number} - (Available space: ${room.available_space})`;
                        roomNumberSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching rooms:', error);
                });
        }

        function attachFormSubmitListener() {
    const form = document.querySelector('#reservationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('userId').value;
            const roomCategory = document.getElementById('roomCategory').value;
            const roomNumber = document.getElementById('roomNumber').value;
            const bedSpace = document.getElementById('bedSpace').value;

            fetch('assign_room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ userId, roomCategory, roomNumber, bedSpace })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Room assignment successful!');
                    location.reload(); // Optionally reload the page or update UI
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error assigning room:', error);
            });
        });
    } else {
        console.error('Form not found or not accessible.');
    }
}

    </script>
</body>
</html>
