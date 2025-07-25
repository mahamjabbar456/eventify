<?php
// Include the database connection file
include 'connect.php';

// Check if the 'id' parameter is set in the URL to fetch slider details
if (isset($_GET['id'])) {
    $sliderId = $_GET['id'];
    // Query to fetch slider details from the database
    $query = "SELECT * FROM slider WHERE sliderId = $sliderId";
    $result = mysqli_query($con, $query);
    
    // If the slider is found, fetch its details and store in variables
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $sliderTitle = $row['sliderTitle'];
        $sliderTag = $row['sliderTag'];
        $sliderDescription = $row['sliderDescription'];
        $existingSliderImage = $row['sliderImage'];
    } else {
        // If slider is not found, show an alert and redirect
        echo "<script>alert('Slider not found!'); window.location='slider.php';</script>";
        exit();
    }
}

// Handle form submission for updating slider details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // Retrieve the updated values from the form input fields
    $sliderId = $_POST['sliderId'];
    $sliderTitle = mysqli_real_escape_string($con, $_POST['sliderTitle']);
    $sliderTag = mysqli_real_escape_string($con, $_POST['sliderTag']);
    $sliderDescription = mysqli_real_escape_string($con, $_POST['sliderDescription']);

    // Allowed file extensions for the uploaded images
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Handle Logo Image upload
    if (!empty($_FILES['sliderImage']['name'])) {
        $sliderImage = $_FILES['sliderImage']['name'];
        $sliderTmpName = $_FILES['sliderImage']['tmp_name'];
        $sliderExt = strtolower(pathinfo($sliderImage, PATHINFO_EXTENSION));
        // Check if the uploaded logo has a valid extension
        if (in_array($sliderExt, $allowedExtensions)) {
            $newSliderName = uniqid('SLI-', true) . '.' . $sliderExt;
            move_uploaded_file($sliderTmpName, "uploads/" . $newSliderName); // Move uploaded file to 'uploads' folder
            
            // If there's an old logo, delete it
            if (!empty($existingSliderImage) && file_exists("uploads/" . $existingSliderImage)) {
                unlink("uploads/" . $existingSliderImage); // Delete old logo
            }
        } else {
            echo "<script>alert('Invalid logo file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
            exit(); // Exit if invalid file type
        }
    } else {
        // If no new logo uploaded, retain the existing logo
        $newSliderName = $existingSliderImage;
    }

    // SQL query to update the hall details in the database
    $sql = "UPDATE slider SET 
                sliderTitle = '$sliderTitle', 
                sliderTag = '$sliderTag', 
                sliderDescription = '$sliderDescription', 
                sliderImage = '$newSliderName'
            WHERE sliderId = $sliderId";

    // Execute the query and check if the update was successful
    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Slider updated successfully!'); window.location='slider.php';</script>";
        // header('location:slider.php');
    } else {
        echo "Error: " . mysqli_error($con); // Show error message if query fails
    }
}
?>

<!-- Include header and sidebar components -->
<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Update Slider</h2>
        <!-- Form for updating hall details -->
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="sliderId" value="<?= $sliderId ?>">

            <div class="form-row">
                <!-- Slider Name input field -->
                <div class="col-md-4 mb-3">
                    <label for="sliderTitle" class="font-weight-bold">Slider Title</label>
                    <input type="text" class="form-control" id="sliderTitle" name="sliderTitle" value="<?= $sliderTitle ?>" required>
                </div>
                <!-- Address input field -->
                <div class="col-md-4 mb-3">
                    <label for="sliderTag" class="font-weight-bold">Slider Tag</label>
                    <input type="text" class="form-control" id="sliderTag" name="sliderTag" value="<?= $sliderTag ?>" required>
                </div>
                <!-- Phone No input field -->
                <div class="col-md-4 mb-3">
                    <label for="sliderImage" class="font-weight-bold">Slider Image</label>
                    <div class="marginTopNegative d-flex align-items-center">
                        <input type="file" class="form-control mr-2" id="sliderImage" name="sliderImage">
                        <img src="uploads/<?= $existingSliderImage ?>" alt="Slider Image" width="60">
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="sliderDescription" class="font-weight-bold">Slider Description</label>
                    <textarea name="sliderDescription" id="sliderDescription" rows="3" class="form-control"><?= htmlspecialchars($sliderDescription) ?></textarea>
                </div>
            </div>
            <!-- Submit button -->
            <div class="form-group">
                <button class="btn btn-primary" type="submit" name='update'>Update Slider</button>
                <a href="slider.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<!-- Include footer component -->
<?php include './includes/footer.php'; ?>
