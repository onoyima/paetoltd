<!--**********************************
			Sidebar start
		***********************************-->
		 <?php include 'admin-sidebar.php'?>
		<!--**********************************
			Sidebar end
		***********************************-->

		<div class="content-body">
			<!-- row -->
			<div class="container-fluid">
				<div class="row">
					<div class="col-xl-12">
						<div class="card">
							<div class="card-body">
								<div class="row shapreter-row">
									<!-- <div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-eye"></i>
											</span>
											<h3 class="count mb-0" id="userCount">0</h3>
											<p class="mb-0">Users</spapn>
										</div>
									</div> -->
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="far fa-comments"></i>
											</span>
											<h3 class="count mb-0" id="userRoomCategory">0</h3>
											<p class="mb-0">Room Category</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-suitcase"></i>
											</span>
											<h3 class="count mb-0" id="rooms">0</h3>
											<p class="mb-0">Rooms</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-suitcase"></i>
											</span>
											<h3 class="count mb-0" id="roomSpace">0</h3>
											<p class="mb-0">Bed Spaces Available</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-calendar"></i>
											</span>
											<h3 class="count mb-0" id="reservations">0</h3>
											<p class="mb-0">Bed Spaces Assigned</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-phone-alt"></i>
											</span>
											<h3 class="count mb-0" id="">744</h3>
											<p class="mb-0">Total Bed Spaces </p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
					<a href="manage-hostel.php">
						<div class="social-graph-wrapper widget-facebook">
							<span class="s-icon">Manage Hostel<i class="fab fa-add"></i></span>
						</div>
					</a>
						
					</div>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
					<a href="confirm-payments.php">
						<div class="social-graph-wrapper widget-linkedin">
							<span class="s-icon">Confirm Payments<i class="fab fa-"></i></span>
						</div>
					</a>
					</div>
					<!-- <div class="col-xl-3 col-xxl-3 col-sm-6">
						<a href="list-student.php">
						<div class="social-graph-wrapper widget-googleplus">
							<span class="s-icon">List Students <i class="fab fa-"></i></span>
						</div>
						</a>
					</div> -->
						<div class="col-xl-3 col-xxl-3 col-sm-6">
						<a href="assigned_room.php">
						<div class="social-graph-wrapper widget-googleplus">
							<span class="s-icon">List Students <i class="fab fa-"></i></span>
						</div>
						</a>
					</div>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
					<a href="manage-hostel.php#view_room">
						<div class="social-graph-wrapper widget-twitter">
							<span class="s-icon">View Hostel<i class="fab fa-"></i></span>
						</div>
					</a>
					</div>

				</div>
			</div>
		</div>




		<!--**********************************
			Content body end
		***********************************-->



	    <!--**********************************
            Footer start
        ***********************************-->
        <?php include 'footer.php'?>
        <!--**********************************
            Footer end
        ***********************************-->



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
        // Function to fetch user count and update the HTML
        function fetchUserCount() {
            fetch('php/fetch_user_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('userCount').innerText = data.user_count;
                    } else {
                        console.error('Error fetching user count:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Fetch user count on page load
        document.addEventListener('DOMContentLoaded', fetchUserCount);
    </script>
	<script>
        // Function to fetch room category count and update the HTML
        function fetchRoomCategoryCount() {
            fetch('php/fetch_roomCategory.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('userRoomCategory').innerText = data.userRoomCategory;
						
                    } else {
                        console.error('Error fetching userRoomCategory:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Fetch room category count on page load
        document.addEventListener('DOMContentLoaded', fetchRoomCategoryCount);
    </script>

<script>
        // Function to fetch room numbers and update the HTML
        function fetchRoomNumbers() {
            fetch('php/fetch_roomnumbers.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('rooms').innerText = data.rooms;
						
                    } else {
                        console.error('Error fetching rooms:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Fetch room numbers on page load
        document.addEventListener('DOMContentLoaded', fetchRoomNumbers);
    </script>

<script>
        // Function to fetch room assignments and update the HTML
        function fetchRoomAssignments() {
            fetch('php/fetch_roomassign.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('reservations').innerText = data.reservations;
						
                    } else {
                        console.error('Error fetching reservations:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Fetch room assignments on page load
        document.addEventListener('DOMContentLoaded', fetchRoomAssignments);
    </script>
	<script>
        // Function to fetch payments data and update the HTML
        function fetchPaymentsData() {
            fetch('php/fetch_roompayment.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('payments').innerText = data.payments;
						
                    } else {
                        console.error('Error fetching payments:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Fetch payments data on page load
        document.addEventListener('DOMContentLoaded', fetchPaymentsData);
    </script>

<script>
    // Function to fetch total available space and update the HTML
    function fetchTotalAvailableSpace() {
        fetch('php/fetch_roomspace.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('roomSpace').innerText = data.total_available_space;
                } else {
                    console.error('Error fetching total available space:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Fetch total available space on page load
    document.addEventListener('DOMContentLoaded', fetchTotalAvailableSpace);
</script>
	
</body>


</html>