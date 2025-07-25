<?php include'./include/startingSection.php'; ?>
    <link rel="stylesheet" href="assets/css/hall.css" />
  </head>
  <body>

    <!--header section starts-->

    <?php
    session_start();
    $isLoggedIn = isset($_SESSION['customerId']);
    include'./include/header.php'; ?>

    <!--header section ends-->
    
    <section class="intro" id="intro">
        <?php
            include 'admin/connect.php'; 
            $hallId = isset($_GET['id']) ? $_GET['id'] : '';
            if($hallId){
                $hallQuery = "SELECT * FROM hall WHERE id = '$hallId'";
                $hallResult = mysqli_query($con, $hallQuery);
                if (mysqli_num_rows($hallResult) > 0) {
                    $sr = 1;
                    while ($row = mysqli_fetch_assoc($hallResult)) {
                        $hallName = $row['name'];

                        echo '<img src="admin/uploads/' . $row['cover'] . '" 
                                alt="' . $row['name'] . '" 
                                loading="lazy"
                                width="1920" 
                                height="1080">
                                <div class="content">
                                    <img src="admin/uploads/' . $row['logo'] . '"  
                                        alt="' . $row['name'] . '" 
                                        loading="lazy"
                                        width="100" 
                                        height="100"
                                        decoding="async">
                                    <h1>' . $row['name'] . '</h1>
                                    <p>' . $row['detail'] . '</p>
                                    <div><span>Capacity: </span><span>' . $row['capacity'] . '</span></div>
                                    <div><span>Event Capacity: </span><span>' . $row['event_capacity'] . '</span></div>
                                    <div><span>Phone No: </span><a href="#">' . $row['contactNo'] . '</a></div>
                                    <div>
                                        <span>Address: </span>
                                        <address>' . $row['address'] . '</address>
                                    </div>
                                </div>';
                }}
            }
        ?>
    </section>

    <div class="live-chat">
        <a href="#" id="chat-toggle" class="chat-btn" aria-label="Open live chat">
            <i class="fas fa-comments"></i> Live Chat
        </a>
        <div id="chat-window" class="chat-window" aria-live="polite">
            <div class="chat-header">
                <h3>Chat with <?php echo htmlspecialchars($hallName); ?></h3>
                <button class="close-chat" aria-label="Close chat">×</button>
            </div>
            <div class="chat-messages" id="chat-messages" role="log">
                <!-- Messages will appear here -->
            </div>
            <div class="chat-input-container">
                <input 
                    type="text" 
                    id="message-input" 
                    placeholder="Type your message..." 
                    aria-label="Type your message"
                >
                <button id="send-btn" aria-label="Send message">Send</button>
            </div>
        </div>
    </div>

    <!--service section starts-->

    <section class="service" id="service">
        <h1 class="heading">Our <span>Services</span></h1>
        
        <div class="box-container">
        <?php
            include 'admin/connect.php'; 
            $hallId = isset($_GET['id']) ? $_GET['id'] : '';
            if($hallId){
                $serviceQuery = "SELECT * FROM service WHERE hallId = '$hallId' AND status = 'active'";
                $serviceResult = mysqli_query($con, $serviceQuery);
                if (mysqli_num_rows($serviceResult) > 0) {
                    $sr = 1;
                    while ($row = mysqli_fetch_assoc($serviceResult)) {

                        echo'<div class="box">
                            <div class="image-container">
                                <img src="admin/uploads/' .$row['background']. '" 
                                    alt='. $row['name'] .' 
                                    loading="lazy"
                                    width="400" 
                                    height="300">
                            </div>
                            <div class="content">
                                <div class="text-content">
                                    <h3>' . $row['name'] . '</h3>
                                    <p class="tagline"><span>Tagline:</span> '. $row['tagline'] .'</p>
                                    <p>' . $row['detail'] . '</p>
                                </div>
                            </div>
                        </div>';
                }}
            }
        ?>
        </div>
    </section>

