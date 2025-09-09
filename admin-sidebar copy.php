<?php global $post; ?>
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

    .nav-sidebar>li>a {
        font-size: 17px
    }

    /* .nav-sidebar ul li:hover {
        background: #eaf3fb;
    } */

    .nav-sidebar {
        background: none;
        margin-top: 5px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-left: 20px;
    }

    .sidebar ul li a span.fa {
        font-size: 20px;
    }

    .profile-info h6 {
        font-size: 20px;
        font-weight: 600;
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
        -moz-transition: opacity 0.6s 0.2s ease-out;
        -o-transition: opacity 0.6s 0.2s ease-out;
        -webkit-transition: opacity 0.6s 0.2s ease-out;
        transition: opacity 0.6s 0.2s ease-out;
        position: absolute;
    }

    .menuLogoImg,
    .arrow-right {
        opacity: 0;
        -moz-transition: opacity 0.6s 0.2s ease-out;
        -o-transition: opacity 0.6s 0.2s ease-out;
        -webkit-transition: opacity 0.6s 0.2s ease-out;
        transition: opacity 0.6s 0.2s ease-out;
        position: absolute;
    }

    .collapseToggle {
        padding-left: 20px;
        display: block;
        cursor: pointer;
        padding-right: 5px;
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
        /* position: absolute; */
    }

    .sidebar--Collapse .menuLogoImg,
    .sidebar--Collapse .arrow-right {
        opacity: 1;
        -moz-transition: opacity 0.3s ease-out;
        -o-transition: opacity 0.3s ease-out;
        -webkit-transition: opacity 0.3s ease-out;
        transition: opacity 0.3s ease-out;
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
        padding-left: 20px;
    }
</style>
<div class="sidebar">
    <div class="logo mt-5">
        <img class="menuLogoImgText" src="/wp-content/uploads/2025/02/logo-with-text.png" alt="logo" srcset="">
        <img class="menuLogoImg" src="/wp-content/uploads/2025/03/logo-img.png" alt="logo" srcset="">
    </div>
    <div class="collapseToggle mt-5 pt-5 mb-3">
        <!-- <span id="toggleIcon" class="fa fa-chevron-left"></span> -->
        <img class="arrow-left" src="/wp-content/uploads/2025/02/double-arrow-left.svg" alt="double-arrow-left">
        <img class="arrow-right" src="/wp-content/uploads/2025/02/double-arrow-right.svg" alt="double-arrow-right">
    </div>

    <ul class="nav nav-sidebar">
        <?php if ($post->ID == 254 || $post->ID == 513) { ?>
            <li>
                <a href="/profile" class="sidebar-link text-decoration-none p-3">
                    <i class="fas fa-home"></i>
                    <span class="hide-on-collapse">Profile</span>
                </a>

            </li>
            <li>
                <a href="/contractor-invoice-creator" class="sidebar-link text-decoration-none p-3">
                    <i class="fas fa-chart-bar"></i>
                    <span class="hide-on-collapse">Invoice Creator</span>
                </a>
            </li>
        <?php } else { ?>
            <li>
                <a href="/dashboard" class="sidebar-link text-decoration-none">
                    <i class="fas fa-home"></i>
                    <span class="menuText">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/team" class="sidebar-link text-decoration-none">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menuText">Team</span>
                </a>
            </li>
            <li>
                <a href="/channel-list" class="sidebar-link text-decoration-none">
                    <i class="fas fa-users"></i>
                    <span class="menuText">Channels</span>
                </a>
            </li>
            <li>
                <a href="/revenue" class="sidebar-link text-decoration-none">
                    <i class="fas fa-box"></i>
                    <span class="menuText">Revenue</span>
                </a>

            </li>
            <li>
                <a href="/invoices" class="sidebar-link text-decoration-none">
                    <i class="fas fa-file-invoice"></i>
                    <span class="menuText">Invoices</span>
                </a>
            </li>
        <?php } ?>
    </ul>
    <ul class="nav nav-sidebar mt-auto">
        <?php if ($post->ID != 254 || $post->ID != 513) { ?>
            <li>
                <a href="/search" class="sidebar-link text-decoration-none">
                    <i class="fas fa-home"></i>
                    <span class="menuText">Search</span>
                </a>
            </li>
        <?php } ?>
        <li>
            <a href="/notification" class="sidebar-link text-decoration-none">
                <i class="fas fa-chart-bar"></i>
                <span class="menuText">Notfication</span>
            </a>
        </li>
        <li>
            <a href="/settings" class="sidebar-link text-decoration-none">
                <i class="fas fa-users"></i>
                <span class="menuText">Settings</span>
            </a>
        </li>
    </ul>
    <div class="profile-section">
        <div class="d-flex align-items-center">
            <img src="https://clickable.opt.co.nz/wp-content/uploads/2025/02/default-img.png" style="height:60px" class="rounded-circle" alt="Profile">
            <div class="ms-3 profile-info">
                <h6 class="mb-0 profile-text">John Doe</h6>
                <small class="profile-text">View Profile</small>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        $('.collapseToggle').on('click', function() {
            $(".sidebar").toggleClass('sidebar--Collapse');
            $('.main-content').toggleClass('main-content--slide');
            $('#toggleIcon').toggleClass('rotate');
        });
    });
</script>