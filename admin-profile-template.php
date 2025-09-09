<?php

/**
 * Template Name: Admin Profile Template
 */
get_header('admin');

// Get the currently logged-in WordPress user
$current_user = wp_get_current_user();

$user_data = [
    'id'        => $current_user->ID,
    'username'  => $current_user->user_login,
    'email'     => $current_user->user_email,
    'firstname' => $current_user->first_name,
    'lastname'  => $current_user->last_name,
    'nickname'  => $current_user->nickname,
    'fullname'  => trim($current_user->first_name . ' ' . $current_user->last_name) ?: $current_user->display_name,
    'roles'     => $current_user->roles,
];

// Example: ACF field for profile photo (optional)
$profile_photo = get_field('profile_photo', 'user_' . $current_user->ID);

echo '<script id="user-data" type="application/json">' . wp_json_encode($user_data) . '</script>';


?>


<div class="container-fluid p-0" x-data="userInfo()" x-init="init()">
    <?php get_template_part('admin-sidebar'); ?>
    <div class="main-content">
        <main class="flex-grow-1 p-4">
            <div class="container-fluid mt-4 p-0">
                <div class="card profile-container">
                    <div class="profile-header position-relative"></div>

                    <div class="row p-4 m-0 z-0">
                        <div class="col-lg-10 col-md-12">
                            <div class="row">
                                <div class="col-xl-3 col-lg-5 col-md-12 d-md-flex justify-content-lg-start justify-content-md-center">
                                    <img class="img-fluid profile-img"
                                        src="<?php echo $profile_photo['sizes']['medium'] ?? '/wp-content/uploads/2025/02/Placeholder-Image-13.png'; ?>"
                                        alt="Profile Picture">
                                </div>
                                <div class="col-xl-9 col-lg-7 col-md-12 d-md-flex flex-md-column align-items-md-center align-items-lg-start">
                                    <h4 class="mt-2" x-text="user.username"></h4>
                                    <p class="text-muted" x-text="user.fullname"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-12 profile-edit-button">
                            <div class="btn-update">
                                <img src="/wp-content/uploads/2025/05/white-edit.svg" alt="">
                                <button class="btn-edit" @click="showActions = true">
                                    Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row p-4 m-0 row-gap-4 profile-details">
                        <div class="col-lg-6 col-md-12 d-flex">
                            <div class="card w-100">
                                <div class="card-body">
                                    <h5>Personal Info</h5>
                                    <p><strong>First Name:</strong> <span x-text="user.firstname"></span></p>
                                    <p><strong>Last Name:</strong> <span x-text="user.lastname"></span></p>
                                    <p><strong>Nickname:</strong> <span x-text="user.nickname"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="card w-100">
                                <div class="card-body">
                                    <h5>Details</h5>
                                    <p><strong>Email:</strong> <span x-text="user.email"></span></p>
                                    <p><strong>Role:</strong> <span x-text="user.roles"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile Modal -->
                    <div class="hidden confirmation-modal" x-show="showActions"
                        x-transition x-bind:class="{ 'hidden': !showActions }">
                        <div class="modal-content">
                            <h3>Update Profile?</h3>
                            <form @submit.prevent="submitConfirmed">
                                <div>
                                    <label class="input-label">Profile Image:</label>
                                    <input type="file" @change="uploadImage">
                                </div>
                                <div>
                                    <label class="input-label">Username:</label>
                                    <input type="text" x-model="user.username" 
                                        :placeholder="username" readonly 
                                        @mouseenter="showTip = true"
                                        @mouseleave="showTip = false"
                                        class="">
                                    <div x-show="showTip"
                                        x-transition
                                        class="absolute left-0 mt-1 px-2 py-1 text-xs text-red bg-gray-800 rounded shadow">
                                        Usernames cannot be changed!
                                    </div>
                                </div>
                                <!-- <div>
                                    <label>Email:</label>
                                    <input type="email" x-model="user.email" :placeholder="email">
                                </div> -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="input-label">First Name:</label>
                                        <input type="text" x-model="user.firstname" :placeholder="firstname">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-label">Last Name:</label>
                                        <input type="text" x-model="user.lastname" :placeholder="lastname">
                                    </div>
                                </div>
                                <div>
                                    <label class="input-label">Nickname:</label>
                                    <input type="text" x-model="user.nickname" :placeholder="nickname">
                                </div>
                                <div class="modal-actions">
                                    <button type="submit" @click="submitConfirmed" class="confirm-btn">Save</button>
                                    <button type="button" @click="showActions = false" class="cancel-btn">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // function userInfo() {
    //     return {
    //         user: null,
    //         showTip: false,
    //         showActions: false,
    //         init() {
    //             try {
    //                 let el = document.getElementById("user-data");
    //                 if (el) {
    //                     this.user = JSON.parse(el.textContent);
    //                 }
    //             } catch (e) {
    //                 console.error("Error parsing user data:", e);
    //                 this.user = {id: 0, username: "guest", fullname: "Guest User"};
    //             }
    //         }
    //     };
    // }

    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

    function userInfo() {
        return {
            user: null,
            showTip: false,
            showActions: false,
            init() {
                try {
                    let el = document.getElementById("user-data");
                    if (el) {
                        this.user = JSON.parse(el.textContent);
                    }
                } catch (e) {
                    console.error("Error parsing user data:", e);
                    this.user = {id: 0, username: "guest", fullname: "Guest User"};
                }
            },
            // async submitConfirmed() {
            //     let formData = new FormData();
            //     formData.append("action", "update_user_profile");
            //     formData.append("id", this.user.id);
            //     formData.append("firstname", this.user.firstname);
            //     formData.append("lastname", this.user.lastname);
            //     formData.append("nickname", this.user.nickname);
            //     formData.append("email", this.user.email);

            //     // If profile image uploaded
            //     let fileInput = document.querySelector('input[type="file"]');
            //     if (fileInput && fileInput.files[0]) {
            //         formData.append("profile_photo", fileInput.files[0]);
            //     }

            //     try {
            //         let response = await fetch(ajaxurl, {
            //             method: "POST",
            //             body: formData,
            //         });
            //         let result = await response.json();

            //         if (result.success) {
            //             alert("Profile updated successfully!");
            //             this.showActions = false;
            //         } else {
            //             alert("Update failed: " + result.data);
            //         }
            //     } catch (err) {
            //         console.error(err);
            //         alert("Something went wrong. Please try again.");
            //     }
            // }

            async submitConfirmed() {
                Swal.fire({
                    title: "Save Changes?",
                    text: "Do you want to update your profile?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes, update",
                    customClass: {
                        confirmButton: 'my-confirm-btn',
                        cancelButton: 'my-cancel-btn'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Updating...",
                            text: "Please wait while we save your changes.",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                                
                                let loader = document.getElementById('photo-loader');
                                if (loader) loader.style.display = 'block';
                                let formData = new FormData();
                                formData.append("action", "update_user_profile");
                                formData.append("id", this.user.id);
                                formData.append("firstname", this.user.firstname);
                                formData.append("lastname", this.user.lastname);
                                formData.append("nickname", this.user.nickname);
                                formData.append("email", this.user.email);
                                // Profile image file
                                let fileInput = document.querySelector('input[type="file"]');
                                if (fileInput && fileInput.files[0]) {
                                    formData.append("profile_photo", fileInput.files[0]);
                                }
                                fetch(ajaxurl, {
                                    method: "POST",
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (loader) loader.style.display = 'none';
                                    if (data.success) {
                                        Swal.fire({
                                            title: "Updated!",
                                            text: "Your profile has been updated.",
                                            icon: "success",
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            showConfirmButton: false
                                        });
                                        setTimeout(() => {
                                            this.showActions = false;
                                            location.reload();
                                        }, 1000);
                                    } else {
                                        Swal.fire("Error", data.data || "Failed to update profile.", "error");
                                    }
                                })
                                .catch(error => {
                                    if (loader) loader.style.display = 'none';
                                    console.error("Request failed:", error);
                                    Swal.fire("Error", "Something went wrong.", "error");
                                });
                            }
                        });
                    }
                });
            }
        };
    }

