<header>
    <div class="topbar">
        <div class="toggle">
                    <label for="">
                        <span><ion-icon name="menu-outline"></ion-icon></span>
                        <h6>Dashboard</h6>
                    </label>
        </div>

        <div class="user-profile">
            <div class="user-trigger">
                <div class="user-avatar">
                    <?php
                    $defaultImage = "../assets/images/profile.jpeg"; 
                    $userImage = isset($_SESSION['userImage']) ? $_SESSION['userImage'] : $defaultImage;
                    ?>
                    <img src="./uploads/<?php echo $userImage; ?>" alt="User" class="profile-img">
                </div>
                <span class="user-name"><?php echo $_SESSION['userName'] ?></span>
                <i class="dropdown-arrow">▼</i>
            </div>
            <ul class="profile-dropdown">
                <li><a href="profile.php"><ion-icon name="person-outline"></ion-icon> Profile</a></li>
                <li><a href="logout.php"><ion-icon name="log-out-outline"></ion-icon> Logout</a></li>
            </ul>
        </div>
    </div>
</header>