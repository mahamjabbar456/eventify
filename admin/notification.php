<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../phpmail/src/Exception.php';
require_once '../phpmail/src/PHPMailer.php';
require_once '../phpmail/src/SMTP.php';

include 'connect.php';  
$currentUserRole = htmlspecialchars($_SESSION['roleName']);
$currentUserId = $_SESSION['userId'];

// Determine which user type to show
$userType = isset($_GET['user_type']) ? $_GET['user_type'] : '';

// Handle form submission
if(isset($_POST['submit'])) {
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $message = mysqli_real_escape_string($con, $_POST['message']);
    $selectedUsers = isset($_POST['selected_users']) ? $_POST['selected_users'] : [];
    
    if(!empty($selectedUsers)) {
        $successCount = 0;
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'eventifywebsite012@gmail.com';
            $mail->Password   = 'bfni fpwv rbdl dmkc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom('eventifywebsite012@gmail.com', 'Eventify');
            $mail->isHTML(true);
            
            // Determine message type based on content
            $messageType = determineMessageType($subject, $message);
            
            foreach($selectedUsers as $userId) {
                // Get user details
                $userQuery = "(SELECT userId, username, email, roleName FROM user JOIN role ON user.roleId = role.roleId WHERE userId = '$userId')
                             UNION
                             (SELECT customerId as userId, name as username, email, 'Customer' as roleName FROM customer WHERE customerId = '$userId')";
                $userResult = mysqli_query($con, $userQuery);
                
                if(mysqli_num_rows($userResult) > 0) {
                    $user = mysqli_fetch_assoc($userResult);
                    
                    // Prepare email
                    $mail->addAddress($user['email'], $user['username']);
                    $mail->Subject = $subject;
                    $mail->Body = generateEmailTemplate($user['roleName'], $messageType, $subject, $message, $user['username']);
                    
                    if($mail->send()) {
                        $successCount++;
                    }
                    
                    $mail->clearAddresses();
                }
            }
            
            echo '<div class="alert alert-success">Email sent to '.$successCount.' users successfully!</div>';
            echo '<script>document.getElementById("messageForm").reset();</script>';
            $_POST = [];
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Message could not be sent. Mailer Error: '.$mail->ErrorInfo.'</div>';
        }
    } else {
        echo '<div class="alert alert-warning">Please select at least one user.</div>';
    }
}

// Determine if message is a problem or notification
function determineMessageType($subject, $message) {
    $problemKeywords = ['problem', 'issue', 'error', 'urgent', 'fix', 'broken', 'trouble', 'help', 'support'];
    $subjectLower = strtolower($subject);
    $messageLower = strtolower($message);
    
    foreach ($problemKeywords as $keyword) {
        if (strpos($subjectLower, $keyword) !== false || strpos($messageLower, $keyword) !== false) {
            return 'problem';
        }
    }
    
    return 'notification';
}

// Email template generator function
function generateEmailTemplate($userType, $messageType, $subject, $message, $username) {
    // Determine colors based on message type
    $colors = [
        'problem' => [
            'admin' => '#dc3545', // Red
            'hall owner' => '#dc3545', // Red
            'editor' => '#dc3545', // Red
            'customer' => '#dc3545' // Red
        ],
        'notification' => [
            'admin' => '#e9ecef', // Dark gray
            'hall owner' => '#e9ecef', // Teal
            'editor' => '#e9ecef', // Purple
            'customer' => '#e9ecef' // Green
        ]
    ];
    
    $color = $colors[$messageType][strtolower($userType)];
    $icon = $messageType === 'problem' ? 'warning-outline' : 'information-circle-outline';
    $originalMessage = str_replace(['\r\n', '\n', '\r','\\'], '<br>', htmlspecialchars($message));
    
    return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid $color;'>
                <h2 style='color: #000;'><ion-icon name='$icon'></ion-icon> $subject</h2>
                <p>Hello <strong>$username</strong>,</p>
                <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    $originalMessage
                </div>
                <p style='font-size: 0.9em; color: #6c757d;'>
                    " . getFooterText($userType, $messageType) . "
                </p>
                <div style='margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; text-align: center;'>
                    <small>" . getTeamName($userType) . "</small>
                </div>
            </div>
        </div>
    ";
}

