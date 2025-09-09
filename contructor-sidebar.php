        <?php

        ?>
        <!-- <div class="d-flex"> -->
        <nav class="sidebar d-flex flex-column flex-shrink-0 position-fixed">
            <div class="p-4 position-relative logo-container">
                <img class="logo"
                    src="/wp-content/uploads/2025/02/logo-with-text.png"
                    alt="Logo"
                    data-text-logo="/wp-content/uploads/2025/02/logo-with-text.png"
                    data-no-text-logo="/wp-content/uploads/2025/02/logo-no-text.png">
            </div>
            <div class="mt-5 mb-4">
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <img src="https://clickable.opt.co.nz/wp-content/uploads/2025/02/double-arrow-left.svg" alt="arrow">
                    <!-- <i class="fa fa-angle-double-right" aria-hidden="true"></i> -->
                </button>
            </div>
            <div class="nav flex-column">
                <a href="/profile" class="sidebar-link text-decoration-none p-3">
                    <i class="fas fa-home me-3"></i>
                    <span class="hide-on-collapse">Profile</span>
                </a>
                <a href="/contractor-invoice-creator" class="sidebar-link text-decoration-none p-3">
                    <i class="fas fa-chart-bar me-3"></i>
                    <span class="hide-on-collapse">Invoice Creator</span>
                </a>
            </div>
            <div class="nav flex-column mt-auto">
                <a href="#" class="sidebar-link text-decoration-none p-3">
                    <i class="fa fa-bell me-3"></i>
                    <span class="hide-on-collapse">Notification</span>
                </a>
                <a href="#" class="sidebar-link text-decoration-none p-3">
                    <i class="fa fa-cog me-3"></i>
                    <span class="hide-on-collapse">Settings</span>
                </a>

            </div>

            <div class="profile-section mt-auto">
                <div class="d-flex align-items-center">
                    <img src="https://clickable.opt.co.nz/wp-content/uploads/2025/02/default-img.png" style="height:60px" class="rounded-circle" alt="Profile">
                    <div class="ms-3 profile-info">
                        <h6 class="mb-0">John Doe</h6>
                        <small class="">aSign Out</small>
                    </div>
                </div>
            </div>
        </nav>

        <script>
            function toggleSidebar() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('collapsed');

                const mainContent = document.querySelector('.main-content');
                mainContent.classList.toggle('collapsed');

                const img = document.querySelector('.logo');
                let currentSrc = img.getAttribute('src');
                let textLogo = img.getAttribute('data-text-logo');
                let noTextLogo = img.getAttribute('data-no-text-logo');

                img.style.opacity = '0'; // Fade out

                setTimeout(() => {
                    img.setAttribute('src', currentSrc === textLogo ? noTextLogo : textLogo);
                    img.style.opacity = '1'; // Fade in after changing the image
                }, 500); // Wait for fade-out to complete before changing image
            }
        </script>
        <!-- </div> -->