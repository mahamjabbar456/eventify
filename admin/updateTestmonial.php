<?php
// Include database connection file
include 'connect.php';

// Check if the 'id' and 'hallId' parameters are set in the URL
if (isset($_GET['id']) && isset($_GET['hallId'])) {
    $testmonialId = $_GET['id'];
    $hallId = $_GET['hallId'];

    $hallQuery = "SELECT * FROM hall WHERE id = '$hallId'";
    $hallResult = mysqli_query($con, $hallQuery);
    $hall = mysqli_fetch_assoc($hallResult);
    $hallName = $hall['name'];

    // Query to fetch testimonial details
    $query = "SELECT * FROM testmonial WHERE testmonialId = $testmonialId AND hallId = $hallId";
    $result = mysqli_query($con, $query);

    // If testimonial is found, fetch its details
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $existingClientImage = $row['clientImage']; // This could be NULL
        $clientName = $row['clientName'];
        $clientTitle = $row['clientTitle'];
        $clientReview = $row['clientReview'];
        $clientRating = $row['clientRating'];
    } else {
        echo "<script>alert('Testimonial not found!'); window.location = 'addTestmonials.php?id={$hallId}';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request!'); window.location = 'addTestmonials.php?id={$hallId}';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $testmonialId = $_POST['testmonialId'];
    $hallId = $_POST['hallId'];

    $clientName = mysqli_real_escape_string($con, $_POST['clientName']);
    $clientTitle = mysqli_real_escape_string($con, $_POST['clientTitle']);
    $clientReview = mysqli_real_escape_string($con, $_POST['clientReview']);
    $clientRating = $_POST['clientRating'];

    // Initialize image variables
    $newImageName = null; // Default to NULL
    $deleteOldImage = false;

    // Handle Image Upload only if a new image is provided
    if (!empty($_FILES['clientImage']['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $image = $_FILES['clientImage']['name'];
        $imageTmpName = $_FILES['clientImage']['tmp_name'];
        $imageExt = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        if (in_array($imageExt, $allowedExtensions)) {
            $newImageName = uniqid('IMG-', true) . '.' . $imageExt;
            move_uploaded_file($imageTmpName, "uploads/" . $newImageName);
            $deleteOldImage = true;
        } else {
            echo "<script>alert('Invalid image file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
            exit();
        }
    }

    // If no new image uploaded and user wants to remove existing image
    if (isset($_POST['removeImage']) && $_POST['removeImage'] == '1') {
        $newImageName = null;
        $deleteOldImage = true;
    }

    // Delete old image if needed
    if ($deleteOldImage && !empty($existingClientImage) && file_exists("uploads/" . $existingClientImage)) {
        unlink("uploads/" . $existingClientImage);
    }

    // Prepare the image value for SQL
    $imageValue = ($newImageName !== null) ? "'$newImageName'" : "NULL";

    // Update testimonial details
    $sql = "UPDATE testmonial SET 
                clientImage = $imageValue,
                clientName = '$clientName',
                clientTitle = '$clientTitle',
                clientReview = '$clientReview',
                clientRating = '$clientRating'
            WHERE testmonialId = $testmonialId AND hallId = $hallId";

    if (mysqli_query($con, $sql)) {
        echo "<script>
            alert('Testimonial updated successfully!');
            window.location = 'addTestmonials.php?id={$hallId}';
        </script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($con);
    }
}
?>

<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Update Service</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="testmonialId" value="<?= $testmonialId ?>">
            <input type="hidden" name="hallId" value="<?= $hallId ?>">

            <div class="form-row">
                <div class="col-md-6 mb-3">
                    <label for="clientName" class="font-weight-bold">Client Name</label>
                    <input type="text" class="form-control" id="clientName" name="clientName" value="<?= $clientName ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="clientImage" class="me-2 font-weight-bold">Client Image</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control mt-0 mr-3" id="clientImage" name="clientImage" style="width: auto;">
                        
                        <?php if (!empty($existingClientImage)): ?>
                            <img src="uploads/<?= $existingClientImage ?>" alt="Current Image" width="60" class="rounded mr-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="removeImage" id="removeImage" value="1">
                                <label class="form-check-label" for="removeImage">Remove Image</label>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">No image currently set</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="clientTitle" class="font-weight-bold">Service Name/Client Title</label>
                    <input type="text" class="form-control" id="clientTitle" name="clientTitle" value="<?= $clientTitle ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="clientRating" class="font-weight-bold">Client Rating</label>
                    <select class="form-control" id="clientRating" name="clientRating" required>
                        <option value="5" <?= ($clientRating == 5) ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5 Stars)</option>
                        <option value="4" <?= ($clientRating == 4) ? 'selected' : '' ?>>⭐⭐⭐⭐ (4 Stars)</option>
                        <option value="3" <?= ($clientRating == 3) ? 'selected' : '' ?>>⭐⭐⭐ (3 Stars)</option>
                        <option value="2" <?= ($clientRating == 2) ? 'selected' : '' ?>>⭐⭐ (2 Stars)</option>
                        <option value="1" <?= ($clientRating == 1) ? 'selected' : '' ?>>⭐ (1 Star)</option>
                    </select>
                </div>


                <div class="col-md-12 mb-3">
                    <label for="clientReview" class="font-weight-bold">Client Review</label>
                    <textarea name="clientReview" id="clientReview" class="form-control" rows="3" required><?= htmlspecialchars($clientReview) ?></textarea>
                </div>

            </div>
            <div class="form-group">
                 <button class="btn btn-primary" type="submit" name="update">Update Testmonial</button>
                <a href="addTestmonials.php?id=<?php echo $hallId; ?>" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>