<!--service section ends-->

    <!-- price section starts -->

    <section class="price" id="price">

        <div class="section-header">
            <h1 class="heading">Our <span>Packages</span></h1>
            <p class="subheading">Choose the perfect package for your event</p>
        </div>

        <div class="box-container">

        <?php
            include 'admin/connect.php'; 
            $hallId = isset($_GET['id']) ? $_GET['id'] : '';
            if($hallId){
                $packageQuery = "SELECT * FROM package WHERE hallId = '$hallId' AND status = 'active'";
            $packageResult = mysqli_query($con, $packageQuery);
            if (mysqli_num_rows($packageResult) > 0) {
                $sr = 1;
                while ($row = mysqli_fetch_assoc($packageResult)) {
                    $acRates = $row['acRates'] === 'perPerson'? 'per Person' : 'per Hour';
                    
                    echo "<div class='box'>
                            <h3 class='title'>{$row['packageName']}</h3>
                            <div class='package-content'>
                                <div class='pricing-details'>
                                    <h4>Seating Options</h4>
                                    <ul>";
                                    if($row['priceWithSofa'] != 0): 
                                        echo "<li><i class='fas fa-couch'></i> <strong>Sofa Seating:</strong> {$row['priceWithSofa']}</li>";
                                    endif; 
                                    echo"
                                        <li><i class='fas fa-couch'></i> <strong>Sofa Seating With Menu:</strong> {$row['priceWithSofaWithMenu']}</li>";
                                    if($row['priceWithChair'] != 0): 
                                        echo "<li><i class='fas fa-chair'></i> <strong>Chair Seating:</strong> {$row['priceWithChair']}</li>";
                                    endif;
                                        echo"<li><i class='fas fa-chair'></i> <strong>Chair Seating With Menu:</strong> {$row['priceWithChairWithMenu']}</                        li>";
                                    if($row['priceWithMix'] != 0): 
                                        echo"<li><i class='fas fa-random'></i> <strong>Sofa and Chair Seating:</strong> {$row['priceWithMix']}</li>";
                                    endif;
                                        echo"<li><i class='fas fa-random'></i> <strong>Sofa, Chair Seating With Menu:</strong> {$row['priceWithMixWithMenu']}</li>
                                    </ul>
                                </div>
                                <div class='menu-details'>
                                    <h4>Menu Details</h4>
                                    <ul>
                                        <li><i class='fas fa-utensils'></i> <strong>Starter:</strong> {$row['menuStarter']}</li>
                                        <li><i class='fas fa-drumstick-bite'></i> <strong>Chicken:</strong> {$row['menuChicken']}</li>
                                        <li><i class='fas fa-hamburger'></i> <strong>Beef:</strong> {$row['menuBeef']}</li>
                                        <li><i class='fas fa-bacon'></i> <strong>Mutton:</strong> {$row['menuMutton']}</li>
                                        <li><i class='fas fa-ice-cream'></i> <strong>Desert:</strong> {$row['menuDesert']}</li>
                                        <li><i class='fas fa-glass-whiskey'></i> <strong>Drinks:</strong> {$row['menuDrinks']}</li>
                                        <li><i class='fas fa-leaf'></i> <strong>Salad:</strong> {$row['menuSalad']}</li>
                                        <li><i class='fas fa-fan'></i> <strong>AC Charges:</strong> {$row['acChargesPrices']} {$acRates}</li>
                                        <li><i class='fas fa-check'></i> <strong>Menu Detail:</strong> {$row['menuDetail']}</li>
                                    </ul>
                                </div>
                            </div>
                            <a href='". ($isLoggedIn ? 'booking.php?id='. $row['packageId'] .'&hallId='. $hallId .'' : 'login.php') ."' class='btn btn1'>Check out</a>
                        </div>";
                }}
            }
        ?>
        </div>

    </section>

    <!-- price section ends -->

    <!-- gallery section starts -->

    <section class="gallery" id="gallery">
      <h1 class="heading">our <span>gallery</span></h1>

      <div class="swiper home-slider">
        <div class="swiper-wrapper">
            <?php
            include 'admin/connect.php'; 
            $hallId = isset($_GET['id']) ? $_GET['id'] : '';
            if($hallId){
                $galleryQuery = "SELECT * FROM gallery WHERE hallId = '$hallId'";
            $galleryResult = mysqli_query($con, $galleryQuery);
            if (mysqli_num_rows($galleryResult) > 0) {
                $sr = 1;
                while ($row = mysqli_fetch_assoc($galleryResult)) {
                    echo '
                    <div class="swiper-slide">
                        <img src="admin/uploads/' . $row['galleryImage'] . '" alt="gallery" />
                    </div>';
                }}
            }
            ?>
        </div>
      </div>
    </section>

    <!-- gallery section ends -->

    <!-- review section starts -->

    <section class="review clientPadding" id="review">
      <h1 class="heading">client's <span>review</span></h1>

      <div class="review-slider swiper-container">
        <div class="swiper-wrapper">
            <?php
            include 'admin/connect.php';
            $hallId = isset($_GET['id']) ? $_GET['id'] : '';
            if($hallId){
                $query = "SELECT * FROM testmonial WHERE hallId = '$hallId'";
                $result = mysqli_query($con, $query);
    
                if(mysqli_num_rows($result) > 0){
                    $sr = 1;
                    while ($row = mysqli_fetch_assoc($result)){
                        $ratingStars = str_repeat("⭐", $row['clientRating']);
                        $displayImage = (!empty($row['clientImage'])) 
                            ? 'admin/uploads/' . $row['clientImage'] 
                            : 'assets/images/reviewdumyimage.jpg';
                        echo '<div class="swiper-slide box">
                                <i class="fas fa-quote-right"></i>
                                <div class="user">
                                <img src="' . $displayImage . '" alt="' . $row['clientName'] . '" />
                                <div class="user-info">
                                    <h3>' . $row['clientName'] . '</h3>
                                    <span>' . $row['clientTitle'] . '</span>
                                </div>
                                </div>
                                <p>
                                '.nl2br(htmlspecialchars($row['clientReview'])).'
                                </p>
                                <p>
                                ' . $ratingStars . '
                                </p>
                            </div>';
                    }}
            }
            
            ?>
        </div>
      </div>

      <?php
        include 'admin/connect.php';
        function showSweetAlert($icon, $title, $text, $redirect = null) {
            echo "<script>
                Swal.fire({
                    icon: '$icon',
                    title: '$title',
                    text: '$text',
                    showConfirmButton: true,
                    timer: 3000
                })";
            if ($redirect) {
                echo ".then(() => { window.location.href = '$redirect'; })";
            }
            echo "</script>";
        }
        // Fetch hallId from the URL parameter
        $hallId = isset($_GET['id']) ? $_GET['id'] : '';
        $isLoggedIn = isset($_SESSION['customerId']);

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
            $clientTitle = mysqli_real_escape_string($con, $_POST['serviceName']);
            $clientReview = mysqli_real_escape_string($con, $_POST['clientReview']);
            $clientRating = $_POST['clientRating'];
            
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

                // Validate file type
                if (in_array($imageExt, $allowedExtensions)) {
                    // Check for upload errors
                    if ($imageError === 0) {
                        // Check file size (5MB limit)
                        if ($imageSize < 5000000) {
                            $newImageName = uniqid('IMG-', true) . '.' . $imageExt;
                            $imageDestination = "admin/uploads/" . $newImageName;
                            
                            if (move_uploaded_file($imageTmpName, $imageDestination)) {
                                $imageUploaded = true;
                            } else {
                                showSweetAlert('error', 'File Requirements', 'Error moving uploaded file!');
                            }
                        } else {
                            showSweetAlert('error', 'File Requirements', 'File size too large! Max 5MB allowed.');
                        }
                    } else {
                        showSweetAlert('error', 'File Requirements', 'Error uploading file!');
                    }
                } else {
                    showSweetAlert('error', 'File Requirements', 'Invalid file type! Only JPG, PNG, GIF, and WEBP allowed.');
                }
            }

            // Prepare the SQL query based on whether an image was uploaded
            if ($imageUploaded) {
                $sql = "INSERT INTO testmonial (clientImage, clientName, clientTitle, clientReview, clientRating, hallId) 
                        VALUES ('$newImageName', '$clientName', '$clientTitle', '$clientReview', '$clientRating', '$hallId')";
            } else {
                $sql = "INSERT INTO testmonial (clientImage, clientName, clientTitle, clientReview, clientRating, hallId) 
                        VALUES (NULL, '$clientName', '$clientTitle', '$clientReview', '$clientRating', '$hallId')";
            }

            if (mysqli_query($con, $sql)) {
                showSweetAlert('success', 'Testmonial Added', 'Testmonial added successfully!', $_SERVER['HTTP_REFERER']);
            } else {
                echo "Error: " . mysqli_error($con);
            }
        }
      ?>

      <div class="form bookingForm">
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="inputBox">
                <div class="form-group">
                    <label for="clientName">Client Name</label>
                    <input type="text" id="clientName" class="form-control" placeholder="Enter your name" name="clientName" required>
                </div>
                <div class="form-group">
                    <label for="clientImage">Client Image</label>
                    <input type="file" id="clientImage" class="form-control" name="clientImage">
                </div>
            </div>
            <div class="inputBox">
                <div class="form-group">
                    <label for="serviceName">Service Name</label>
                    <input type="text" id="serviceName" class="form-control" placeholder="Enter service name" name="serviceName" required>
                </div>
                <div class="form-group">
                    <label for="clientRating">Client Rating</label>
                    <select class="form-control" id="clientRating" name="clientRating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                        <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                        <option value="3">⭐⭐⭐ (3 Stars)</option>
                        <option value="2">⭐⭐ (2 Stars)</option>
                        <option value="1">⭐ (1 Star)</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="clientReview">Client Review</label>
                <textarea name="clientReview" placeholder="Enter your review" id="clientReview" cols="30" rows="10"></textarea>
            </div>
            <input type="submit" value="Submit your review" class="btn" name="submit">
        </form>
    </div>
    </section>

    <!-- review section ends -->

    <!-- footer section starts -->

    <?php include'./include/footer.php'; ?>

    <!-- footer section ends -->

    <!-- theme toggler starts-->

    <?php include'./include/themeToggler.php'; ?>

    <!-- theme toggler ends -->
     <script>
        document.addEventListener('DOMContentLoaded', function() {
    const chatWindow = document.getElementById('chat-window');
    const chatToggle = document.getElementById('chat-toggle');
    const messageInput = document.getElementById('message-input');
    let refreshInterval;
    
    // Toggle chat window
    chatToggle.addEventListener('click', function(e) {
        e.preventDefault();
        const isOpening = chatWindow.style.display !== 'flex';
        chatWindow.style.display = isOpening ? 'flex' : 'none';
        
        if (isOpening) {
            loadMessages();
            refreshInterval = setInterval(loadMessages, 3000);
        } else {
            clearInterval(refreshInterval);
        }
    });

    // Close chat
    document.querySelector('.close-chat').addEventListener('click', function() {
        chatWindow.style.display = 'none';
        clearInterval(refreshInterval);
    });

    // Send message
    document.getElementById('send-btn').addEventListener('click', sendMessage);
    
    // Allow Enter key to send
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;
        
        fetch(`chat.php?hallId=<?php echo $hallId; ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message=${encodeURIComponent(message)}`
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(() => {
            messageInput.value = '';
            loadMessages();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send message');
        });
    }

    function loadMessages() {
        fetch(`chat.php?hallId=<?php echo $hallId; ?>`)
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                }
                throw new Error('Network error');
            }
            return response.json();
        })
        .then(messages => {
            let html = '';
            messages.forEach(msg => {
                const messageClass = msg.sender === 'user' ? 'user-message' : 'hall-message';
                const time = msg.formatted_time ? formatTime(msg.formatted_time) : 'Just now';
                const readStatus = msg.isRead ? '' : ' (unread)';
                html += `<div class="message ${messageClass}">
                           <p>${msg.message}</p>
                           <span class="time">${time}${readStatus}</span>
                         </div>`;
            });
            document.getElementById('chat-messages').innerHTML = html;
            // Scroll to bottom
            const container = document.getElementById('chat-messages');
            container.scrollTop = container.scrollHeight;
        })
        .catch(error => console.error('Error loading messages:', error));
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
});
     </script>

<?php include'./include/scriptSection.php'; ?>