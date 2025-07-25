<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
require_once './includes/auth.php';
adminOnly(); 
?>

<?php
    // session_start();
    include 'connect.php';

    $rolesQuery = "SELECT * FROM role";
    $roleResult = mysqli_query($con,$rolesQuery);

    $roles = [];
    while($row = mysqli_fetch_assoc($roleResult)){
        $roles[] = $row;
    }

    $defaultRow = !empty($roles) ? $roles[0]['roleId'] : '';
    $error = "";

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])){
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_data']['userImage_name'] = $_FILES['userImage']['name'];
        $name=mysqli_real_escape_string($con, $_POST['username']);
        $email=mysqli_real_escape_string($con, $_POST['email']);
        $password=mysqli_real_escape_string($con, $_POST['password']);
        $cnic=mysqli_real_escape_string($con, str_replace('-', '', $_POST['cnic']));
        $dateOfBirth=mysqli_real_escape_string($con, $_POST['dateOfBirth']);
        $address=mysqli_real_escape_string($con, $_POST['address']);
        $role=mysqli_real_escape_string($con, $_POST['role']);
        $phoneNo = mysqli_real_escape_string($con, str_replace('-', '', $_POST['phoneNo']));

        $today = new DateTime();
        $birthDate = new DateTime($dateOfBirth);
        $age = $today->diff($birthDate)->y;

        if ($age < 18) {
            echo "<script>alert('User must be at least 18 years old!');</script>";
            exit();
        }

        $checkQuery = "SELECT * FROM user WHERE email='$email' OR cnic='$cnic'";
        $checkResult = mysqli_query($con, $checkQuery);

        if(mysqli_num_rows($checkResult) > 0){
        echo "<script>alert('User already exists with this Email or CNIC!');</script>";
        }else{ 

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
            $imageName = $_FILES['userImage']['name'];
            $imageTmpName = $_FILES['userImage']['tmp_name'];
            $imageError = $_FILES['userImage']['error'];
            $imageSize = $_FILES['userImage']['size'];
            $imageType = $_FILES['userImage']['type'];

            $allowedExtensions = ['jpg','jpeg', 'png', 'gif', 'webp'];
            $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if(in_array($imageExt, $allowedExtensions)){
                if($imageError === 0){
                    if($imageSize < 5000000){
                        $newImageName = uniqid('IMG-',true) . '.' . $imageExt;
                        $imageDestination = "uploads/" . $newImageName;

                        move_uploaded_file($imageTmpName, $imageDestination);

                        $sql = "INSERT INTO user(username,email,password,phone,address,dateOfBirth,roleId,cnic,image)     VALUES ('$name','$email','$hashedPassword','$phoneNo','$address','$dateOfBirth','$role','$cnic', '$newImageName')";

                        if(mysqli_query($con,$sql)){
                            unset($_SESSION['form_data']);
                            echo "<script>alert('User registered successfully!');</script>";
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
        $deleteQuery = "DELETE FROM user WHERE userId='$deleteId'";
        if (mysqli_query($con, $deleteQuery)) {
            echo "<script>alert('User deleted successfully!');window.location='user.php';</script>";
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
                <h2>User Form</h2>
                <form id="userForm" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="username" class="font-weight-bold">Full Name</label>
                            <input type="text" class="form-control" id="username" placeholder="Enter full Name"
                                name="username" required value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="font-weight-bold">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter your email"
                                name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="userPassword" class="font-weight-bold">Password</label>
                            <input type="password" class="form-control" id="userPassword" placeholder="Enter your password"
                                name="password" required value="<?php echo isset($_POST['password']) ? $_POST['password'] : ''; ?>">
                            <ion-icon name="eye" class="togglePassword"></ion-icon>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cnic" class="font-weight-bold">CNIC</label>
                            <input type="text" class="form-control" id="cnic" placeholder="Enter your CNIC"
                                name="cnic" required value="<?php echo isset($_POST['cnic']) ? $_POST['cnic'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="dateOfBirth" class="font-weight-bold">Date Of Birth</label>
                            <input type="date" class="form-control" id="dateOfBirth"
                                placeholder="Enter your Date of Birth" name="dateOfBirth" required value="<?php echo isset($_POST['dateOfBirth']) ? $_POST['dateOfBirth'] : ''; ?>" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="address" class="font-weight-bold">Address</label>
                            <input type="text" class="form-control" id="address" placeholder="Enter your Address"
                                name="address" required value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="role" class="font-weight-bold">Role</label>
                            <select class="form-control" id="role" name="role" required >
                                <option value="" disabled selected>Select a role</option>
                                <?php
                                    foreach($roles as $role){
                                        // $selected = ($role['roleId'] == $defaultRow) ? "selected" : "";
                                        $selected = (isset($_POST['role']) && $_POST['role'] == $role['roleId']) ? "selected" : "";
                                        echo "<option value='{$role['roleId']}' $selected>{$role['roleName']}</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="phoneNo" class="font-weight-bold">Phone No</label>
                            <input type="tel" class="form-control" id="phoneNo" placeholder="Enter your Phone No"
                                name="phoneNo" required value="<?php echo isset($_POST['phoneNo']) ? $_POST['phoneNo'] : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="userImage" class="font-weight-bold">Image</label>
                            <input type="file" class="form-control" id="userImage" placeholder="Enter your Image" name="userImage" required>
                            <?php if (isset($_SESSION['form_data']['userImage_name'])): ?>
                                <small class="text-muted">Previously selected: <?php echo htmlspecialchars($_SESSION['form_data']['userImage_name']); ?></small>
                            <?php endif; ?>
                        </div>

                    </div>
                    <button class="btn btn-primary" type="submit" name='submit'>Add User</button>
                </form>

                <div class="table-responsive" style="width: 100%; overflow-x: auto;">
                <table id="myTable" class="table table-striped table-bordered table-hover">
                    <thead class="th-background">
                        <tr class="th-background">
                            <th>Sr No.</th>
                            <th>FullName</th>
                            <th>Email</th>
                            <th>Phone No</th>
                            <th>Address</th>
                            <th>Date of Birth</th>
                            <th>Role</th>
                            <th>CNIC</th>
                            <th>User Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                        <tbody>
                            <?php
                            include 'connect.php';

                            // Step 1: Fetch all roles and create a mapping array
                            $rolesQuery = "SELECT * FROM role";
                            $rolesResult = mysqli_query($con, $rolesQuery);
    
                            $roleMapping = [];
                            while ($roleRow = mysqli_fetch_assoc($rolesResult)) {
                                $roleMapping[$roleRow['roleId']] = $roleRow['roleName'];
                            }
    
                            // Step 2: Fetch users normally
                            $query = "SELECT * FROM user ORDER BY userId DESC";
                            $result = mysqli_query($con, $query);
                            if (mysqli_num_rows($result) > 0) {
                                $sr = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Step 3: Replace roleId with roleName using the mapping array
                                    $roleName = isset($roleMapping[$row['roleId']]) ? $roleMapping[$row['roleId']] : "Unknown Role";

                                    echo "<tr>
                                             <td>{$sr}</td>
                                             <td>{$row['username']}</td>
                                             <td>{$row['email']}</td>
                                             <td>{$row['phone']}</td>
                                             <td>{$row['address']}</td>
                                             <td>{$row['dateOfBirth']}</td>
                                             <td>{$roleName}</td>
                                             <td>{$row['cnic']}</td>
                                             <td><img src='uploads/{$row['image']}' width='50' height='50'></td>
                                             <td class='text-center'>
                                                <div class='d-inline-flex justify-content-center'>
                                                   <a href='updateUser.php?id={$row['userId']}' class='btn btn-warning btn-sm mr-2'><ion-icon name='create-outline'></ion-icon></a>
                                                   <a href='?deleteId={$row['userId']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'><ion-icon name='trash-outline'></ion-icon></a>
                                                </div>
                                             </td>
                                         </tr>";
                                    $sr++;
                                }
                            } else {
                                echo "<tr><td colspan='11' class='text-center'>No Users Found</td></tr>";
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
     function isOver18(birthDate) {
        const today = new Date();
        const birthDateObj = new Date(birthDate);
        let age = today.getFullYear() - birthDateObj.getFullYear();
        const monthDiff = today.getMonth() - birthDateObj.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDateObj.getDate())) {
            age--;
        }
        
        return age >= 18;
    }

    // Validate on form submission
    $('#userForm').on('submit', function(e) {
        const dobInput = $('#dateOfBirth').val();
        
        if (dobInput && !isOver18(dobInput)) {
            alert('User must be at least 18 years old!');
            e.preventDefault();
            return false;
        }
    });

    // Validate on date change (optional)
    $('#dateOfBirth').on('change', function() {
        const dobInput = $(this).val();
        
        if (dobInput && !isOver18(dobInput)) {
            alert('User must be at least 18 years old!');
            $(this).val(''); // Clear the field
        }
    });
    // Phone number masking
    $('input[name="phoneNo"]').inputmask({
        mask: '0399-9999999',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
    $('input[name="cnic"]').inputmask({
        mask: '99999-9999999-9',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });

    const togglePassword = document.querySelector('.togglePassword');
    const passwordInput = document.getElementById('userPassword');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            const iconName = this.getAttribute('name') === 'eye' ? 'eye-off' : 'eye';
            this.setAttribute('name', iconName);
        });
    }
});
</script>

<?php include './includes/footer.php'; ?>