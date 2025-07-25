<?php
include './connect.php';

// 1. ADMIN FUNCTIONS
function getTotalHalls() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM hall");
    return mysqli_fetch_row($result)[0];
}

function getTotalBookings() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM booking");
    return mysqli_fetch_row($result)[0];
}

function getTotalRevenue() {
    global $con;
    $result = mysqli_query($con, "SELECT SUM(paidPayment) FROM payment");
    return mysqli_fetch_row($result)[0] ?: 0;
}

function getTotalCustomers() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(DISTINCT customerId) FROM booking");
    return mysqli_fetch_row($result)[0];
}

function getRepeatCustomers() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM 
               (SELECT customerId FROM booking GROUP BY customerId HAVING COUNT(*) > 1) AS repeat_customers");
    return mysqli_fetch_row($result)[0];
}

// combined admin and editor card
function getTotalPackages() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM package");
    return mysqli_fetch_row($result)[0];
}

// 2. HALL OWNER FUNCTIONS
function getOwnerHalls($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM assignhall WHERE userId = $ownerId");
    return mysqli_fetch_row($result)[0];
}

function getOwnerBookings($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM booking b 
              JOIN assignhall ah ON b.hallId = ah.hallId 
              WHERE ah.userId = $ownerId");
    return mysqli_fetch_row($result)[0];
}

function getOwnerPendingBookings($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM booking b 
              JOIN assignhall ah ON b.hallId = ah.hallId 
              WHERE ah.userId = $ownerId AND b.status = 'pending'");
    return mysqli_fetch_row($result)[0];
}

function getOwnerConfirmedBookings($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM booking b 
              JOIN assignhall ah ON b.hallId = ah.hallId 
              WHERE ah.userId = $ownerId AND b.status = 'confirmed'");
    return mysqli_fetch_row($result)[0];
}

function getOwnerDoneBookings($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM booking b 
              JOIN assignhall ah ON b.hallId = ah.hallId 
              WHERE ah.userId = $ownerId AND b.status = 'done'");
    return mysqli_fetch_row($result)[0];
}

function getOwnerEarnings($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT SUM(p.paidPayment) FROM payment p 
              JOIN booking b ON p.bookingId = b.bookingId 
              JOIN assignhall ah ON b.hallId = ah.hallId 
              WHERE ah.userId = $ownerId");
    return mysqli_fetch_row($result)[0] ?: 0;
}

function getUpcomingBookings($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM booking b 
               JOIN assignhall ah ON b.hallId = ah.hallId 
               WHERE ah.userId = $ownerId AND b.dateOfBooking BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)");
    return mysqli_fetch_row($result)[0];
}

function getOwnerServices($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM service s 
              JOIN assignhall ah ON s.hallId = ah.hallId 
              WHERE ah.userId = $ownerId");
    return mysqli_fetch_row($result)[0];
}

function getOwnerPackages($ownerId) {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM package p 
              JOIN assignhall ah ON p.hallId = ah.hallId 
              WHERE ah.userId = $ownerId");
    return mysqli_fetch_row($result)[0];
}

// 3. EDITOR FUNCTIONS
function getPendingApprovals() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM hall WHERE status = 'inactive'");
    return mysqli_fetch_row($result)[0];
}

function getPublishedHalls() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM hall WHERE status = 'active'");
    return mysqli_fetch_row($result)[0];
}

function getRecentTestimonials() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM testmonial");
    return mysqli_fetch_row($result)[0];
}

function getHallServicesCount() {
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) FROM service");
    return mysqli_fetch_row($result)[0];
}
?>