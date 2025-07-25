<?php
include 'connect.php';

// Fetch hallId from the URL parameter
$hallId = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch hall name if hallId is provided
if ($hallId) {
    // Fetch hall details using the hallId
    $hallQuery = "SELECT * FROM hall WHERE id = '$hallId'";
    $hallResult = mysqli_query($con, $hallQuery);
    $hall = mysqli_fetch_assoc($hallResult);
    $hallName = $hall['name'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // Get form data
    $clientName = mysqli_real_escape_string($con, $_POST['clientName']);
    $clientTitle = mysqli_real_escape_string($con, $_POST['clientTitle']);
    $clientReview = mysqli_real_escape_string($con, $_POST['clientReview']);
    $clientRating = $_POST['clientRating'];
    $hallId = $_POST['hallId'];
    
    // Initialize image variables
    $newImageName = null;
    $imageUploaded = false;

    // Check if an image was uploaded
    if (!empty($_FILES['clientImage']['name'])) {
        $clientImage = $_FILES['clientImage']['name'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $imageTmpName = $_FILES['clientImage']['tmp_name'];
        $imageSize = $_FILES['clientImage']['size'];
        $imageError = $_FILES['clientImage']['error'];
        $imageExt = strtolower(pathinfo($clientImage, PATHINFO_EXTENSION));

        // Validate file types
        if (in_array($imageExt, $allowedExtensions)) {
            // Check if there is an error in file upload
            if ($imageError === 0) {
                // Check file size (5MB limit)
                if ($imageSize < 5000000) {
                    // Unique names for images
                    $newImageName = uniqid('IMG-', true) . '.' . $imageExt;

                    // Set image upload path
                    $imageDestination = "uploads/" . $newImageName;

                    // Move images to the destination folder
                    if (move_uploaded_file($imageTmpName, $imageDestination)) {
                        $imageUploaded = true;
                    }
                } else {
                    echo "<script>alert('File size too large! Max 5MB allowed.');</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Error uploading file!');</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
            exit();
        }
    }

    // Prepare SQL query based on whether image was uploaded
    if ($imageUploaded) {
        $sql = "INSERT INTO testmonial (clientImage, clientName, clientTitle, clientReview, clientRating, hallId) 
                VALUES ('$newImageName', '$clientName', '$clientTitle', '$clientReview', '$clientRating', '$hallId')";
    } else {
        $sql = "INSERT INTO testmonial (clientImage, clientName, clientTitle, clientReview, clientRating, hallId) 
                VALUES (NULL, '$clientName', '$clientTitle', '$clientReview', '$clientRating', '$hallId')";
    }

    if (mysqli_query($con, $sql)) {
        echo "<script>alert('Testimonial added successfully!'); window.location='addTestmonials.php?id={$hallId}'</script>";
    } else {
        echo "Error: " . mysqli_error($con);
    }
}

 // Handle delete request
 if (isset($_GET['deleteTestmonial'])) {
    $deleteId = mysqli_real_escape_string($con, $_GET['deleteTestmonial']);
    $deleteQuery = "DELETE FROM testmonial WHERE testmonialId='$deleteId'";
    if (mysqli_query($con, $deleteQuery)) {
        echo "<script>
            alert('Testmonial deleted successfully!');
            window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
        </script>";
        exit(); // Refresh the page
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
        <h2>Add Testmonial</h2>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="hallId" value="<?= $hallId ?>">

            <div class="form-row">
                <!-- Hall Name (fixed) -->
                <div class="col-md-6 mb-3">
                        <label for="clientName" class="font-weight-bold">Client Name</label>
                        <input type="text" class="form-control" id="clientName" name="clientName" placeholder="Enter client name">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="clientImage" class="font-weight-bold">Client Image</label>
                    <input type="file" class="form-control" id="clientImage" placeholder="Enter Service Name" name="clientImage">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="clientTitle" class="font-weight-bold">Service Name/Client Title</label>
                    <input type="text" class="form-control" id="clientTitle" placeholder="Enter Client Title" name="clientTitle" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="clientRating" class="font-weight-bold">Client Rating</label>
                    <select class="form-control" id="clientRating" name="clientRating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                        <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                        <option value="3">⭐⭐⭐ (3 Stars)</option>
                        <option value="2">⭐⭐ (2 Stars)</option>
                        <option value="1">⭐ (1 Star)</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="clientReview" class="font-weight-bold">Client Review</label>
                    <textarea name="clientReview" id="clientReview" class="form-control" rows="3" placeholder="Enter Client Review"></textarea>
                </div>
            
            </div>
            <button class="btn btn-primary" type="submit" name="submit">Add Testmonials</button>
        </form>

        <div class="table-responsive">
        <table id="myTable">
            <thead class="th-background">
                <tr class="th-background">
                   <th>Sr No.</th>
                   <th>Hall Name</th>
                   <th>Client Name</th>
                   <th>Client Image</th>
                   <th>Service Name</th>
                   <th>Rating</th>
                   <th>Client Review</th>
                   <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch data from the service table to display
                include 'connect.php';
                
                $query = "SELECT * FROM testmonial WHERE hallId = '$hallId' ORDER BY testmonialId DESC";
                $result = mysqli_query($con, $query);

                if(mysqli_num_rows($result) > 0){
                    $sr = 1;
                    while ($row = mysqli_fetch_assoc($result)){
                        // $hallName = isset($hallMapping[$row['hallId']]) ? $hallMapping[$row['hallId']] : "Unknown hall";
                        $ratingStars = str_repeat("⭐", $row['clientRating']);
                        $displayImage = (!empty($row['clientImage'])) 
                            ? 'uploads/' . $row['clientImage'] 
                            : '../assets/images/reviewdumyimage.jpg';
                        echo "<tr>
                            <td>{$sr}</td>
                            <td>{$hallName}</td>
                            <td>{$row['clientName']}</td>
                            <td><img src='{$displayImage}' alt='{$row['clientName']}' width='80' height='50' /></td>
                            <td>{$row['clientTitle']}</td>
                            <td>".nl2br(htmlspecialchars($row['clientReview']))."</td>
                            <td>{$ratingStars}</td>
                            <td class='text-center'>
                               <div class='d-inline-flex justify-content-center'>
                                <a href='updateTestmonial.php?id={$row['testmonialId']}&hallId={$row['hallId']}' class='btn btn-warning btn-sm mr-2' title='Edit'><ion-icon name='create-outline'></ion-icon></a>
                                  <a href='?deleteTestmonial={$row['testmonialId']}' class='btn btn-danger btn-sm mr-2' onclick='return confirm(\"Are you sure?\")' title='Delete'><ion-icon name='trash-outline'></ion-icon></a>
                               </div>
                            </td>
                        </tr>";
                        $sr++;
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No Services Found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>  