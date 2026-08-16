<!--**********************************
			Sidebar start
		***********************************-->
		 <?php include 'admin-sidebar.php'?>
		<!--**********************************
			Sidebar end
		***********************************-->

		<div class="content-body" id="pt-content">
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
											<h3 class="count mb-0" id="userRoomCategory" data-pt-count>0</h3>
											<p class="mb-0">Room Category</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-suitcase"></i>
											</span>
											<h3 class="count mb-0" id="rooms" data-pt-count>0</h3>
											<p class="mb-0">Rooms</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-suitcase"></i>
											</span>
											<h3 class="count mb-0" id="roomSpace" data-pt-count>0</h3>
											<p class="mb-0">Bed Spaces Available</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-calendar"></i>
											</span>
											<h3 class="count mb-0" id="reservations" data-pt-count>0</h3>
											<p class="mb-0">Bed Spaces Assigned</p>
										</div>
									</div>
									<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
										<div class="static-icon">
											<span>
												<i class="fas fa-phone-alt"></i>
											</span>
											<h3 class="count mb-0"><?php
												$totalBeds = 0;
												if (isset($conn)) {
													$tbRes = $conn->query("SELECT SUM(full_capacity) AS total FROM room");
													if ($tbRes) {
														$tbRow = $tbRes->fetch_assoc();
														$totalBeds = (int)$tbRow['total'];
													}
												}
												echo $totalBeds;
											?></h3>
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
						<a href="list-student.php">
						<div class="social-graph-wrapper widget-googleplus">
							<span class="s-icon">List Students <i class="fab fa-"></i></span>
						</div>
						</a>
					</div>
					<div class="col-xl-3 col-xxl-3 col-sm-6">
					<a href="assigned_room.php">
						<div class="social-graph-wrapper widget-twitter">
							<span class="s-icon">Assign Student<i class="fab fa-"></i></span>
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
	
	<!-- Apex Chart -->
		
	<!-- Chart piety plugin files -->
	
	<!-- Dashboard 1 -->
	
	
	<script src="js/custom.min.js"></script>
	<script src="js/dlabnav-init.js"></script>
	<script src="js/demo.js"></script>
	<!-- <script src="js/styleSwitcher.js"></script> -->

		
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
	<!-- Portal polish -->
	<script src="vendor/toastr/js/toastr.min.js"></script>
	<script src="js/paetos.js"></script>
	<script src="js/pt-nav.js"></script>
</body>


</html>