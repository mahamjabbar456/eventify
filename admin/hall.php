
<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
require_once './includes/auth.php';
editorOnly(); // Editor and Admin can access
?>

<?php
    include 'connect.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_data']['logo_tmp'] = $_FILES['logo']['tmp_name'];
        $_SESSION['form_data']['logo_name'] = $_FILES['logo']['name'];
        $_SESSION['form_data']['cover_tmp'] = $_FILES['cover']['tmp_name'];
        $_SESSION['form_data']['cover_name'] = $_FILES['cover']['name'];
        $hallName = mysqli_real_escape_string($con, $_POST['hallName']);
        $address =  mysqli_real_escape_string($con, $_POST['address']);
        $phoneNo = mysqli_real_escape_string($con, str_replace('-', '', $_POST['phoneNo']));
        $capacity = mysqli_real_escape_string($con, $_POST['capacity']);
        $event_capacity = mysqli_real_escape_string($con, $_POST['event_capacity']);
        $status = mysqli_real_escape_string($con,$_POST['status']) ;
        $hallDetail = mysqli_real_escape_string($con, $_POST['hallDetail']);

        $checkQuery = "SELECT * FROM hall WHERE name = '$hallName'";
        $checkResult = mysqli_query($con,$checkQuery);

        if(mysqli_num_rows($checkResult) > 0){
            echo "<script>alert('Error: Hall name already exists! Please choose a different name.')</script>";
        } else {
            // Image upload handling
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // Logo Image
            $logoImage = $_FILES['logo']['name'];
            $logoTmpName = $_FILES['logo']['tmp_name'];
            $logoSize = $_FILES['logo']['size'];
            $logoError = $_FILES['logo']['error'];
            $logoExt = strtolower(pathinfo($logoImage, PATHINFO_EXTENSION));

            // Cover Image
            $coverImage = $_FILES['cover']['name'];
            $coverTmpName = $_FILES['cover']['tmp_name'];
            $coverSize = $_FILES['cover']['size'];
            $coverError = $_FILES['cover']['error'];
            $coverExt = strtolower(pathinfo($coverImage, PATHINFO_EXTENSION));

            // Validate file types
            if (in_array($logoExt, $allowedExtensions) && in_array($coverExt, $allowedExtensions)) {
                // Check if there is an error in file upload
                if ($logoError === 0 || $coverError === 0) {
                    // Check file size (5MB limit)
                    if ($logoSize < 5000000 || $coverSize < 5000000) {
                        // Unique names for images
                        $newLogoName = uniqid('LOGO-', true) . '.' . $logoExt;
                        $newCoverName = uniqid('COVER-', true) . '.' . $coverExt;

                        // Set image upload path
                        $logoDestination = "uploads/" . $newLogoName;
                        $coverDestination = "uploads/" . $newCoverName;

                        // Move images to the destination folder
                        move_uploaded_file($logoTmpName, $logoDestination);
                        move_uploaded_file($coverTmpName, $coverDestination);

                        // Insert into database
                        $sql = "INSERT INTO hall (name, address, capacity, contactNo, detail, logo, cover, event_capacity, status) 
                                VALUES ('$hallName', '$address', '$capacity', '$phoneNo', '$hallDetail', '$newLogoName', '$newCoverName', '$event_capacity', '$status')";

                        if (mysqli_query($con, $sql)) {
                            unset($_SESSION['form_data']);
                            echo "<script>
                                        alert('Hall added successfully!');
                                        window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                                </script>";
                        } else {
                            echo "Error: " . mysqli_error($con);
                        }
                    } else {
                        echo "<script>alert(`File size too large! Max 5MB allowed.`);</script>";
                    }
                } else {
                    echo "<script>alert('Error uploading file!');</script>";
                }
            } else {
                echo "<script>alert('Invalid file type! Only JPG, PNG, GIF, and WEBP allowed.');</script>";
            }
            unset($_SESSION['form_data']);
        }
    }
    // Handle delete request
    if (isset($_GET['deleteId'])) {
        $deleteId = mysqli_real_escape_string($con, $_GET['deleteId']);
        $deleteQuery = "DELETE FROM hall WHERE id='$deleteId'";
        if (mysqli_query($con, $deleteQuery)) {
            echo "<script>alert('Hall deleted successfully!'); window.location='hall.php';</script>";
            exit(); // Refresh the page
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
?>

<div class="main" style="overflow-x:hidden;">
            <!-- Top navigation bar -->
            <?php include './includes/topNavBar.php'; ?>
            <div class="mainpart mt-4 mx-3">
                <h2>Add Hall</h2>
                <form method="post" enctype="multipart/form-data" id="hallForm" autocomplete="off">
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="hallName" class="font-weight-bold">Hall Name</label>
                            <input type="text" class="form-control" id="hallName" placeholder="Enter Hall Name"
                                name="hallName" required value="<?php echo isset($_POST['hallName']) ? $_POST['hallName'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="address" class="font-weight-bold">Address</label>
                            <input type="text" class="form-control" id="address" placeholder="Enter your Address"
                                name="address" required value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="phoneNo" class="font-weight-bold">Phone No</label>
                            <input type="tel" class="form-control" id="phoneNo" placeholder="Enter your Phone No"
                                name="phoneNo" required value="<?php echo isset($_POST['phoneNo']) ? $_POST['phoneNo'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="capacity" class="font-weight-bold">Capacity</label>
                            <input type="number" class="form-control" id="capacity" placeholder="Enter your hall capacity"
                                name="capacity" required value="<?php echo isset($_POST['capacity']) ? $_POST['capacity'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="event_capacity" class="font-weight-bold">Function Capacity at a time</label>
                            <input type="number" class="form-control" id="event_capacity" placeholder="Enter your Maximum function capacity at a time:"
                                name="event_capacity" required value="<?php echo isset($_POST['event_capacity']) ? $_POST['event_capacity'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="font-weight-bold">Hall Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= (isset($_POST['status']) && $_POST['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="logo" class="font-weight-bold">Logo Image</label>
                            <input type="file" class="form-control" id="logo" placeholder="Enter your Image"
                                name="logo" required>
                                <?php if (isset($_SESSION['form_data']['logo_name'])): ?>
                                    <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['logo_name']); ?></small>
                                <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cover" class="font-weight-bold">Cover Image</label>
                            <input type="file" class="form-control" id="cover" placeholder="Enter your Image"
                                name="cover" required>
                                <?php if (isset($_SESSION['form_data']['cover_name'])): ?>
                                    <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['cover_name']); ?></small>
                                <?php endif; ?>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="hallDetail" class="font-weight-bold">Detail</label>
                            <textarea name="hallDetail" id="hallDetail" placeholder="Enter your description" rows="3" class="form-control" ><?php echo isset($_POST['hallDetail']) ? $_POST['hallDetail'] : ''; ?></textarea>
                        </div>

                    </div>
                    <button class="btn btn-primary" type="submit" name='submit'>Add Hall</button>
                </form>

                <div class="table-responsive" style="width: 100%; overflow-x: auto;">
                <table id="myTable" class="table table-striped table-bordered table-hover">
                    <thead class="th-background">
                        <tr class="th-background">
                           <th>Sr No.</th>
                           <th>Hall Name</th>
                           <th>Address</th>
                           <th>Capacity</th>
                           <th>Function Capacity</th>
                           <th>Status</th>
                           <th>Phone No</th>
                           <th style="width:200px;">Details</th>
                           <th>Logo Image</th>
                           <th>Cover Image</th>
                           <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                           include 'connect.php';
                           $query = "SELECT * FROM hall ORDER BY id DESC";
                           $result = mysqli_query($con,$query);

                           if(mysqli_num_rows($result) > 0){
                            $sr = 1;
                            while ($row = mysqli_fetch_assoc($result)){
                                echo "<tr>
                            <td>{$sr}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['address']}</td>
                            <td>{$row['capacity']}</td>
                            <td>{$row['event_capacity']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['contactNo']}</td>
                            <td style='max-width:200px;'>".nl2br(htmlspecialchars($row['detail']))."</td>
                            <td><img src='uploads/{$row['logo']}' alt='logo' width='80' height='50' /></td>
                            <td><img src='uploads/{$row['cover']}' alt='cover' width='120' height='70' /></td>
                            <td class='text-center'>
                               <div class='d-flex justify-content-center flex-wrap'>
                               <a href='updateHall.php?id={$row['id']}' class='btn btn-warning btn-sm m-1' title='Edit'><ion-icon name='create-outline'></ion-icon></a>
                               <a href='?deleteId={$row['id']}' class='btn btn-danger btn-sm m-1' onclick='return confirm(\"Are you sure?\")' title='Delete'><ion-icon name='trash-outline'></ion-icon></a>
                               <a href='addService.php?id={$row['id']}' class='btn btn-primary btn-sm m-1' >Add Services</a>
                               <a href='addPackage.php?id={$row['id']}' class='btn btn-primary btn-sm m-1' >Add Package</a>
                               <a href='addGallery.php?id={$row['id']}' class='btn btn-primary btn-sm m-1' >Add Gallery</a>
                               <a href='addTestmonials.php?id={$row['id']}' class='btn btn-primary btn-sm m-1' >Add Testmonial</a>
                               
                                </div>
                            </td>
                        </tr>";
                        $sr++;
                            }
                           } else {
                            echo "<tr><td colspan='9' class='text-center'>No Halls Found</td></tr>";
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

<?php include './includes/footer.php'; ?>