<?php include'./include/startingSection.php'; ?>
    <link rel="stylesheet" href="assets/css/about.css">
  </head>
  <body>

    <!--header section starts-->

    <?php
    session_start();
    include'./include/header.php'; ?>

    <!--header section ends-->

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

    <div class="mvv-container">
        <div class="mvv-block">
            <div class="image">
                <img src="assets/images/mission.jpeg" alt="">
            </div>
            <div class="content">
                <h5>Our Mission</h5>
                <p>Our mission is to simplify and elevate the wedding planning experience by seamlessly connecting couples with the perfect marriage halls. We strive to provide a hassle-free, transparent, and efficient booking platform that ensures unforgettable celebrations for our customers while empowering venue owners with smart management tools.</p>
            </div>
        </div>
        <div class="mvv-block">
            <div class="image">
                <img src="assets/images/vision.jpeg" alt="">
            </div>
            <div class="content">
                <h5>Our Vision</h5>
                <p>We envision a world where planning a wedding is as joyful as the event itself. By leveraging technology and exceptional customer service, we aim to become the leading event planner system for marriage halls, offering a one-stop solution for venue discovery, booking, and event coordination. Our goal is to transform the wedding industry by making it more accessible, organized, and delightful for everyone involved./p>
            </div>
        </div>
        <div class="mvv-block">
            <div class="image">
                <img src="assets/images/value.jpeg" alt="">
            </div>
            <div class="content">
                <h5>Our Value</h5>
                <p>We prioritize the needs and satisfaction of our customers, ensuring a smooth and personalized experience for every couple and venue partner. Honesty and clarity are at the core of our operations. We provide accurate information, fair pricing, and reliable services to build long-term trust. By integrating advanced technology, we streamline the booking process, reduce manual efforts, and enhance convenience for both customers and venue owners.</p>
            </div>
        </div>
    </div>

    <!-- footer section starts -->

    <?php include'./include/footer.php'; ?>

    <!-- footer section ends -->

    <!-- theme toggler starts-->

  <?php include'./include/themeToggler.php'; ?>

    <!-- theme toggler ends -->

<?php include'./include/scriptSection.php'; ?>