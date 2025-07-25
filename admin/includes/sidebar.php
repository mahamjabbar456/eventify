<?php
// MUST be first - no whitespace before!
session_start();

// Verify user is logged in and has a role
if(!isset($_SESSION['userId']) || !isset($_SESSION['roleName'])) {
    header("Location: login.php");
    exit();
}

// Sanitize role name
$role = htmlspecialchars($_SESSION['roleName']);
?>

<div class="container1">
    <div class="navigation">
        <ul>
            <li class="dashboard">
                <a href="#">
                    <span class="icon">
                        <ion-icon name="logo-deviantart"></ion-icon>
                    </span>
                    <span class="title">Eventify</span>
                </a>
            </li>
            <li>
                <a href="index.php"> 
                    <span class="icon">
                        <ion-icon name="home-outline"></ion-icon>
                    </span>
                    <span class="title">Dashboard</span>
                </a>
            </li>
            
            <?php if($role === 'Admin' || $role === 'Editor'): ?>
                <?php if($role === 'Admin'): ?>
                <li class="dropdown">
                    <a href="#">
                        <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="title">User Management</span>
                        <span class="icon"><ion-icon name="chevron-down-outline"></ion-icon></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="user.php">Users</a></li>
                        <li><a href="role.php">Roles</a></li>
                        <li><a href="assignhall.php">Assign Hall</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="dropdown">
                    <a href="#">
                        <span class="icon"><ion-icon name="server"></ion-icon></span>
                        <span class="title">Website Management</span>
                        <span class="icon"><ion-icon name="chevron-down-outline"></ion-icon></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="slider.php">Add Slider</a></li>
                        <li><a href="query.php">Query</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            
            <?php if($role === 'Admin' || $role === 'Hall Owner' || $role === 'Editor'): ?>
                <li class="dropdown">
                    <a href="#">
                        <span class="icon"><ion-icon name="business-outline"></ion-icon></span>
                        <span class="title">Venue Management</span>
                        <span class="icon dropdown-icon"><ion-icon name="chevron-down-outline"></ion-icon></span>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if($role === 'Admin' || $role === 'Editor'): ?>
                            <li><a href="hall.php">Add Hall</a></li>
                        <?php endif; ?>
                        <?php if($role === 'Hall Owner'): ?>
                            <li><a href="showHall.php">Hall</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <li>
                    <a href="booking.php">
                        <span class="icon">
                           <ion-icon name="calendar-outline"></ion-icon>
                        </span>
                        <span class="title">Booking</span>
                    </a>
                </li>
                <?php if($role === 'Hall Owner'): ?>
                    <li>
                        <a href="chat.php">
                            <span class="icon">
                            <ion-icon name="chatbubbles-outline"></ion-icon>
                            </span>
                            <span class="title">Instant Messaging</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="notification.php">
                        <span class="icon">
                        <ion-icon name="notifications-outline"></ion-icon>
                        </span>
                        <span class="title">Notification System</span>
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="logout.php">
                    <span class="icon">
                        <ion-icon name="log-out-outline"></ion-icon>
                    </span>
                    <span class="title">Sign Out</span>
                </a>
            </li>
        </ul>
    </div>