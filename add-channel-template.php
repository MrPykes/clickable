<?php
/**
 * Template Name:  Add-Channels Template
 */

get_header('admin');

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    div.card.p-4{
        padding: 32px 40px 32px 40px !important;
    }
    div.container.mt-5{
        margin-top: 0px !important;
        padding-top: 56px !important;
    }
    div.add-channel-container {
        font-family: Sora;
        padding-top: 50px;
    }
    .breadcrumb{
        font-weight: 300;
        font-size: 14px;
        column-gap: 24px;
    }
    .breadcrumb-item a{
        color: #717171;
        text-decoration: none;
    }
    .breadcrumb-item+.breadcrumb-item::before {
        float: left;
        padding-right: var(--bs-breadcrumb-item-padding-x);
        color: var(--bs-breadcrumb-divider-color);
        content: var(--bs-breadcrumb-divider, ">");
        margin-right: 24px;
    }
    h2.title-form{
        font-weight: 600;
        font-size: 30px;
        letter-spacing: -0.03em;
        color: #101010;
        margin-bottom: 40px;
    }
    p.title-desc{
        font-weight: 300;
        font-size: 16px;
        color: #101010;
    }
    label.detail-desc{
        font-weight: 700;
        font-size: 16px;
        color: #101010;
    }
    label.input-label{
        color: #101010;
        font-size: 14px;
        font-weight: 300;
        margin-bottom: 12px;
    }
    form.add-channel-form {
        display: flex;
        flex-direction: column;
        row-gap: 64px;
    }
    div.add-channel-url{
        display: flex;
        flex-direction: column;
        row-gap: 40px;
    }
    input.form-control {
        border: solid 1px #D0D0D0 ;
        border-radius: 10px !important;
    }
    .url-form{
        display: flex;
        justify-content: space-between;
        column-gap: 10px;
    }
    .mb-3.url-div {
        display: flex;
        flex-direction: column;
    }
    div.add-channel-details {
        display: flex;
        flex-direction: column;
        row-gap: 40px;
    }
    div.channel-details{
        display: flex;
        flex-direction: column;
        row-gap: 24px;
    }
    div.btn-add-channel{
        align-self: flex-end;
    }
    button.btn-form {
        font-family: sora;
        font-weight: 600;
        background-color: #2194FF; 
        color: white; 
        padding: 15px 20px 15px 20px;
        border: solid 1px #2194FF;
        border-radius: 10px;
        font-size: 14px;
        cursor: pointer;
        transition: box-shadow 0.3s ease, transform 0.2s ease;
        box-shadow: 0px 10px 20px rgba(214, 225, 235, 0.2);
    }
    button.btn-form:hover {
        background-color: #fff;
        color: #0165C2;
        border: solid 1px #0165C2;
    }
    button.swal2-confirm {
        background-color: #0165C2;
        color: #fff;
        border: solid 1px #0165C2;
    }
    button.swal2-confirm:hover {
        background-color: #fff;
        color: #0165C2;
        border: solid 1px #0165C2;
    }
