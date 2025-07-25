<header class="header">
    <a href="#" class="logo"><span>e</span>ventify</a>

    <nav class="navbar">
        <a href="index.php">Home</a>
        <div class="dropdown">
            <a href="halls.php" class="dropbtn">Hall</a>
            <div class="dropdown-content">
                <?php
                    include 'admin/connect.php';
                    $query = "SELECT * FROM hall WHERE status = 'active'";
                    $result = mysqli_query($con, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<a href='specifichall.php?id={$row['id']}'>{$row['name']}</a>";
                        }
                    } else {
                        echo "<a href='#'>No Halls Found</a>";
                    }
                ?>
            </div>
        </div>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
    </nav>
    <?php
        if (isset($_SESSION['customerId'])) {
            echo '<div class="profile-dropdown">
                    <div class="profile-dropdown-btn" onclick="toggle()">
                        
                        <span>
                            ' . $_SESSION['customerName'] . '
                        </span>
                    </div>

                    <ul class="profile-dropdown-list">
                        <li class="profile-dropdown-list-item">
                            <a href="profile.php">
                                <i class="fa-regular fa-id-card"></i>
                                Profile
                            </a>
                        </li>
                        <li class="profile-dropdown-list-item">
                            <a href="changePassword.php">
                                <i class="fa fa-lock"> </i>
                                Change Password
                            </a>
                        </li>
                        <li class="profile-dropdown-list-item">
                            <a href="bookingOrder.php">
                                <i class="fas fa-book"> </i>
                                Booking
                            </a>
                        </li>
                        <hr>
                        <li class="profile-dropdown-list-item">
                            <a href="logout.php">
                                <i class="fa-solid fa-arrow-right-from-bracket"> </i>
                                Log Out
                            </a>
                        </li>
                    </ul>
                </div>';
        } else {
            // User is not logged in, show login link
            echo '<a class="loginDetail" href="login.php">Login</a>';
        }
        ?>

    <div id="menu-bars" class="fas fa-bars"></div>
</header>