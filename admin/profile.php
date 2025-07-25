<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main" style="overflow-x:hidden;">
            <!-- Top navigation bar -->
            <?php include './includes/topNavBar.php'; ?>
            <div class="mainpart mt-4 mx-3">
            <?php
              include 'connect.php';
              $userId = $_SESSION['userId'];
              $query = "SELECT * FROM user WHERE userId = '$userId'";
              $result = mysqli_query($con, $query);
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $safeId = htmlspecialchars($userId, ENT_QUOTES);
                    $username = htmlspecialchars($row['username'] ?? 'Not set');
                    $cnic = htmlspecialchars($row['cnic'] ?? 'Not set');
                    $email = htmlspecialchars($row['email'] ?? 'Not set');
                    $phone = htmlspecialchars($row['phone'] ?? 'Not set');
                    $dob = !empty($row['dateOfBirth']) ? date('F j, Y', strtotime($row['dateOfBirth'])) : 'Not set';
                    $address = htmlspecialchars($row['address'] ?? 'Not set');
                    $profilePic = !empty($row['image']) ? 'uploads/' . $row['image'] : '../assets/images/aboutus.jpeg';
                    echo <<<HTML
                        <div class="profileContainer">
                            <div class="profile-header">
                                <div class="profile-avatar">
                                    <img src="$profilePic" alt="Profile Picture" />
                                    <button class="edit-avatar-btn"  onclick="window.location.href='updateProfile.php?id={$safeId}'">
                                        <ion-icon name="create-outline"></ion-icon>
                                    </button>
                                </div>
                                <h2>$username</h2>
                                <div class="profile-actions">
                                    <button class="btn-edit" onclick="window.location.href='updateProfileInfo.php?id={$safeId}'">
                                        <ion-icon name="person-circle-outline"></ion-icon> Edit Profile
                                    </button>
                                    <button class="btn-change-password" onclick="window.location.href='changePassword.php'">
                                        <ion-icon name="key-outline"></ion-icon> Change Password
                                    </button>
                                </div>
                            </div>

                            <div class="profile-details">
                                <div class="detail-card">
                                    <div class="detail-item">
                                        <span class="detail-icon">
                                            <ion-icon name="card-outline"></ion-icon>
                                        </span>
                                        <div class="detail-content">
                                            <h4>CNIC</h4>
                                            <p>$cnic</p>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-icon">
                                            <ion-icon name="mail-outline"></ion-icon>
                                        </span>
                                        <div class="detail-content">
                                            <h4>Email</h4>
                                            <p>$email</p>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-icon">
                                            <ion-icon name="call-outline"></ion-icon>
                                        </span>
                                        <div class="detail-content">
                                            <h4>Phone</h4>
                                            <p>$phone</p>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-icon">
                                            <ion-icon name="calendar-outline"></ion-icon>
                                        </span>
                                        <div class="detail-content">
                                            <h4>Date of Birth</h4>
                                            <p>$dob</p>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-icon">
                                            <ion-icon name="location-outline"></ion-icon>
                                        </span>
                                        <div class="detail-content">
                                            <h4>Address</h4>
                                            <p>$address</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    HTML;
                }
              } else {
                    echo "<div class='error-message'>User not found.</div>";
              }
            ?>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>