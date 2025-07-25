<?php include'./include/startingSection.php'; ?>
</head>
<body>

    <!--header section starts-->
    <?php 
    session_start();
    include'./include/header.php'; ?>
    <!--header section ends-->

    <!--home section starts-->
    <section class="hall" style="padding: 10rem 9% 2rem 9%;" id="hall">

        <h1 class="heading">our <span>halls</span></h1>

        <!-- Search Bar -->
        <div class="search-bar">
            <div>
                <input type="text" id="hallSearch" name="search" placeholder="Search hall by name..." >
                <button type="button" class="btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="box-container" id="hallResults">
            <?php
               include 'admin/connect.php';
               $query = "SELECT * FROM hall WHERE status = 'active'";
               $result = mysqli_query($con, $query);

               if(mysqli_num_rows($result)>0){
                  while($row = mysqli_fetch_assoc($result)){
                    echo '<div class="box hall-item" data-name="'.htmlspecialchars(strtolower($row['name'])).'">
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
                  // Add a hidden no-results message for JavaScript to use
                  echo '<div class="box no-results-message" style="display:none;">
                          <p></p>
                        </div>';
               } else{
                  echo '<div class="box no-results-message">
                          <p>No Halls found</p>
                        </div>';
               }
            ?>
        </div>
    </section>

    <!-- footer section starts -->
    <?php include'./include/footer.php'; ?>
    <!-- footer section ends -->

    <?php include'./include/scriptSection.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('hallSearch');
        const hallItems = document.querySelectorAll('.hall-item');
        const noResultsMessage = document.querySelector('.no-results-message');
        
        // Create the no results message if it doesn't exist
        if(!noResultsMessage) {
            const noResultsDiv = document.createElement('div');
            noResultsDiv.className = 'box no-results-message';
            noResultsDiv.style.display = 'none';
            noResultsDiv.innerHTML = '<p></p>';
            document.getElementById('hallResults').appendChild(noResultsDiv);
        }

        // Live search as you type
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            let hasMatches = false;

            // Show/hide hall items based on search
            hallItems.forEach(item => {
                const hallName = item.getAttribute('data-name');
                if(hallName.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasMatches = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Handle no results message
            const messageElement = document.querySelector('.no-results-message');
            if(messageElement) {
                if(!hasMatches && searchTerm !== '') {
                    messageElement.style.display = 'block';
                    messageElement.querySelector('p').textContent = `No Halls found matching "${searchTerm}"`;
                } else {
                    messageElement.style.display = 'none';
                }
            }
        });
    });
    </script>
</body>
</html>