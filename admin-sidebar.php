<?php global $post;

$userId = $_SESSION['userID'] ?? null;
$fullName = $_SESSION['fullName'] ?? null;
$userEmail = $_SESSION['userEmail'] ?? null;
// If no transient, fallback to WordPress user's full name

if (empty($fullName) && is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $fullName = trim($current_user->first_name . ' ' . $current_user->last_name);

    // Optional fallback if first/last names are empty
    if (empty($fullName)) {
        $fullName = $current_user->display_name;
    }
}

$parts = explode(' ', trim($fullName)); // Split the full name into parts

if (count($parts) > 1) {
    array_pop($parts); // Remove the last element only if there's more than one part
}

$firstName = implode(' ', $parts);


?>

<div class="sidebar">
    <div class="logo">
        <img class="menuLogoImgText" src="/wp-content/uploads/2025/02/logo-with-text.png" alt="logo" srcset="">
        <img class="menuLogoImg" src="/wp-content/uploads/2025/08/Logo.png" alt="logo" srcset="">
    </div>
    <div class="collapseToggle mt-5 pt-5 mb-3">
        <!-- <span id="toggleIcon" class="fa fa-chevron-left"></span> -->
        <img class="arrow-left" src="/wp-content/uploads/2025/02/double-arrow-left.svg" alt="double-arrow-left">
        <img class="arrow-right" src="/wp-content/uploads/2025/02/double-arrow-right.svg" alt="double-arrow-right">
    </div>

    <ul class="nav nav-sidebar">
        <?php if ($post->ID == 254 || $post->ID == 513) { ?>
            <a href="/profile" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-home"></i> -->
                    <img class="prof-icon" src="/wp-content/uploads/2025/02/teams.svg">
                    <span class="menuText">Profile</span>
                </li>
            </a>
            <a href="/contractor-invoice-creator" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-chart-bar"></i> -->
                    <img class="inv-creator-icon" src="/wp-content/uploads/2025/03/Invoice-Creator.png">
                    <span class="menuText">Invoice Creator</span>
                </li>
            </a>
        <?php } else { ?>
            <a href="/dashboard" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-home"></i> -->
                    <img class="dashboard-icon" src="/wp-content/uploads/2025/02/dashboard.svg">
                    <span class="menuText">Dashboard</span>
                </li>
            </a>
            <a href="/team" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-chart-bar"></i> -->
                    <img class="prof-icon" src="/wp-content/uploads/2025/02/teams.svg">
                    <span class="menuText">Team</span>
                </li>
            </a>
            <a href="/channels" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-users"></i> -->
                    <img class="channels-icon" src="/wp-content/uploads/2025/08/channelsvg.svg">
                    <span class="menuText">Channels</span>
                </li>
            </a>
            <a href="/revenue" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-box"></i> -->
                    <img class="rev-icon" src="/wp-content/uploads/2025/02/total-revenue.svg">
                    <span class="menuText">Revenue</span>
                </li>
            </a>
            <a href="/invoices" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-file-invoice"></i> -->
                    <img class="inv-creator-icon" src="/wp-content/uploads/2025/03/Invoice-Creator.png">
                    <span class="menuText">Invoices</span>
                </li>
            </a>
            <a href="/miscellaneous" class="sidebar-link text-decoration-none">
                <li>
                    <img class="inv-creator-icon" src="/wp-content/uploads/2025/08/miscellaneous.svg">
                    <span class="menuText">Miscellaneous</span>
                </li>
            </a>
        <?php } ?>
    </ul>
    <ul class="nav nav-sidebar mt-auto">
        <?php if ($post->ID != 254 && $post->ID != 513) { ?>
            <!-- <li>
                <a href="/search" class="sidebar-link text-decoration-none">
                    <img class="search-icon" src="/wp-content/uploads/2025/03/Search.png">
                    <span class="menuText">Search</span>
                </a>
            </li> -->
            <a href="/setting" class="sidebar-link text-decoration-none">
                <li>
                    <!-- <i class="fas fa-users"></i> -->
                    <img class="settings-icon" src="/wp-content/uploads/2025/03/Settings.png">
                    <span class="menuText">Settings</span>
                </li>
            </a>
        <?php } ?>
        <!-- <li>
            <a href="/notification" class="sidebar-link text-decoration-none">
                <img class="notif-icon" src="/wp-content/uploads/2025/03/Notifications.png">
                <span class="menuText">Notfication</span>
            </a>
        </li> -->
        
    </ul>
    <div class="profile-section" x-data="logoutComponent">
        <div class="d-flex align-items-center">
            <!-- <a href="" class="d-flex align-items-center text-decoration-none"> -->
            <img src="/wp-content/uploads/2025/02/default-img.png" style="height:60px; cursor: pointer;"
                @click="logoutWithSuccess()" class="rounded-circle" alt="Profile">
            <div class="ms-3 profile-info">
                <h6 class="mb-0 profile-text"><?php echo $firstName; ?></h6>
                <small class="mb-0 profile-text profile-link" @click="logoutWithSuccess()">Log Out</small>
            </div>
            <!-- </a> -->
        </div>
    </div>
