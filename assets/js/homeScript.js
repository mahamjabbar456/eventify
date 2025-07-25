let menu = document.querySelector('#menu-bars');
let navbar = document.querySelector('.navbar');

menu.onclick = () => {
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
}

// Add this new dropdown functionality
document.querySelectorAll('.dropdown .dropbtn').forEach(dropbtn => {
    dropbtn.addEventListener('click', function(e) {
        // Only prevent default for mobile/tablet
        if (window.innerWidth <= 992) {
            e.preventDefault();
            const dropdownContent = this.nextElementSibling;
            dropdownContent.classList.toggle('active');
            
            // Close other open dropdowns
            document.querySelectorAll('.dropdown-content').forEach(content => {
                if (content !== dropdownContent && content.classList.contains('active')) {
                    content.classList.remove('active');
                }
            });
        }
    });
});

// Close dropdowns when clicking outside (both profile and hall dropdowns)
window.addEventListener('click', function(e) {
    if (!e.target.matches('.dropbtn') && !e.target.closest('.dropdown-content') && 
        !e.target.matches('.profile-dropdown-btn') && !e.target.closest('.profile-dropdown-list')) {
        
        // Close all dropdowns
        document.querySelectorAll('.dropdown-content, .profile-dropdown-list').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }
});

// Rest of your existing code remains the same
let themeToggler = document.querySelector('.theme-toggler');
let toggleBtn = document.querySelector('.toggle-btn');

toggleBtn.onclick = () => {
    themeToggler.classList.toggle('active');
}

window.onscroll = () => {
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');
    themeToggler.classList.remove('active');
    
    // Also close all dropdowns on scroll
    document.querySelectorAll('.dropdown-content, .profile-dropdown-list').forEach(dropdown => {
        dropdown.classList.remove('active');
    });
}

document.querySelectorAll('.theme-toggler .theme-btn').forEach(btn => {
    btn.onclick = () => {
        let color = btn.style.backgroundColor;
        document.querySelector(':root').style.setProperty('--main-color', color);
    }
});

// Your existing Swiper initializations
var swiper = new Swiper(".home-slider", {
    effect: "coverflow",
    grabCursor: true,
    centeredSlides: true,
    slidesPerView: "auto",
    coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 100,
        modifier: 2,
        slideShadows: true,
    },
    loop: true,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    }
});

var swiper = new Swiper(".review-slider", {
    slidesPerView: 1,
    grabCursor: true,
    loop: true,
    spaceBetween: 10,
    breakpoints: {
        0: {
            slidesPerView: 1,
        },
        700: {
            slidesPerView: 2,
        },
        1050: {
            slidesPerView: 3,
        },
    },
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    }
});

var Swiper = new Swiper(".mySwiper", {
    slidesPerView: 1,
    loop: true,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
});

let profileDropdownList = document.querySelector(".profile-dropdown-list");
let profileBtn = document.querySelector(".profile-dropdown-btn");

const toggleProfileDropdown = () => profileDropdownList.classList.toggle("active");

profileBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    toggleProfileDropdown();
});