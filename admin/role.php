<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
    require_once './includes/auth.php';
    adminOnly(); // Editor and Admin can access
?>

<?php
 include 'connect.php';
 if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])){
    $_SESSION['form_data'] = $_POST;
    $_SESSION['form_data']['roleImage_tmp'] = $_FILES['roleImage']['tmp_name'];
    $_SESSION['form_data']['roleImage_name'] = $_FILES['roleImage']['name'];
    $roleName=mysqli_real_escape_string($con,$_POST['roleName']);
    
    $imageName = $_FILES['roleImage']['name'];
    $imageTmpName = $_FILES['roleImage']['tmp_name'];
    $imageSize = $_FILES['roleImage']['size'];
    $imageError = $_FILES['roleImage']['error'];
    $imageType = $_FILES['roleImage']['type'];

    $checkQuery = "SELECT * FROM role WHERE roleName = '$roleName'";
    $checkResult = mysqli_query($con, $checkQuery);

    if(mysqli_num_rows($checkResult)>0){
        echo "<script>alert('Error: This role already exists! Please choose a different rolename.');</script>";
    }else {
        $allowedExtensions = ['jpg','jpeg', 'png', 'gif', 'webp'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if(in_array($imageExt, $allowedExtensions)){
            if($imageError === 0){
                if($imageSize < 5000000){
                    $newImageName = uniqid('IMG-',true) . '.' . $imageExt;
                    $imageDestination = "uploads/" . $newImageName;

                    move_uploaded_file($imageTmpName, $imageDestination);

                    // $sql = "INSERT INTO `role`(`roleName`, `roleImage`) VALUES ('[value-2]','[value-3]')";
                    $sql = "INSERT INTO `role`(`roleName`, `roleImage`) VALUES ('$roleName','$newImageName')";

                    if(mysqli_query($con,$sql)){
                        unset($_SESSION['form_data']);
                        echo "<script>alert('Role added successfully!');</script>";
                        echo "<script>window.location.href = '".$_SERVER['PHP_SELF']."';</script>";
                    } else {
                        echo "Error : " . mysqli_error($con);
                    }
                }else {
                    echo "<script>alert('File size too large!');</script>";
                }
            }else{
                echo "<script>alert('Error uploading file!');</script>";
            }
        }else{
            echo "<script>alert('Invalid file type!');</script>";
        }
        unset($_SESSION['form_data']);
    }
 }

  // Handle delete request
    if (isset($_GET['deleteId'])) {
        $deleteId = mysqli_real_escape_string($con, $_GET['deleteId']);
        $deleteQuery = "DELETE FROM role WHERE roleId='$deleteId'";
        if (mysqli_query($con, $deleteQuery)) {
            echo "<script>alert('Role deleted successfully!'); window.location='role.php';</script>";
            exit(); // Refresh the page
        } else {
            echo "Error: " . mysqli_error($con);
        }
    }
?>

<div class="main">
            <!-- Top navigation bar -->
            <?php include './includes/topNavBar.php'; ?>
            <div class="mainpart mt-4 mx-3">
                <h2>Role Form</h2>
                <div class="row pl-3">
                    <form method="post" enctype="multipart/form-data" class='col-md-6' autocomplete="off">
                        <div class="form-row">
                          <div class="col-md-12 mb-3">
                            <label for="roleName" class="font-weight-bold">Role Name</label>
                            <input type="text" class="form-control" id="roleName" placeholder="Enter role" name="roleName" required value="<?php echo isset($_POST['roleName']) ? $_POST['roleName'] : ''; ?>">
                          </div>
                          <div class="col-md-12 mb-3">
                            <label for="roleImage" class="font-weight-bold">Role Image</label>
                            <input type="file" class="form-control" id="roleImage" placeholder="Enter role Image" name="roleImage" required>
                            <?php if (isset($_SESSION['form_data']['roleImage_name'])): ?>
                                <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['roleImage_name']); ?></small>
                            <?php endif; ?>
                          </div>
                        </div>
                        <button class="btn btn-primary" type="submit" name="submit">Add Role</button>
                    </form>
    
                    <table class="table table-bordered table-striped table-hover mt-3 col-md-6">
                      <thead class="th-background">
                       <tr>
                               <th>Sr No.</th>
                               <th>Role Name</th>
                               <th>Role Image</th>
                               <th>Action</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php 
                              include 'connect.php';
                              $query = 'SELECT * FROM role ORDER BY roleId DESC';
                              $result = mysqli_query($con,$query);

                              if(mysqli_num_rows($result)>0){
                                $sr = 1;
                                while($row = mysqli_fetch_assoc($result)){
                                    echo"<tr>
                                            <td>{$sr}</td>
                                            <td>{$row['roleName']}</td>
                                            <td><img src='uploads/{$row['roleImage']}' alt='Role Image' width='80' height='50'></td>
                                            <td class='text-center'>
                                                <div class='d-inline-flex justify-content-center'>
                                                   <a href='updateRole.php?id={$row['roleId']}' class='btn btn-warning btn-sm mr-2'><ion-icon name='create-outline'></ion-icon></a>
                                                   <a href='?deleteId={$row['roleId']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'><ion-icon name='trash-outline'></ion-icon></a>
                                                 </div>
                                             </td>
                                        </tr>";
                                    $sr++;
                                }
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