function getFooterText($userType, $messageType) {
    if ($messageType === 'problem') {
        return "This message requires your urgent attention. Please take appropriate action.";
    }
    
    switch(strtolower($userType)) {
        case 'admin':
            return "This is an important system notification.";
        case 'hall owner':
            return "Hall Owner Notification - Please check your dashboard for more details.";
        case 'editor':
            return "Content Editor Notification - Please review this information for your content updates.";
        case 'customer':
        default:
            return "Thank you for choosing Eventify for your event needs.";
    }
}

function getTeamName($userType) {
    switch(strtolower($userType)) {
        case 'admin':
            return "Eventify Administration Team";
        case 'hall owner':
            return "Eventify Hall Management";
        case 'editor':
            return "Eventify Content Team";
        case 'customer':
        default:
            return "Eventify Customer Support";
    }
}
?>

<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Notification System</h2>
        <form method="post" enctype="multipart/form-data" id="messageForm" autocomplete="off">
        <div class="row">
            <!-- Left side - Form -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="subject" class="font-weight-bold">Subject</label>
                    <input type="text" class="form-control" id="subject" placeholder="Enter Your Subject"
                        name="subject" required value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="message" class="font-weight-bold">Message</label>
                    <textarea name="message" id="message" placeholder="Enter your message" rows="5" class="form-control" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                
                <button class="btn btn-primary" type="submit" name='submit'>
                    <ion-icon name="send-outline"></ion-icon> Send Message
                </button>
                
                <?php if($currentUserRole == 'Admin' || $currentUserRole == 'Editor'): ?>
                <div class="btn-group mt-2" role="group">
                    <button type="filter" name="user_type" value="all" class="btn btn-dark">
                        <ion-icon name="people-circle-outline"></ion-icon> All Users
                    </button>
                    <button type="filter" name="user_type" value="hall_owners" class="btn btn-info">
                        <ion-icon name="business-outline"></ion-icon> Hall Owners
                    </button>
                    <button type="filter" name="user_type" value="customers" class="btn btn-secondary">
                        <ion-icon name="people-outline"></ion-icon> Customers
                    </button>
                </div>
                <?php elseif($currentUserRole == 'Hall Owner'): ?>
                <div class="btn-group mt-2" role="group">
                    <button type="filter" name="user_type" value="customers" class="btn btn-secondary">
                        <ion-icon name="people-outline"></ion-icon> My Customers
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Hidden fields to preserve form data -->
                <input type="hidden" name="preserve_subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                <input type="hidden" name="preserve_message" value="<?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?>">
            </div>
            
            <!-- Right side - User List -->
            <?php 
            // Determine which user type to show (from POST or GET)
            $userType = isset($_POST['user_type']) ? $_POST['user_type'] : (isset($_GET['user_type']) ? $_GET['user_type'] : '');
            
            // Restore form data if coming from a filter click
            if(isset($_POST['preserve_subject']) && empty($_POST['subject'])) {
                $_POST['subject'] = $_POST['preserve_subject'];
            }
            if(isset($_POST['preserve_message']) && empty($_POST['message'])) {
                $_POST['message'] = $_POST['preserve_message'];
            }
            
            if($userType): ?>
            <div class="col-md-6 mt-4 md-mt-0" id="userListContainer">
                <div class="card">
                    <div class="card-header th-background d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?php 
                            echo $userType === 'hall_owners' ? 'Hall Owners' : 
                                 ($userType === 'editors' ? 'Editors' :
                                 ($userType === 'customers' ? ($currentUserRole == 'Hall Owner' ? 'My Customers' : 'Customers') : 'All Users'));
                            ?>
                        </h5>
                        <button type="filter" name="user_type" value="" class="btn btn-sm btn-light">
                            <ion-icon name="close-outline"></ion-icon>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <input type="text" class="form-control" id="userSearch" placeholder="Search users by email..." onkeyup="searchUsers()">
                        </div>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped table-hover">
                                <thead class="th-background sticky-top">
                                    <tr class="th-background">
                                        <th width="50px">
                                            <input type="checkbox" id="selectAllUsers" onclick="toggleSelectAll()">
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody id="userListBody">
                                    <?php
                                    // Query based on selected user type and current user role
                                    if($userType === 'hall_owners' && ($currentUserRole == 'Admin' || $currentUserRole == 'Editor')) {
    $query = "SELECT u.userId, u.username, u.email, r.roleName 
             FROM user u 
             JOIN role r ON u.roleId = r.roleId 
             WHERE r.roleName = 'Hall Owner'";
} 
elseif($userType === 'editors' && ($currentUserRole == 'Admin' || $currentUserRole == 'Editor')) {
    $query = "SELECT u.userId, u.username, u.email, r.roleName 
             FROM user u 
             JOIN role r ON u.roleId = r.roleId 
             WHERE r.roleName = 'Editor'";
}
elseif($userType === 'customers') {
    if($currentUserRole == 'Hall Owner') {
        // Get hall IDs assigned to this hall owner
        $hallQuery = "SELECT hallId FROM assignhall WHERE userId = '$currentUserId'";
        $hallResult = mysqli_query($con, $hallQuery);
        
        $hallIds = array();
        while($row = mysqli_fetch_assoc($hallResult)) {
            $hallIds[] = $row['hallId'];
        }
        
        if(!empty($hallIds)) {
            $hallIdList = implode(",", $hallIds);
            $query = "SELECT DISTINCT c.customerId as userId, c.name as username, c.email, 'Customer' as roleName 
                     FROM customer c
                     JOIN booking b ON c.customerId = b.customerId
                     WHERE b.hallId IN ($hallIdList)";
        } else {
            $query = "SELECT NULL as userId, NULL as username, NULL as email, NULL as roleName 
                     WHERE 1=0";
        }
    } else {
        // Both Admin and Editor see all customers
        $query = "SELECT customerId as userId, name as username, email, 'Customer' as roleName 
                 FROM customer";
    }
}
elseif($userType === 'all' && ($currentUserRole == 'Admin' || $currentUserRole == 'Editor')) {
    // Combined query for all users (both admin and editor)
    $query = "(SELECT u.userId, u.username, u.email, r.roleName 
              FROM user u 
              JOIN role r ON u.roleId = r.roleId)
              UNION
              (SELECT customerId as userId, name as username, email, 'Customer' as roleName 
              FROM customer)";
}
elseif($userType === 'all_roles' && ($currentUserRole == 'Admin' || $currentUserRole == 'Editor')) {
    // Show all roles including editors
    $query = "(SELECT u.userId, u.username, u.email, r.roleName 
              FROM user u 
              JOIN role r ON u.roleId = r.roleId
              WHERE r.roleName IN ('Admin', 'Editor', 'Hall Owner'))
              UNION
              (SELECT customerId as userId, name as username, email, 'Customer' as roleName 
              FROM customer)";
}
                                    
                                    if(isset($query)) {
                                        $result = mysqli_query($con, $query);
                                        
                                        if(mysqli_num_rows($result) > 0) {
                                            while($user = mysqli_fetch_assoc($result)) {
                                                echo '<tr>';
                                                echo '<td><input type="checkbox" class="user-checkbox" name="selected_users[]" value="'.$user['userId'].'"></td>';
                                                echo '<td>'.$user['username'].'</td>';
                                                echo '<td>'.$user['email'].'</td>';
                                                echo '<td>'.$user['roleName'].'</td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center">No users found</td></tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        </form>
    </div>
</div>

<script>
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const selectAll = document.getElementById('selectAllUsers');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function searchUsers() {
    const input = document.getElementById('userSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#userListBody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (name.includes(filter) || email.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include './includes/footer.php'; ?>