</div>



<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('logoutComponent', () => ({
            showlogout: true,
            logoutWithSuccess() {
                this.showlogout = false;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You will be logged out.',
                    icon: 'warning',
                    showCancelButton: true,
                    customClass: {
                        confirmButton: 'my-confirm-btn',
                        cancelButton: 'my-cancel-btn'
                    },
                    confirmButtonText: 'Yes, Logout'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Logging out...',
                            text: 'Please wait...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        setTimeout(() => {
                            window.location.href = '/logout';
                        }, 1000);
                    }
                });
            }
        }));
    });

    // jQuery(document).ready(function($) {
    //     $('.collapseToggle').on('click', function() {
    //         $(".sidebar").toggleClass('sidebar--Collapse');
    //         $('.main-content').toggleClass('main-content--slide');
    //         $('#toggleIcon').toggleClass('rotate');
    //     });
    // });

    jQuery(document).ready(function($) {
        function closeSidebarOnResize() {
            if (window.innerWidth <= 425) {
                $(".sidebar").addClass("sidebar--Collapse"); // Ensure sidebar stays collapsed
            } else {
                $(".sidebar").removeClass("sidebar--Collapse"); // Open it back if above 425px
            }
        }

        // Run on page load and window resize
        closeSidebarOnResize();
        $(window).on("resize", closeSidebarOnResize);

        // Toggle sidebar manually
        $(".collapseToggle").on("click", function() {
            $(".sidebar").toggleClass("sidebar--Collapse");
            $(".main-content").toggleClass("main-content--slide");
            $("#toggleIcon").toggleClass("rotate");
        });
    });
</script>

