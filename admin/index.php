<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
include 'connect.php';  
require_once 'includes/functions.php'; 

// Sanitize role name
$role = htmlspecialchars($_SESSION['roleName']);
?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    
    <!-- =============== Cards =============== -->
    <?php
        if ($role == 'Admin') {
            echo '<div class="cardBox">
                    <div class="card">
                        <div>
                            <div class="numbers">'.getTotalHalls().'</div>
                            <div class="cardName">Total Halls</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="business-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getHallServicesCount().'</div>
                            <div class="cardName">Total Services</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="construct-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getTotalPackages().'</div>
                            <div class="cardName">Total Packages</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="albums-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getTotalBookings().'</div>
                            <div class="cardName">Total Bookings</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="bookmarks-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getTotalCustomers().'</div>
                            <div class="cardName">Customers</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="people-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getTotalRevenue().'</div>
                            <div class="cardName">Total Revenue</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="cash-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getRepeatCustomers().'</div>
                            <div class="cardName">Repeat Customers</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="people-outline"></ion-icon>
                        </div>
                    </div>
            </div>';
        } 
        elseif ($role == 'Hall Owner') {
            $ownerId = $_SESSION['userId'];
            echo '<div class="cardBox">
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerHalls($ownerId).'</div>
                            <div class="cardName">Hall Owner</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="home-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerServices($ownerId).'</div>
                            <div class="cardName">My Services</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="construct-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerPackages($ownerId).'</div>
                            <div class="cardName">My Packages</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="albums-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerBookings($ownerId).'</div>
                            <div class="cardName">Bookings</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerPendingBookings($ownerId).'</div>
                            <div class="cardName">Pending Booking</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="time-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerConfirmedBookings($ownerId).'</div>
                            <div class="cardName">Confirmed Booking</div>
                        </div>
                        <div class="iconBx">
                             <ion-icon name="checkmark-circle-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerDoneBookings($ownerId).'</div>
                            <div class="cardName">Done Bookings</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="flag-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getOwnerEarnings($ownerId).'</div>
                            <div class="cardName">Earnings</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="wallet-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getUpcomingBookings($ownerId).'</div>
                            <div class="cardName">Upcoming (7 Days)</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="today-outline"></ion-icon>
                        </div>
                    </div>
            </div>';
        }
        elseif ($role == 'Editor') {
            echo '<div class="cardBox">
                    <div class="card">
                        <div>
                            <div class="numbers">'.getPendingApprovals().'</div>
                            <div class="cardName">Pending Approvals Halls</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getPublishedHalls().'</div>
                            <div class="cardName">Published Halls</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="checkmark-done-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getRecentTestimonials().'</div>
                            <div class="cardName">New Reviews</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="star-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getHallServicesCount().'</div>
                            <div class="cardName">Services</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="construct-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getTotalPackages().'</div>
                            <div class="cardName">Total Packages</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="albums-outline"></ion-icon>
                        </div>
                    </div>
                    <div class="card">
                        <div>
                            <div class="numbers">'.getTotalBookings().'</div>
                            <div class="cardName">Total Bookings</div>
                        </div>
                        <div class="iconBx">
                            <ion-icon name="bookmarks-outline"></ion-icon>
                        </div>
                    </div>
                </div>';
        }
    ?>
</div>

<?php include './includes/footer.php'; ?>