</script>

<style>
.modal-content{
    max-width: 500px;
    max-height: 800px;
}
.profile-header {
    background-image: url('/wp-content/uploads/2025/02/profile-banner.png');
    height: 150px;
    border-radius: 10px 10px 0 0;
    background-size: cover;
    background-repeat: no-repeat;
}
.profile-img {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    border: 10px solid white;
    margin-top: -60px;
}
.card {
    border-radius: 10px;
}
.profile-details p {
    display: flex;
    justify-content: space-between;
    flex-direction: row;
}
.profile-details p span {
    font-weight: 600;
    font-size: 14px;
    color: #101010;
    display: flex;
}
.profile-details h5 {
    font-size: 16px;
    font-weight: 700;
    color: #101010;
    margin-bottom: 40px;
}
.profile-details {
    padding: 40px;
}
.profile-details .card-body.card-details {
    padding: 40px;
}
.profile-details .card-body.card-details p {
    font-weight: 300;
    font-size: 14px;
    color: #101010 !important;
}
.row h4 {
    font-size: 24px;
    font-weight: 600;
    color: #101010;
    margin-bottom: 18px;
}
.row p {
    font-size: 16px;
    font-weight: 300;
    color: #717171 !important;
}
.profile-edit {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
}
.profile-edit-button {
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
    align-items: center;
    padding: 0;
}
button.btn-edit {
    padding: 0;
    border: unset;
    color: #FFFFFF !important;
}
button.btn-edit:hover, button.btn-edit:focus {
    background-color: transparent;
}
.btn-update {
    background-color: #2194FF;
    box-shadow: -3px 14px 25px -10px #92b4d0;
    padding: 15px 20px;
    border-radius: 10px;
}
.btn-update:hover {
    background-color: #6494FA;
    cursor: pointer;
}
/* Media Queries */
@media only screen and (max-width: 768px) {
    .profile-edit {
        align-items: center;
    }
    .profile-edit-button {
        justify-content: center;
    }
}
</style>

<?php get_footer('admin'); ?>