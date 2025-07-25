<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main" style="overflow-x: hidden;">
    <!-- Top navigation bar -->
    <?php include './includes/topNavBar.php'; ?>
    
    <div class="mainpart mt-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold" style="color: #2e2185;">Hall Management</h2>
            </div>

            <div class="row justify-content-center">
                <?php
                    include 'connect.php';
                    $userId = $_SESSION['userId'];
                    $query = "SELECT h.* 
                            FROM hall h
                            JOIN assignhall ah ON h.id = ah.hallId
                            WHERE ah.userId = $userId
                            ORDER BY h.id DESC";
                    $result = mysqli_query($con, $query);

                    if(mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $statusClass = ($row['status'] == 'Active') ? 'bg-success' : 'bg-danger';
                ?>
                <div class="col-xl-7 col-lg-6 col-md-6 mb-4">
                    <div class="card h-100 shadow-lg hall-card">
                        <div class="hall-img-container">
                            <img src="uploads/<?= $row['cover'] ?>" class="hall-img" alt="Hall Cover">
                            <span class="badge <?= $statusClass ?> status-badge"><?= $row['status'] ?></span>
                            <div class="hall-logo-container">
                                <img src="uploads/<?= $row['logo'] ?>" alt="Hall Logo" class="hall-logo">
                            </div>
                        </div>
                        
                        <div class="card-body px-4 py-3">
                            <div class="hall-header">
                                <h5 class="hall-title"><?= $row['name'] ?></h5>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <ion-icon name="location-outline" class="icon-feature"></ion-icon>
                                <span class="text-muted"><?= $row['address'] ?></span>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <ion-icon name="people-outline" class="icon-feature"></ion-icon>
                                        <small class="text-muted"><?= $row['capacity'] ?> Capacity</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center">
                                        <ion-icon name="calendar-outline" class="icon-feature"></ion-icon>
                                        <small class="text-muted"><?= $row['event_capacity'] ?> Events</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <ion-icon name="call-outline" class="icon-feature"></ion-icon>
                                <span class="text-muted"><?= $row['contactNo'] ?></span>
                            </div>
                            
                            <p class="card-detail mb-0"><?= $row['detail'] ?></p>
                            
                            <div class="action-buttons">
                                <a href="updateHall.php?id=<?= $row['id'] ?>" class="btn btn-sm action-btn">
                                    <ion-icon name="create-outline"></ion-icon> Edit
                                </a>
                                <a href="addService.php?id=<?= $row['id'] ?>" class="btn btn-sm action-btn">
                                    <ion-icon name="restaurant-outline"></ion-icon> Services
                                </a>
                                <a href="addPackage.php?id=<?= $row['id'] ?>" class="btn btn-sm action-btn">
                                    <ion-icon name="cube-outline"></ion-icon> Packages
                                </a>
                                <a href="addGallery.php?id=<?= $row['id'] ?>" class="btn btn-sm action-btn">
                                    <ion-icon name="images-outline"></ion-icon> Gallery
                                </a>
                                <a href="addTestmonials.php?id=<?= $row['id'] ?>" class="btn btn-sm action-btn">
                                    <ion-icon name="chatbubble-ellipses-outline"></ion-icon> Testimonials
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                        }
                    } else {
                        echo '<div class="col-12 text-center py-5">
                                <div class="empty-state p-5 rounded-3 text-center" style="max-width: 600px; margin: 0 auto;">
                                    <div class="empty-state-icon">
                                        <ion-icon name="information-circle-outline"></ion-icon>
                                    </div>
                                    <h4 class="fw-bold mb-3" style="color: #2e2185;">No Halls Found</h4>
                                    <p class="mb-4 text-muted">You haven\'t added any halls yet. Get started by adding your first hall.</p>
                                    <a href="addHall.php" class="btn px-4 py-2 text-white" style="background-color: #2e2185; border-radius: 8px;">
                                        <ion-icon name="add-outline" class="me-1"></ion-icon> Add New Hall
                                    </a>
                                </div>
                              </div>';
                    }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include './includes/footer.php'; ?>