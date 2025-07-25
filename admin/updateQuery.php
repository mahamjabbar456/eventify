<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
    include 'connect.php';
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Load PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require '../phpmail/src/Exception.php';
    require '../phpmail/src/PHPMailer.php';
    require '../phpmail/src/SMTP.php';

    // Check if the 'id' parameter is set
    if (isset($_GET['id'])) {
        $queryId = mysqli_real_escape_string($con, $_GET['id']);
        $query = "SELECT * FROM query WHERE queryId = '$queryId'";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $queryId = $row['queryId'];
            $customerEmail = $row['email'];
            $customerName = $row['name'];
            $originalSubject = $row['subject'];
            $originalMessage = $row['message'];
        } else {
            echo "<script>alert('Query not found!'); window.location='query.php';</script>";
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
        $queryId = mysqli_real_escape_string($con, $_POST['queryId']);
        $queryReply = mysqli_real_escape_string($con, $_POST['queryReply']);

        // Get query details
        $queryDetails = "SELECT * FROM query WHERE queryId = '$queryId'";
        $result = mysqli_query($con, $queryDetails);
        $row = mysqli_fetch_assoc($result);
        
        $customerEmail = $row['email'];
        $customerName = $row['name'];
        $originalSubject = $row['subject'];
        $originalMessage = $row['message'];

        // Determine response type
        $responseType = detectResponseType($originalSubject, $originalMessage);

        // Update query in database
        $querySql = "UPDATE query SET queryReply = '$queryReply' WHERE queryId = '$queryId'";

        if (mysqli_query($con, $querySql)) {
            // Send email
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = "eventifywebsite012@gmail.com";
                $mail->Password = "bfni fpwv rbdl dmkc";
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Recipients
                $mail->setFrom("eventifywebsite012@gmail.com", "Eventify Team");
                $mail->addAddress($customerEmail, $customerName);
                
                // Build email based on response type
                switch ($responseType) {
                    case 'compliment':
                        $mail->Subject = "Thank You for Your Kind Words!";
                        $mail->Body = buildComplimentEmail($customerName, $originalMessage, $queryReply);
                        break;
                        
                    case 'suggestion':
                        $mail->Subject = "Thanks for Your Valuable Suggestion!";
                        $mail->Body = buildSuggestionEmail($customerName, $originalMessage, $queryReply);
                        break;
                        
                    default:
                        $mail->Subject = "Response to Your Query #{$queryId}";
                        $mail->Body = buildStandardEmail($customerName, $originalMessage, $queryReply, $queryId);
                }
                
                $mail->AltBody = strip_tags($mail->Body);
                $mail->send();
                
                echo "<script>
                    alert('Reply sent successfully!');
                    window.location.href = 'query.php';
                </script>";
            } catch (Exception $e) {
                echo "<script>
                    alert('Reply saved but email failed: " . addslashes($mail->ErrorInfo) . "');
                    window.location.href = 'query.php';
                </script>";
            }
        } else {
            echo "<script>alert('Error saving reply: " . addslashes(mysqli_error($con)) . "');</script>";
        }
    }

    // ======================
    // HELPER FUNCTIONS
    // ======================
    
    /**
     * Detects response type by analyzing both subject and message
     */
    function detectResponseType($subject, $message) {
        $subject = strtolower($subject);
        $message = strtolower($message);
        
        // Compliment detection (priority)
        $complimentTriggers = [
            'thank', 'appreciate', 'great', 'awesome', 
            'excellent', 'love', 'wonderful', 'perfect'
        ];
        
        foreach ($complimentTriggers as $word) {
            if (strpos($subject, $word) !== false || strpos($message, $word) !== false) {
                return 'compliment';
            }
        }
        
        // Suggestion detection
        $suggestionTriggers = [
            'suggest', 'recommend', 'improve', 'better',
            'idea', 'advice', 'feedback', 'how about'
        ];
        
        foreach ($suggestionTriggers as $word) {
            if (strpos($subject, $word) !== false || strpos($message, $word) !== false) {
                return 'suggestion';
            }
        }
        
        return 'standard';
    }
    
    /**
     * Email template for compliments
     */
    function buildComplimentEmail($name, $originalMsg, $reply) {
        $formattedOriginalMsg = str_replace(['\r\n', '\n', '\r','\\'], '<br>', htmlspecialchars($originalMsg));
        $formattedReply = str_replace(['\r\n', '\n', '\r','\\'], '<br>', htmlspecialchars($reply));
        return "
            <div style='font-family: Arial; max-width: 600px; margin: auto; border: 1px solid #eee;'>
                <div style='background: #4CAF50; color: white; padding: 20px; text-align: center;'>
                    <h2>We Appreciate You!</h2>
                </div>
                
                <div style='padding: 20px;'>
                    <p>Dear $name,</p>
                    <p>Your kind words made our day! Here's our response:</p>
                    
                    <div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <p><strong>Your Message:</strong></p>
                        <p>$formattedOriginalMsg</p>
                    </div>
                    
                    <div style='background: #e8f5e9; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <p><strong>Our Response:</strong></p>
                        <p>$formattedReply</p>
                    </div>
                    
                    <p>We look forward to serving you again!</p>
                    <p>Warm regards,<br><strong>The Eventify Team</strong></p>
                </div>
            </div>
        ";
    }
    
    /**
     * Email template for suggestions
     */
    function buildSuggestionEmail($name, $originalMsg, $reply) {
        $formattedOriginalMsg = str_replace(['\r\n', '\n', '\r','\\'], '<br>', htmlspecialchars($originalMsg));
        $formattedReply = str_replace(['\r\n', '\n', '\r','\\'], '<br>', htmlspecialchars($reply));
        return "
            <div style='font-family: Arial; max-width: 600px; margin: auto; border: 1px solid #eee;'>
                <div style='background: #2196F3; color: white; padding: 20px; text-align: center;'>
                    <h2>Thanks for Your Idea!</h2>
                </div>
                
                <div style='padding: 20px;'>
                    <p>Hi $name,</p>
                    <p>We value customers who help us improve. Here's our response:</p>
                    
                    <div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <p><strong>Your Suggestion:</strong></p>
                        <p>$formattedOriginalMsg</p>
                    </div>
                    
                    <div style='background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <p><strong>Our Response:</strong></p>
                        <p>$formattedReply</p>
                    </div>
                    
                    <p>We'll seriously consider your input!</p>
                    <p>Best regards,<br><strong>The Eventify Team</strong></p>
                </div>
            </div>
        ";
    }
    
    /**
     * Default email template
     */
    function buildStandardEmail($name, $originalMsg, $reply, $queryId) {
        $formattedOriginalMsg = str_replace(['\r\n', '\n', '\r','\\'], '<br>', htmlspecialchars($originalMsg));
        $formattedReply = str_replace(['\r\n', '\n', '\r','\\'], '<br>', htmlspecialchars($reply));
        return "
            <div style='font-family: Arial; max-width: 600px; margin: auto; border: 1px solid #eee;'>
                <div style='background: #673AB7; color: white; padding: 20px; text-align: center;'>
                    <h2>Your Query #$queryId</h2>
                </div>
                
                <div style='padding: 20px;'>
                    <p>Dear $name,</p>
                    <p>Thank you for contacting us. Here's our response:</p>
                    
                    <div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <p><strong>Your Query:</strong></p>
                        <p>$formattedOriginalMsg</p>
                    </div>
                    
                    <div style='background: #EDE7F6; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                        <p><strong>Our Response:</strong></p>
                        <p>$formattedReply</p>
                    </div>
                    
                    <p>Let us know if you need anything else.</p>
                    <p>Best regards,<br><strong>The Eventify Team</strong></p>
                </div>
            </div>
        ";
    }
?>

<!-- HTML FORM (unchanged from your original) -->
<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mainpart mt-4 mx-3">
        <h2>Add Query Reply</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="queryId" value="<?= $queryId ?>">
            
            <div class="col-md-12 mb-3">
                <label for="queryReply" class="font-weight-bold">Query Reply</label>
                <textarea class="form-control" id="queryReply" name="queryReply" rows="3" required placeholder="Write your query reply"><?php 
                    echo isset($_POST['queryReply']) ? htmlspecialchars($_POST['queryReply']) : ''; 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="reply">Send Reply</button>
                <a href="query.php" class="ml-2 btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include './includes/footer.php'; ?>