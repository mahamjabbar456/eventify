<?php include'./include/startingSection.php'; ?>
  </head>
  <body>

    <!--header section starts-->

    <?php 
    session_start();
    include'./include/header.php'; ?>

    <!--header section ends-->

    <!--home section starts-->

    <section class="home" id="home">
        <div class="home-content">
          <div class="swiper mySwiper">
              <div class="swiper-wrapper">

                <?php
                    include 'admin/connect.php';

                    $query = 'SELECT * FROM slider';
                    $result = mysqli_query($con,$query);

                    if(mysqli_num_rows($result) > 0){
                      while($row = mysqli_fetch_assoc($result)){
                        echo '
                          <div class="swiper-slide">
                              <img src="admin/uploads/'. $row['sliderImage'] .'" alt="' . $row['sliderTitle'] .'" class="home-img" >
                              <div class="home-details">
                                  <div class="home-text">
                                      <h4 class="homeSubtitle">'. $row['sliderTag'] .'</h4>
                                      <h2 class="homeTitle">'. $row['sliderTitle'] .'</h2>
                                      <p class="homeDescription">'. $row['sliderDescription'] .'</p>
                                  </div>
                              </div>
                          </div>
                        ';
                      }
                    }
                ?>
              </div>

              <div class="swiper-button-next swiper-navBtn"></div>
              <div class="swiper-button-prev swiper-navBtn"></div>
              <div class="swiper-pagination"></div>
          </div>
        </div>
        
    </section>
    <div class="searchSection">
          <form action="findFunction.php" method="GET" autocomplete="off">
            <div class="formgroup">
              <label for="date">Date</label>
              <input type="date" name="date" id="date" required  min="<?php echo date('Y-m-d'); ?>"  value="<?php echo isset($_POST['dateOfBooking']) ? $_POST['dateOfBooking'] : ''; ?>">
            </div>
            <div class="formgroup">
              <label for="function">Function Name</label>
              <input type="text" name="function" id="function" placeholder="Enter your function type" required>
            </div>
            <div class="formgroup">
              <label for="seating">Total Seats</label>
              <input type="number" name="seating" id="seating" placeholder="Enter your Seating Capacity" required>
            </div>
            <div class="formgroup">
              <label for="price">Price (Per Person)</label>
              <input type="number" name="price" id="price" placeholder="Enter your price" required>
            </div>
            <input type="submit" value="search" class="btn">
            <!-- <a href="findFunction.php?date=&?function=&?seating=&?price="></a> -->
          </form>
    </div>
    <!--home section ends-->

    <!--about section starts-->

    <section class="about" id="about">
      <h1 class="heading"><span>about</span> us</h1>

      <div class="row">
        <div class="image">
          <img src="assets/images/aboutus1.jpeg" alt="about us" />
        </div>

        <div class="content">
          <h3>we will give a very special celebration for you</h3>
          <p>
            We are dedicated to making wedding planning effortless and memorable. Our mission is to bridge the gap between couples and the perfect marriage halls by offering a seamless, transparent, and stress-free booking experience. Whether you're searching for a luxurious banquet hall or an intimate venue, our platform provides a curated selection of top-rated options, ensuring your special day is everything you've dreamed of. 
          </p>
          <p>
            With a strong commitment to customer satisfaction, we prioritize ease of use, reliability, and personalized service to help you plan your wedding with confidence. Driven by innovation and a passion for celebrations, we strive to revolutionize the wedding industry by integrating smart technology with exceptional support.
          </p>
          <a href="contact.php" class="btn">contact us</a>
        </div>
      </div>
    </section>

    <!--about section ends-->

    <!-- gallery section starts -->

    <section class="gallery" id="gallery">
      <h1 class="heading">our <span>gallery</span></h1>

      <div class="swiper home-slider">
        <div class="swiper-wrapper">
            <?php
            include 'admin/connect.php'; 
            $galleryQuery = "SELECT * FROM gallery";
            $galleryResult = mysqli_query($con, $galleryQuery);
            if (mysqli_num_rows($galleryResult) > 0) {
                $sr = 1;
                while ($row = mysqli_fetch_assoc($galleryResult)) {
                    echo '
                    <div class="swiper-slide">
                        <img src="admin/uploads/' . $row['galleryImage'] . '" alt="gallery" />
                    </div>';
                }}
            ?>
        </div>
      </div>
    </section>

    <!-- gallery section ends -->

    <section class="hall" id="hall">

        <h1 class="heading">our <span>halls</span></h1>

        <div class="box-container">

            <?php
               include 'admin/connect.php';
               $query = "SELECT * FROM hall WHERE status = 'active' LIMIT 3";
               $result = mysqli_query($con, $query);

               if(mysqli_num_rows($result)>0){
                  while($row = mysqli_fetch_assoc($result)){
                    echo '<div class="box">
                            <div class="box1">
                              <img src="admin/uploads/' . $row['logo'] . '" alt="">
                            <div class="icons">
                              <a href="specifichall.php?id=' .$row['id']. '" class="fas fa-eye">View</a>
                            </div>
                            </div>
                            <h2><span>' . $row['name']. '</span></h2>
                            <p>' . $row['detail'] . '</p>
                          </div>';
                  }
               } else{
                  echo '<div class="box">
                          <p>No Halls found</p>
                        </div>';
               }
            ?>

        </div>
        <a href="halls.php" class="btn">View More</a>

    </section>

    <!--about section starts-->

    <section class="about" id="about">
      <h1 class="heading"><span>register</span> your marquee</h1>

      <div class="row">
        <div class="image">
          <img src="assets/images/register1.jpeg" alt="register us" />
        </div>

        <div class="content">
          <h3>Hall Owner Registration and Management</h3>
          <p>
          The event planner system allows hall owners to register their halls and provide detailed information, including hall capacity, amenities, and availability. Hall owners can create and manage their profiles, upload photos and descriptions, and set their own rates and policies. 
          </p>
          <p>
          This enables event planners to easily find and book suitable halls for their events, while hall owners can increase their visibility and attract more clients. The system streamlines the process of managing hall information and bookings, making it easier for hall owners to manage their business.
          </p>
          <a href="registerHall.php" class="btn">register us</a>
        </div>
      </div>
    </section>

    <!--about section ends-->

    <!-- review section starts -->

    <section class="review clientPadding" id="review">
      <h1 class="heading">client's <span>review</span></h1>

      <div class="review-slider swiper-container">
        <div class="swiper-wrapper">
            <?php
            include 'admin/connect.php';
            $query = "SELECT * FROM testmonial";
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
            ?>
        </div>
      </div>
    </section>

    <!-- review section ends -->

    <!-- footer section starts -->

    <?php include'./include/footer.php'; ?>

    <!-- footer section ends -->

    <!-- theme toggler starts-->

    <?php include'./include/themeToggler.php'; ?>

    <!-- theme toggler ends -->

    <?php include'./include/scriptSection.php'; ?>