<style>
.sidebar {
        width: 280px;
        -moz-transition: width 0.5s ease-out;
        -o-transition: width 0.5s ease-out;
        -webkit-transition: width 0.5s ease-out;
        transition: width 0.5s ease-out;
        /* display: inline-block; */
        z-index: 0;
        display: flex;
        flex-direction: column;
    }

    .nav-sidebar>li>a ,
    .nav-sidebar span.menuText {
        font-size: 17px
    }

    .nav-sidebar li {
        padding: 10px;
        border-radius: 8px;
    }

    .nav-sidebar li:hover {
        background: #eaf3fb;
    }

    .nav-sidebar {
        background: none;
        margin-top: 45px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        /* padding-left: 20px; */
    }

    .sidebar ul li a span.fa {
        font-size: 20px;
    }

    .profile-info h6,
    .profile-info a {
        font-size: 20px;
        font-weight: 600;
        color: #101010;
    }

    .profile-info a:hover {
        color: #2194ff;
    }

    .menuText {
        left: 75px;
        opacity: 1;
        -moz-transition: opacity 0.6s 0.2s ease-out;
        -o-transition: opacity 0.6s 0.2s ease-out;
        -webkit-transition: opacity 0.6s 0.2s ease-out;
        transition: opacity 0.6s 0.2s ease-out;
        position: absolute;
    }

    .menuLogoImgText,
    .arrow-left {
        opacity: 1;
        -moz-transition: opacity 0.6s 0.2s ease-in-out;
        -o-transition: opacity 0.6s 0.2s ease-in-out;
        -webkit-transition: opacity 0.6s 0.2s ease-in-out;
        transition: opacity 0.6s 0.2s ease-in-out;
        position: absolute;
    }

    .menuLogoImg,
    .arrow-right {
        opacity: 0;
        -moz-transition: opacity 0.6s 0.2s ease-in-out;
        -o-transition: opacity 0.6s 0.2s ease-in-out;
        -webkit-transition: opacity 0.6s 0.2s ease-in-out;
        transition: opacity 0.6s 0.2s ease-in-out;
        position: absolute;
    }

    .collapseToggle {
        padding-left: 0;
        display: block;
        cursor: pointer;
        transition: padding-left 0.6s ease-out;
    }

    #toggleIcon {
        -moz-transition: transform 0.4s ease-out;
        -o-transition: transform 0.4s ease-out;
        -webkit-transition: transform 0.4s ease-out;
        transition: transform 0.4s ease-out;
    }

    .rotate {
        -moz-transform: rotate(180deg);
        -ms-transform: rotate(180deg);
        -o-transform: rotate(180deg);
        -webkit-transform: rotate(180deg);
        transform: rotate(180deg);
        -moz-transition: transform 0.4s ease-out;
        -o-transition: transform 0.4s ease-out;
        -webkit-transition: transform 0.4s ease-out;
        transition: transform 0.4s ease-out;
    }

    .sidebar--Collapse {
        width: 105px;
        -moz-transition: width 0.6s ease-out;
        -o-transition: width 0.6s ease-out;
        -webkit-transition: width 0.6s ease-out;
        transition: width 0.6s ease-out;
        padding: 20px;
    }

    .sidebar--Collapse .profile-text,
    .sidebar--Collapse .menuText,
    .sidebar--Collapse .menuLogoImgText,
    .sidebar--Collapse .arrow-left {
        opacity: 0;
        -moz-transition: opacity 0.3s ease-out;
        -o-transition: opacity 0.3s ease-out;
        -webkit-transition: opacity 0.3s ease-out;
        transition: opacity 0.3s ease-out;
        display: none;
        /* position: absolute; */
    }

    .sidebar--Collapse .menuLogoImg,
    .sidebar--Collapse .arrow-right {
        opacity: 1;
        -moz-transition: opacity 0.3s ease-in-out;
        -o-transition: opacity 0.3s ease-in-out;
        -webkit-transition: opacity 0.3s ease-in-out;
        transition: opacity 0.3s ease-in-out;

    }

    .sidebar--Collapse .collapseToggle,
    .sidebar--Collapse .logo {
        display: flex;
        justify-content: center;
    }

    .sidebar--Collapse .nav-sidebar li {
        display: flex;
        justify-content: center;
    }

    .main-content {
        background-color: #eaf3fb;
        margin-left: 280px;
        -moz-transition: margin-left 0.55s ease-out;
        -o-transition: margin-left 0.55s ease-out;
        -webkit-transition: margin-left 0.55s ease-out;
        transition: margin-left 0.55s ease-out;
    }

    .main-content--slide {
        -moz-transition: margin-left 0.6s ease-out;
        -o-transition: margin-left 0.6s ease-out;
        -webkit-transition: margin-left 0.6s ease-out;
        transition: margin-left 0.6s ease-out;
        margin-left: 100px;
    }

    .profile-info>* {
        white-space: nowrap;
    }

    .logo {
        margin-top: 45px;
    }

    .arrow-right,
    .arrow-left {
        padding: 10px;
        border: solid 1px #D0D0D0;
        border-radius: 10px;
    }

    .arrow-right:hover {
        background: #EAF3FB;
    }

    .arrow-left:hover {
        background: #EAF3FB;
    }

    .nav>li>a:hover {
        background-color: transparent !important;
    }

    .profile-link {
        cursor: pointer;
    }

    .confirmation-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .modal-content form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-content label {
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        max-width: 800px;
        max-height: 800px;
        width: 90%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .confirm-btn {
        background-color: #2194FF;
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button.confirm-btn:hover,
    button.confirm-btn:focus {
        color: #fff;
        background-color: #142B41;
        text-decoration: none;
    }

    .cancel-btn {
        background-color: transparent;
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #101010;
    }

    button.cancel-btn:hover {
        color: #101010;
        background-color: #2194FF80;
        text-decoration: none;
    }

    .my-confirm-btn,
    .okay-confirm-btn {
        background-color: #2194FF !important;
        color: #fff !important;
        border: solid 1px #2194FF;
        border-radius: 4px;
        padding: 10px !important;
        font-size: 14px;
    }

    .my-confirm-btn:hover,
    .okay-confirm-btn:hover {
        background-color: transparent !important;
        color: #2194FF !important;
    }

    .my-cancel-btn {
        color: #101010 !important;
        border: 1px solid #ddd !important;
        background-color: transparent !important;
        border-radius: 4px;
        padding: 10px !important;
        font-size: 14px;
    }

    .my-cancel-btn:hover {
        background-color: #2194FF80 !important;
        color: #101010 !important;
    }

    h2.swal2-title {
        font-size: 24px;
        color: #101010 !important;
    }

    div.swal2-html-container {
        font-size: 18px;
    }
    /* Media Queries */
    @media only screen and (max-width: 425px) {
        .logo {
            margin-top: 0;
        }

        .sidebar {
            position: fixed;
            /* height: 100vh; */
            height: 100%;
            background: #fff;
            top: 0;
            left: 0;
            z-index: 1000;
            padding: 20px;
        }

        .main-content {
            overflow-y: auto;
            margin-left: 100px !important;
        }

        .sidebar--Collapse .nav-sidebar {
            align-items: center;
        }
    }
</style>