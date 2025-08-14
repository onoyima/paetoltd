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



		<!--**************************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
				<div class="row page-titles">
					
                </div>
                <!-- row -->
                <div class="row">
                   
					<div class="col-xl-6 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Add Room</h4>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
								<form id="addRoomForm">
        <div class="row">
            <div class="mb-3 col-md-6">
                <label class="form-label">Room Number</label>
                <input type="text" class="form-control" id="roomNumber" placeholder="Room Number">
            </div>
            <div class="mb-3 col-md-6">
                <label class="form-label">Room Type</label>
                <select id="roomType" class="default-select form-control wide">
                    <option selected>Choose...</option>
                </select>
            </div>
            <div class="mb-3 col-md-6">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control" id="capacity" placeholder="Capacity">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Room</button>
    </form>

                                </div>
								<hr>
								
                            </div>
                        </div>
					</div>
					<div class="col-xl-6 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Add Room Category</h4>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
								<form id="addCategoryForm">
        <div class="row">
            <div class="mb-3 col-md-6">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" id="categoryName" placeholder="Name" required>
            </div>
            <div class="mb-3 col-md-6">
                <label class="form-label">Rate</label>
                <input type="text" class="form-control" id="categoryRate" placeholder="300000" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Room Category</button>
    </form>
                                </div>
                            </div>
							<HR>
                        </div>
					</div>
					<div class="col-xl-7 col-lg-12">
                        <div class="card" id="view_room">
                            <div class="card-header">
                                <h4 class="card-title">View Room</h4>
                            </div>
                            <div class="card-body">
    <div class="basic-form">
        <table class="table display mb-4 dataTablesCard  card-table" id="roomTable" >
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Room No.</th>
                    <th> Category</th>
                    <th> Capacity</th>
                    <th>Available</th>
					<th>Actions</th>
                </tr>
            </thead>
            <tbody>
               
            </tbody>
        </table>
    </div>
</div>

                        </div>
					</div>
					<div class="col-xl-5 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"> Room Category</h4>
                            </div>
							<div class="card-body">
    <div class="basic-form">
        <table class="table display mb-4 dataTablesCard  card-table" id="roomDetailsTable">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th> Category</th>
                    <th> Rate</th>
					<th>Actions</th>
                    
                </tr>
            </thead>
            <tbody>
               
            </tbody>
        </table>
    </div>
</div>
                        </div>
					</div>
					


					
                </div>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->



		<!--**********************************
			Content body end
		***********************************-->



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
        function fetchRoomCategories() {
            return $.getJSON('php/room_category.php');
        }

        function fetchRooms() {
            return $.getJSON('php/room.php');
        }

        function displayRooms(categories, rooms) {
            var tableBody = $('#roomTable tbody');
            tableBody.empty(); // Clear existing rows
            
            var categoriesMap = {};
            categories.forEach(function(category) {
                categoriesMap[category.id] = category.room_type;
            });

            rooms.forEach(function(room, index) {
                var roomCategory = categoriesMap[room.category_id];
                var row = `
                    <tr data-room-id="${room.id}">
                        <td>${index + 1}</td>
                        <td contenteditable="true" class="editable">${room.room_number}</td>
                        <td>
                            <select class="form-control editable">
                                ${Object.entries(categoriesMap).map(([id, type]) => `<option value="${id}" ${room.category_id == id ? 'selected' : ''}>${type}</option>`).join('')}
                            </select>
                        </td>
                        <td contenteditable="true" class="editable">${room.full_capacity}</td>
                        <td contenteditable="true" class="editable">${room.available_space}</td>
                        <td>
                            <button class="btn btn-primary edit-button">Edit</button>
                            <button class="btn btn-success update-button" style="display:none;">Update</button>
                        </td>
                    </tr>
                `;
                tableBody.append(row);
            });

            // Add event listeners for the edit and update buttons
            tableBody.on('click', '.edit-button', function() {
                var row = $(this).closest('tr');
                row.find('.editable').attr('contenteditable', 'true');
                row.find('.edit-button').hide();
                row.find('.update-button').show();
            });

            tableBody.on('click', '.update-button', function() {
                var row = $(this).closest('tr');
                var roomId = row.data('room-id');
                var roomNumber = row.find('td').eq(1).text();
                var roomCategory = row.find('select').val();
                var fullCapacity = row.find('td').eq(3).text();
                var availableSpace = row.find('td').eq(4).text();

                $.ajax({
                    url: 'php/update_room.php',
                    method: 'POST',
                    data: {
                        id: roomId,
                        room_number: roomNumber,
                        category_id: roomCategory,
                        full_capacity: fullCapacity,
                        available_space: availableSpace
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            row.find('.editable').attr('contenteditable', 'false');
                            row.find('.edit-button').show();
                            row.find('.update-button').hide();
                            alert('Room details updated successfully!');
                        } else {
                            alert('Error updating room details: ' + response.message);
                        }
                    },
                    error: function(error) {
                        console.error('Error updating room details:', error);
                    }
                });
            });
        }

        // Fetch room categories and rooms, then display them
        $.when(fetchRoomCategories(), fetchRooms()).done(function(categoriesResponse, roomsResponse) {
            var categories = categoriesResponse[0];
            var rooms = roomsResponse[0];
            displayRooms(categories, rooms);
        }).fail(function(error) {
            console.error('Error fetching room data:', error);
        });
    });
