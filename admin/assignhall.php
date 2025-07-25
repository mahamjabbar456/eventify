<?php 
include './includes/header.php';
include './includes/sidebar.php';
include 'connect.php';

require_once './includes/auth.php';
adminOnly(); // Editor and Admin can access

// Fetch all halls from database
$hallQuery = "SELECT * FROM hall WHERE status = 'active' ORDER BY name ASC";
$hallResult = mysqli_query($con, $hallQuery);

// Fetch all hall owners (users with role 'hallOwner')
$userQuery = "SELECT * FROM user WHERE roleId = (SELECT roleId FROM role WHERE roleName = 'Hall Owner') ORDER BY username ASC";
$userResult = mysqli_query($con, $userQuery);

// Fetch all assigned halls with user information
$assignQuery = "SELECT ah.assignId as assignId, h.id as hallId, h.name as hallName, 
                u.userId, u.username as ownerName
                FROM assignhall ah
                JOIN hall h ON ah.hallId = h.id
                JOIN user u ON ah.userId = u.userId
                ORDER BY h.name ASC";
$assignResult = mysqli_query($con, $assignQuery);
?>

<div class="main" style="overflow-x:hidden;">
    <!-- Top navigation bar -->
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        
        <?php
        // Check if form is submitted
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(isset($_POST['assign'])) {
                $hallId = $_POST['hall'];
                $userId = $_POST['user'];
                
                // Check if assignment already exists
                $checkQuery = "SELECT * FROM assignhall WHERE hallId = $hallId AND userId = $userId";
                $checkResult = mysqli_query($con, $checkQuery);
                
                if(mysqli_num_rows($checkResult) == 0) {
                    // Insert new assignment
                    $insertQuery = "INSERT INTO assignhall (hallId, userId) VALUES ($hallId, $userId)";
                    if(mysqli_query($con, $insertQuery)) {
                        echo '<div class="alert alert-success">Hall assigned successfully!</div>';
                        // Refresh the page to show updated assignments
                        echo '<script>setTimeout(function(){ window.location = window.location; }, 1000);</script>';
                    } else {
                        echo '<div class="alert alert-danger">Error assigning hall: '.mysqli_error($con).'</div>';
                    }
                } else {
                    echo '<div class="alert alert-warning">This hall is already assigned to this user.</div>';
                }
            }
            
            if(isset($_POST['delete'])) {
                $assignId = $_POST['assignId'];
                $deleteQuery = "DELETE FROM assignhall WHERE assignId = $assignId";
                if(mysqli_query($con, $deleteQuery)) {
                    echo '<div class="alert alert-success">Assignment removed successfully!</div>';
                    // Refresh the page to show updated assignments
                    echo '<script>setTimeout(function(){ window.location = window.location; }, 1000);</script>';
                } else {
                    echo '<div class="alert alert-danger">Error removing assignment: '.mysqli_error($con).'</div>';
                }
            }
        }
        ?>
        
        <!-- Show All halls -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h4 class="m-0 headingColor">Assign New Hall</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="th-background">
                                            <tr>
                                                <th colspan="2" class="text-center">Available Halls</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($hallResult) > 0): ?>
                                                <?php while($hall = mysqli_fetch_assoc($hallResult)): ?>
                                                    <tr>
                                                        <td style="width: 40px;">
                                                            <input type="radio" id="hall<?php echo $hall['id']; ?>" 
                                                                   name="hall" value="<?php echo $hall['id']; ?>" required />
                                                        </td>
                                                        <td>
                                                            <label for="hall<?php echo $hall['id']; ?>" class="form-check-label font-weight-bold">
                                                                <?php echo htmlspecialchars($hall['name']); ?>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No halls available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="th-background">
                                            <tr>
                                                <th colspan="2" class="text-center">Hall Owners</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($userResult) > 0): ?>
                                                <?php while($user = mysqli_fetch_assoc($userResult)): ?>
                                                    <tr>
                                                        <td style="width: 40px;">
                                                            <input type="radio" id="user<?php echo $user['userId']; ?>" 
                                                                   name="user" value="<?php echo $user['userId']; ?>" required />
                                                        </td>
                                                        <td>
                                                            <label for="user<?php echo $user['userId']; ?>" class="form-check-label font-weight-bold">
                                                                <?php echo htmlspecialchars($user['username']); ?>
                                                            </label>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No hall owners available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-right">
                                <button type="submit" name="assign" class="btn btn-primary">
                                    <i class="fas fa-link"></i> Assign Hall
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
         
        <!-- Show assign hall -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h4 class="m-0 headingColor">Current Assignments</h4>
            </div>
            <div class="card-body">
                <?php if(mysqli_num_rows($assignResult) > 0): ?>
                    <div class="table-responsive" >
                        <table id="assignTable" class="table table-striped table-bordered table-hover">
                            <thead class="th-background">
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Hall Name</th>
                                    <th>Assigned Owner</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sr = 1;
                                while($assign = mysqli_fetch_assoc($assignResult)): ?>
                                    <tr>
                                        <td><?php echo $sr; ?></td>
                                        <td><?php echo htmlspecialchars($assign['hallName']); ?></td>
                                        <td><?php echo htmlspecialchars($assign['ownerName']); ?></td>
                                        <td class="text-center">
                                            <form method="POST">
                                                <input type="hidden" name="assignId" value="<?php echo $assign['assignId']; ?>">
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Are you sure you want to remove this assignment?');">
                                                    <i class="fas fa-trash-alt"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php $sr++; ?>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No hall assignments found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script>
    $(document).ready(function() {
        $('#assignTable').DataTable({
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                { "orderable": false, "targets": [3] } // Disable sorting for action column
            ]
        });
    });
</script>

<?php include './includes/footer.php'; ?>