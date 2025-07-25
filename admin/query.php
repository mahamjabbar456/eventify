<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>

<?php
require_once './includes/auth.php';
editorOnly(); // Editor and Admin can access
?>

<div class="main" style="overflow-x:hidden;">
            <!-- Top navigation bar -->
            <?php include './includes/topNavBar.php'; ?>
            <div class="mainpart mt-4 mx-3">
                <h2>All Queries</h2>
                
                <div class="table-responsive" style="width: 100%; overflow-x: auto;">
                <table id="myTable" class="table table-striped table-bordered">
                    <thead class="th-background">
                        <tr class="th-background">
                            <th>Sr No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone No</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Query Reply</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include 'connect.php';
        
                        // Query to get all bookings with hall and payment details
                        $query = "SELECT * FROM query
                                ORDER BY queryId DESC";
        
                        $result = mysqli_query($con, $query);
                        $sr = 1;
        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                
                                // {$row['queryReply']}
                                // {$row['message']}
                                echo "<tr>
                                    <td>{$sr}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['email']}</td>
                                    <td>
                                        {$row['phoneNo']}
                                    </td>
                                    <td>
                                        {$row['subject']}
                                    </td>
                                    <td>
                                        ".nl2br(htmlspecialchars($row['message']))."
                                    </td>
                                    <td>
                                        ".nl2br(htmlspecialchars($row['queryReply']))."
                                    </td>
                                    <td>
                                    <a href='updateQuery.php?id={$row['queryId']}' 
                                           class='btn btn-sm btn-warning ms-1' title='Edit'>
                                           <ion-icon name='create-outline'></ion-icon>
                                    </a>
                                    </td>
                                </tr>";
                                $sr++;
                            }
                        } else {
                            echo "<tr><td colspan='15' class='text-center'>No bookings found</td></tr>";
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