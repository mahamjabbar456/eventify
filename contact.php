<?php 
session_start();
include'./include/startingSection.php'; ?>
    <link rel="stylesheet" href="assets/css/contact.css" />
  </head>
  <body>

    <!--header section starts-->

    <?php
    include'./include/header.php';
    include 'admin/connect.php';

    function showSweetAlert($icon, $title, $text, $redirect = null) {
        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$text',
                showConfirmButton: true,
                timer: 3000
            })";
        if ($redirect) {
            echo ".then(() => { window.location.href = '$redirect'; })";
        }
        echo "</script>";
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
        // Get form data
        $phoneNo = trim($_POST['phoneNo']);
        // Remove all non-numeric characters from CNIC
        $phoneNo = preg_replace('/[^0-9]/', '', $phoneNo);
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $email =  mysqli_real_escape_string($con, $_POST['email']);
        $phoneNo = mysqli_real_escape_string($con, $phoneNo);
        $subject = mysqli_real_escape_string($con, $_POST['subject']);
        $message = mysqli_real_escape_string($con, $_POST['message']);
        $sql = "INSERT INTO query (name, email, phoneNo, subject, message, queryReply) 
                VALUES ('$name', '$email', '$phoneNo', '$subject', '$message', 'NULL')";

        if (mysqli_query($con, $sql)) {
            showSweetAlert('success', 'Message Sent', 'Your query was submitted successfully!',  $_SERVER['HTTP_REFERER']);
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
?>

    <!--header section ends-->

     <!-- contact section starts -->

     <section class="contact clientPadding" id="contact">

        <h1 class="heading"> <span>contact</span> us </h1>

        <div class="contact-box">
            <div class="left">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30542.82997253678!2d80.12412705500039!3d16.883127887204058!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a35a8948e610c35%3A0x1e0889828ef0ba28!2sChillakallu%20Toll%20Plaza!5e0!3m2!1sen!2s!4v1742376452399!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <form action="" method="POST" autocomplete="off">
                <div class="inputBox">
                    <input type="text" placeholder="name" name="name" required>
                    <input type="email" placeholder="email" name="email" required>
                </div>
                <div class="inputBox">
                    <input type="Phone No" placeholder="phoneNo" name="phoneNo" id="phoneNo" required>
                    <input type="text" placeholder="subject" name="subject" required>
                </div>
                <textarea name="message" placeholder="your message" id="" cols="30" rows="10" required></textarea>
                <input type="submit" name="submit" value="send message" class="btn">
            </form>
        </div>

    </section>   

    <!-- contact section ends -->

    <!-- footer section starts -->

    <?php include'./include/footer.php'; ?>

    <!-- footer section ends -->

    <!-- theme toggler starts-->

    <?php include'./include/themeToggler.php'; ?>

    <!-- theme toggler ends -->

    <script>
    $(document).ready(function() {
    // Phone number masking
        $('input[name="phoneNo"]').inputmask({
            mask: '0399-9999999',  // Adjust this format as needed
            placeholder: '_',
            showMaskOnHover: true,
            showMaskOnFocus: true,
        });
    });
    </script>

<?php include'./include/scriptSection.php'; ?>