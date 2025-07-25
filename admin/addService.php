<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

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
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_data']['image_tmp'] = $_FILES['image']['tmp_name'];
        $_SESSION['form_data']['image_name'] = $_FILES['image']['name'];
        $_SESSION['form_data']['background_tmp'] = $_FILES['background']['tmp_name'];
        $_SESSION['form_data']['background_name'] = $_FILES['background']['name'];
        
        $serviceName = mysqli_real_escape_string($con, $_POST['serviceName']);
        $detail = mysqli_real_escape_string($con, $_POST['detail']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $hallId = $_POST['hallId'];
        $tagline = !empty($_POST['tagline']) ? mysqli_real_escape_string($con, $_POST['tagline']) : NULL;

        // Check if service name exists
        $checkQuery = "SELECT * FROM service WHERE name = '$serviceName' AND hallId = '$hallId'";
        $checkResult = mysqli_query($con, $checkQuery);

        if(mysqli_num_rows($checkResult) > 0) {
            echo "<script>alert('Error: This service already exists for the selected hall!');</script>";
        } else {
            // Handle optional image upload
            $newImageName = NULL;
            if (!empty($_FILES['image']['name'])) {
                $image = $_FILES['image']['name'];
                $imageTmpName = $_FILES['image']['tmp_name'];
                $imageExt = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($imageExt, $allowedExtensions)) {
                    $newImageName = uniqid('IMG-', true) . '.' . $imageExt;
                    $imageDestination = "uploads/" . $newImageName;
                    move_uploaded_file($imageTmpName, $imageDestination);
                } else {
                    echo "<script>alert('Invalid image format!');</script>";
                    exit();
                }
            }

            // Handle required background upload
            $background = $_FILES['background']['name'];
            $backgroundTmpName = $_FILES['background']['tmp_name'];
            $backgroundExt = strtolower(pathinfo($background, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($backgroundExt, $allowedExtensions)) {
                $newBackgroundName = uniqid('BG-', true) . '.' . $backgroundExt;
                $backgroundDestination = "uploads/" . $newBackgroundName;
                move_uploaded_file($backgroundTmpName, $backgroundDestination);
            } else {
                echo "<script>alert('Invalid background format!');</script>";
                exit();
            }

            // Prepare SQL values
            $imageValue = $newImageName !== NULL ? "'$newImageName'" : "NULL";
            $taglineValue = $tagline !== NULL ? "'$tagline'" : "NULL";

            // Insert into database
            $sql = "INSERT INTO service (name, image, background, detail, tagline, status, hallId) 
                    VALUES ('$serviceName', $imageValue, '$newBackgroundName', '$detail', $taglineValue, '$status', '$hallId')";

            if (mysqli_query($con, $sql)) {
                unset($_SESSION['form_data']);
                echo "<script>
                    alert('Service added successfully!');
                    window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                </script>";
            } else {
                echo "Error: " . mysqli_error($con);
            }
        }
    }
    // Handle delete request
    if (isset($_GET['deleteService'])) {
        $deleteId = mysqli_real_escape_string($con, $_GET['deleteService']);
        $deleteQuery = "DELETE FROM service WHERE serviceId='$deleteId'";
        if (mysqli_query($con, $deleteQuery)) {
            echo "<script>
                alert('Service deleted successfully!');
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
            </script>";
            exit(); // Refresh the page
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Add Service</h2>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="form-row">
                <!-- Hall Name (fixed) -->
                <div class="col-md-4 mb-3">
                        <label for="hallName" class="font-weight-bold">Hall Name</label>
                        <input type="text" class="form-control" id="hallName" value="<?= isset($hallName) ? $hallName : '' ?>" disabled>
                        <input type="hidden" name="hallId" value="<?= $hallId ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="serviceName" class="font-weight-bold">Service Name</label>
                    <input type="text" class="form-control" id="serviceName" placeholder="Enter Service Name" name="serviceName" required value="<?php echo isset($_POST['serviceName']) ? $_POST['serviceName'] : ''; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="image" class="font-weight-bold">Image</label>
                    <input type="file" class="form-control" id="image" placeholder="Enter Service Image" name="image">
                    <?php if (isset($_SESSION['form_data']['image_name'])): ?>
                        <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['image_name']); ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="background" class="font-weight-bold">Background</label>
                    <input type="file" class="form-control" id="background" placeholder="Enter Service Background" name="background" required>
                    <?php if (isset($_SESSION['form_data']['background_name'])): ?>
                        <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['background_name']); ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tagline" class="font-weight-bold">Tagline</label>
                    <input type="text" class="form-control" id="tagline" placeholder="Enter Service Tagline" name="tagline" value="<?php echo isset($_POST['tagline']) ? $_POST['tagline'] : ''; ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status" class="font-weight-bold">Service Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="active" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="detail" class="font-weight-bold">Detail</label>
                    <textarea name="detail" id="detail" class="form-control" rows="3" placeholder="Enter Service Detail"><?php echo isset($_POST['detail']) ? $_POST['detail'] : ''; ?></textarea>
                </div>
            </div>
            <button class="btn btn-primary" type="submit" name="submit">Add Service</button>
        </form>

        <table id="myTable">
            <thead>
                <tr>
                   <th>Sr No.</th>
                   <th>Hall Name</th>
                   <th>Service Name</th>
                   <th>Image</th>
                   <th>Background</th>
                   <th>Detail</th>
                   <th>Tagline</th>
                   <th>Status</th>
                   <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch data from the service table to display
                include 'connect.php';
                
                // Step 1: Fetch all roles and create a mapping array
                $hallsQuery = "SELECT * FROM hall";
                $hallsResult = mysqli_query($con, $hallsQuery);
    
                $hallMapping = [];
                while ($hallRow = mysqli_fetch_assoc($hallsResult)) {
                    $hallMapping[$hallRow['id']] = $hallRow['name'];
                }
                $query = "SELECT * FROM service WHERE hallId = '$hallId' ORDER BY serviceId DESC";
                $result = mysqli_query($con, $query);

                if(mysqli_num_rows($result) > 0){
                    $sr = 1;
                    while ($row = mysqli_fetch_assoc($result)){
                        $hallName = isset($hallMapping[$row['hallId']]) ? $hallMapping[$row['hallId']] : "Unknown hall";
                        $displayImage = (!empty($row['image'])) 
                            ? "<img src='uploads/{$row['image']}' alt='image' width='80' height='50' />"
                            : 'NULL';
                        $tagline = (!empty($row['tagline'])) 
                            ? $row['tagline']
                            : 'NULL';
                        echo "<tr>
                            <td>{$sr}</td>
                            <td>{$hallName}</td>
                            <td>{$row['name']}</td>
                            <td>{$displayImage}</td>
                            <td><img src='uploads/{$row['background']}' alt='background' width='120' height='70' /></td>
                            <td>".nl2br(htmlspecialchars($row['detail']))."</td>
                            <td>{$tagline}</td>
                            <td>{$row['status']}</td>
                            <td class='text-center'>
                               <div class='d-inline-flex justify-content-center'>
                                <a href='updateService.php?id={$row['serviceId']}&hallId={$row['hallId']}' class='btn btn-warning btn-sm mr-2' title='Edit'><ion-icon name='create-outline'></ion-icon></a>
                                  <a href='?deleteService={$row['serviceId']}' class='btn btn-danger btn-sm mr-2' onclick='return confirm(\"Are you sure?\")' title='Delete'><ion-icon name='trash-outline'></ion-icon></a>
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
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>  