</style>
<div class="container-fluid p-0 parent-main">
<?php get_template_part('admin-sidebar');  ?>
    <div class="main-content">
        <div class="add-channel-container">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/channels">CHANNELS</a></li>
                        <li class="breadcrumb-item active" aria-current="page">ADD CHANNEL</li>
                    </ol>
                </nav>
                <h2 class="title-form">Add Channel</h2>
                <!-- <p class="title-desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique.</p> -->
                <div class="card p-4">
                    <div x-data="channelForm()" class="add-channel-form">
                        <form @submit.prevent="submitChannel" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                            <div class="add-channel-url">
                                <label class="detail-desc">Input URL here:</label>
                                <div class="mb-3 url-div">
                                    <label class="input-label">Channel URL</label>
                                    <div class="url-form">
                                        <input type="text" class="form-control" x-model="channel_url" name="channel_url">
                                        <!-- <button type="button" class="btn-form">Generate</button> -->
                                        <!-- <button type="button" class="btn-form" @click="checkChannelUrl()">Generate</button> -->
                                    </div>
                                </div>
                            </div>
                            <div class="add-channel-details">
                                <label class="detail-desc">Channel Details</label>
                                <div class="channel-details">
                                    <div class="mb-3">
                                        <label class="input-label">Name</label>
                                        <input type="text" class="form-control" x-model="channel_name" name="channel_name" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="input-label">Description</label>
                                            <input type="text" class="form-control" x-model="channel_description" name="channel_description" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="input-label">Date Created</label>
                                            <!-- <input type="date" id="startDatePicker" x-model="date_created" 
                                                class="form-control" @click="$el.showPicker && $el.showPicker()" @change="filterData()"> -->
                                                <input type="date" x-model="date_created" 
                                                    name="date_created" class="form-control"
                                                    @click="$el.showPicker && $el.showPicker()" />
 
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <input type="hidden" name="channel_nonce" value="<?php echo wp_create_nonce('add_channel_nonce'); ?>">
                            <input type="hidden" name="action" value="add_channel">
                            <div class="btn-add-channel">
                                <button type="submit" name="submit_channel" class="btn-form">Add Channel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    document.addEventListener("alpine:init", () => {
        Alpine.data("channelForm", () => ({
            channel_url: "",
            channel_name: "",
            channel_description: "",
            date_created: new Date().toISOString().split('T')[0],
            success: <?php echo isset($_GET['success']) ? 'true' : 'false'; ?>,
            // submitChannel() {
            //     Swal.fire({
            //         title: 'Saving Channel...',
            //         text: 'Please wait while we save your channel.',
            //         allowOutsideClick: false,
            //         didOpen: () => {
            //             Swal.showLoading();
            //         }
            //     });

            //     const formData = new FormData();
            //     formData.append('action', 'add_channel');
            //     formData.append('channel_nonce', '<?php echo wp_create_nonce("add_channel_nonce"); ?>');
            //     formData.append('channel_url', this.channel_url);
            //     formData.append('channel_name', this.channel_name);
            //     formData.append('channel_description', this.channel_description);
            //     formData.append('date_created', this.date_created);

            //     fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
            //         method: 'POST',
            //         body: formData
            //     })
            //     .then(res => res.json())
            //     .then(data => {
            //         if (data.success && data.data && data.data.redirect_url) {
            //             Swal.fire({
            //                 icon: 'success',
            //                 title: 'Channel Saved!',
            //                 showConfirmButton: false,
            //                 timer: 1200
            //             });

            //             setTimeout(() => {
            //                 window.location.href = data.data.redirect_url;
            //             }, 1300);
            //         } else {
            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Error',
            //                 text: data.data?.message || 'Something went wrong while saving.'
            //             });
            //         }
            //     })
            //     .catch(error => {
            //         console.error(error);
            //         Swal.fire({
            //             icon: 'error',
            //             title: 'Request Failed',
            //             text: 'Please check your connection or try again later.'
            //         });
            //     });
            // }
            submitChannel() {
                Swal.fire({
                    title: 'Saving Channel...',
                    text: 'Please wait while we save your channel.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData();
                formData.append('action', 'add_channel');
                formData.append('channel_nonce', '<?php echo wp_create_nonce("add_channel_nonce"); ?>');
                formData.append('channel_url', this.channel_url);
                formData.append('channel_name', this.channel_name);
                formData.append('channel_description', this.channel_description);
                formData.append('date_created', this.date_created);

                fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data?.redirect_url) {
                        Swal.fire({
                            title: 'Channel Saved!',
                            text: 'Redirecting...',
                            icon: 'success',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        setTimeout(() => {
                            window.location.href = data.data.redirect_url;
                        }, 1200);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.data?.message || 'Something went wrong while saving.'
                        });
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: 'Please check your connection or try again later.'
                    });
                });
            }

            

        }));
    });

// function channelForm() {
//     return {
//         channel_url: '',
//         channel_name: '',
//         channel_description: '',
//         date_created: '',
        
//         // checkChannelUrl() {
//         //     if (!this.channel_url.trim()) {
//         //         Swal.fire({
//         //             icon: 'warning',
//         //             title: 'Missing URL',
//         //             text: 'Please enter a Channel URL before generating.',
//         //         });
//         //         return;
//         //     }
//         //     const ajaxUrl = '/wp-admin/admin-ajax.php';
//         //     fetch(`${ajaxUrl}?action=check_channel_url&channel_url=${encodeURIComponent(this.channel_url)}`)
//         //         .then(res => res.json())
//         //         .then(data => {
//         //             if (data.exists) {
//         //                 Swal.fire({
//         //                     icon: 'error',
//         //                     title: 'URL Already In Use',
//         //                     text: 'That channel URL is already taken. Please try a different one.',
//         //                 });
//         //             } else {
//         //                 Swal.fire({
//         //                     icon: 'success',
//         //                     title: 'URL Available',
//         //                     text: 'The channel URL is available!',
//         //                 });
//         //             }
//         //         })
//         //         .catch(error => {
//         //             console.error(error);
//         //             Swal.fire({
//         //                 icon: 'error',
//         //                 title: 'Error',
//         //                 text: 'Something went wrong while checking the URL.',
//         //             });
//         //         });
//         // },
        
//         filterData() {
//             setTimeout(() => { // Prevent infinite loop
//                 const startYear = parseInt(document.getElementById('startDatePicker').value.split('-')[0]) || 2019;
//                 const endYear = parseInt(document.getElementById('endDatePicker').value.split('-')[0]) || 2025;
//                 this.filteredData.labels = this.data.labels.filter(year => year >= startYear && year <= endYear);
//                 const startIndex = this.data.labels.indexOf(this.filteredData.labels[0]);
//                 const endIndex = this.data.labels.indexOf(this.filteredData.labels[this.filteredData.labels.length - 1]) + 1;
//                 this.filteredData.values = this.data.values.slice(startIndex, endIndex);
//                 this.renderChart();
//             }, 0);
//         }
//     };
// }

</script>

<!-- media queries -->
<style>
    @media only screen and (max-width: 768px) {
        div.add-channel-container {
            padding: 0;
        }
    }
    @media only screen and (max-width: 767px) {
        .parent-main {
            display: flex;
            flex-direction: row;
        }
        .menuLogoImgText {
            width: 180px;
        }
        .main-content {
            width: 520px;
            padding: 20px;
        }
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php get_footer('admin');?>