</script>


<script>
    $(document).ready(function() {
        function fetchRoomCategories() {
            $.getJSON('php/room_category.php', function(data) {
                var tableBody = $('#roomDetailsTable tbody');
                tableBody.empty(); // Clear existing rows
                
                data.forEach(function(category, index) {
                    var row = `
                        <tr data-category-id="${category.id}">
                            <td>${index + 1}</td>
                            <td contenteditable="true" class="editable">${category.room_type}</td>
                            <td contenteditable="true" class="editable">${category.rate}</td>
                            <td>
                                <button class="btn btn-primary edit-button">Edit</button>
                                <button class="btn btn-success update-button" style="display:none;">Update</button>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });

                // Add event listeners for the edit and update buttons
                tableBody.on('click', '.edit-button', function() {
                    var row = $(this).closest('tr');
                    row.find('.editable').attr('contenteditable', 'true');
                    row.find('.edit-button').hide();
                    row.find('.update-button').show();
                });

                tableBody.on('click', '.update-button', function() {
                    var row = $(this).closest('tr');
                    var categoryId = row.data('category-id');
                    var roomType = row.find('td').eq(1).text();
                    var rate = row.find('td').eq(2).text();

                    $.ajax({
                        url: 'php/update_room_category.php',
                        method: 'POST',
                        data: {
                            id: categoryId,
                            room_type: roomType,
                            rate: rate
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                row.find('.editable').attr('contenteditable', 'false');
                                row.find('.edit-button').show();
                                row.find('.update-button').hide();
                                alert('Category details updated successfully!');
                            } else {
                                alert('Error updating category details: ' + response.message);
                            }
                        },
                        error: function(error) {
                            console.error('Error updating category details:', error);
                        }
                    });
                });
            }).fail(function(error) {
                console.error('Error fetching room categories:', error);
            });
        }

        // Fetch room categories on page load
        fetchRoomCategories();
    });
</script>


<script>
        $(document).ready(function() {
            // Fetch room categories and populate the dropdown
            function fetchRoomCategories() {
                $.getJSON('php/room_category.php', function(data) {
                    var roomTypeSelect = $('#roomType');
                    roomTypeSelect.empty(); // Clear existing options
                    roomTypeSelect.append('<option selected>Choose...</option>');
                    
                    data.forEach(function(category) {
                        var option = $('<option></option>').val(category.id).text(category.room_type);
                        roomTypeSelect.append(option);
                    });
                }).fail(function(error) {
                    console.error('Error fetching room categories:', error);
                });
            }

            // Fetch room categories on page load
            fetchRoomCategories();

            // Handle form submission
            $('#addRoomForm').submit(function(event) {
                event.preventDefault();
                
                var roomNumber = $('#roomNumber').val();
                var roomType = $('#roomType').val();
                var capacity = $('#capacity').val();

                $.ajax({
                    url: 'php/add_room.php',
                    method: 'POST',
                    data: {
                        roomNumber: roomNumber,
                        roomType: roomType,
                        capacity: capacity
                    },
                    success: function(response) {
                        alert('Room added successfully!');
                        $('#addRoomForm')[0].reset();
                    },
                    error: function(error) {
                        console.error('Error adding room:', error);
                    }
                });
            });
        });
    </script>

<script>
        $(document).ready(function() {
            // Handle form submission
            $('#addCategoryForm').submit(function(event) {
                event.preventDefault();

                var categoryName = $('#categoryName').val();
                var categoryRate = $('#categoryRate').val();

                $.ajax({
                    url: 'php/add_category.php',
                    method: 'POST',
                    data: {
                        categoryName: categoryName,
                        categoryRate: categoryRate
                    },
                    success: function(response) {
                        alert('Room category added successfully!');
                        $('#addCategoryForm')[0].reset();
                    },
                    error: function(error) {
                        console.error('Error adding room category:', error);
                    }
                });
            });
        });
    </script>

</body>


</html>