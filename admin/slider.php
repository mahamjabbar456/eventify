<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
require_once './includes/auth.php';
editorOnly(); // Editor and Admin can access
?>

<?php
    include 'connect.php';
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])){
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_data']['sliderImage_tmp'] = $_FILES['sliderImage']['tmp_name'];
        $_SESSION['form_data']['sliderImage_name'] = $_FILES['sliderImage']['name'];
        $sliderTitle = mysqli_real_escape_string($con,$_POST['sliderTitle']);
        $sliderTag = mysqli_real_escape_string($con,$_POST['sliderTag']);
        $sliderDescription = mysqli_real_escape_string($con,$_POST['sliderDescription']);
        
        $imageName = $_FILES['sliderImage']['name'];
        $imageTmpName = $_FILES['sliderImage']['tmp_name'];
        $imageSize = $_FILES['sliderImage']['size'];
        $imageError = $_FILES['sliderImage']['error'];
        $imageType = $_FILES['sliderImage']['type'];

        $allowedExtensions = ['jpg','jpeg', 'png', 'gif', 'webp'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if(in_array($imageExt, $allowedExtensions)){
            if($imageError === 0){
                if($imageSize < 5000000){
                    $newImageName = uniqid('SLI-',true) . '.' . $imageExt;
                    $imageDestination = "uploads/" . $newImageName;

                    move_uploaded_file($imageTmpName, $imageDestination);

                    // $sql = "INSERT INTO `role`(`roleName`, `roleImage`) VALUES ('[value-2]','[value-3]')";
                    $sql = "INSERT INTO `slider`(`sliderImage`, `sliderTitle`, `sliderTag`,`sliderDescription`) VALUES ('$newImageName','$sliderTitle','$sliderTag','$sliderDescription')";

                    if(mysqli_query($con,$sql)){
                        unset($_SESSION['form_data']);
                        echo "<script>alert('Slider added successfully!');</script>";
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
    }

    // Handle delete request
    if (isset($_GET['deleteId'])) {
        $deleteId = mysqli_real_escape_string($con, $_GET['deleteId']);
        $deleteQuery = "DELETE FROM slider WHERE sliderId='$deleteId'";
        if (mysqli_query($con, $deleteQuery)) {
            echo "<script>alert('Slider deleted successfully!'); window.location='slider.php';</script>";
            // header("Location: slider.php");
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
                <h2>Slider Form</h2>
                <!-- <div class="row pl-3"> -->
                    <form method="post" enctype="multipart/form-data" autocomplete="off">
                        <div class="form-row">
                          <div class="col-md-4 mb-3">
                            <label for="sliderTitle" class="font-weight-bold">Slider Title</label>
                            <input type="text" class="form-control" id="sliderTitle" placeholder="Enter slider title" name="sliderTitle" required value="<?php echo isset($_POST['sliderTitle']) ? $_POST['sliderTitle'] : ''; ?>">
                          </div>
                          <div class="col-md-4 mb-3">
                            <label for="sliderTag" class="font-weight-bold">Slider Tag</label>
                            <input type="text" class="form-control" id="sliderTag" placeholder="Enter slider tag" name="sliderTag" required value="<?php echo isset($_POST['sliderTag']) ? $_POST['sliderTag'] : ''; ?>">
                          </div>
                          <div class="col-md-4 mb-3">
                            <label for="sliderImage" class="font-weight-bold">Slider Image</label>
                            <input type="file" class="form-control" id="sliderImage" placeholder="Enter role Image" name="sliderImage" required>
                            <?php if (isset($_SESSION['form_data']['sliderImage_name'])): ?>
                                    <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['sliderImage_name']); ?></small>
                            <?php endif; ?>
                          </div>
                          <div class="col-md-12 mb-3">
                            <label for="sliderDescription" class="font-weight-bold">Slider Description</label>
                            <textarea name="sliderDescription" id="sliderDescription" rows="3" placeholder="Enter slider description" class="form-control"><?php echo isset($_POST['sliderDescription']) ? $_POST['sliderDescription'] : ''; ?></textarea>
                          </div>
                          
                        </div>
                        <button class="btn btn-primary" type="submit" name="submit">Add Slider</button>
                      </form>
    
                      <table class="table table-bordered table-striped table-hover mt-3 col-md-6">
                      <thead class="th-background">
                       <tr>
                               <th>Sr No.</th>
                               <th>Slider Name</th>
                               <th>Slider Tag</th>
                               <th>Slider Description</th>
                               <th>Slider Image</th>
                               <th>Action</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php 
                              include 'connect.php';
                              $query = 'SELECT * FROM slider ORDER BY sliderId DESC';
                              $result = mysqli_query($con,$query);

                              if(mysqli_num_rows($result)>0){
                                $sr = 1;
                                while($row = mysqli_fetch_assoc($result)){
                                    // <td>{$row['sliderDescription']}</td>
                                    echo"<tr>
                                            <td>{$sr}</td>
                                            <td>{$row['sliderTitle']}</td>
                                            <td>{$row['sliderTag']}</td>
                                            <td>".nl2br(htmlspecialchars($row['sliderDescription']))."</td>
                                            <td><img src='uploads/{$row['sliderImage']}' alt='Role Image' width='80' height='50'></td>
                                            <td class='text-center'>
                                                <div class='d-inline-flex justify-content-center'>
                                                   <a href='updateSlider.php?id={$row['sliderId']}' class='btn btn-warning btn-sm mr-2'><ion-icon name='create-outline'></ion-icon></a>
                                                   <a href='?deleteId={$row['sliderId']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'><ion-icon name='trash-outline'></ion-icon></a>
                                                 </div>
                                             </td>
                                        </tr>";
                                    $sr++;
                                }
                              }
                           ?>
                       </tbody>
                   </table>
                <!-- </div> -->
            </div>
</div>
<!-- container -->
</div>
<!-- mainContainer -->
</div>

<?php include './includes/footer.php'; ?>