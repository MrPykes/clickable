<?php
get_header('admin');
$userId =  get_the_ID();
$profile_photo = get_field('profile_photo', $userId);
$fullName = get_field('fullname', $userId);

//Profile Section
$teamContract = [];
$team_query = new WP_Query([
    'post_type'      => 'team',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'post__in'       => [get_the_ID()],
]);

if ($team_query->have_posts()):
    while ($team_query->have_posts()):
        $team_query->the_post();
        $image = get_field('profile_photo');

        // Role handling
        $role_field = get_field('role');
        $role_title = '';
        if ($role_field) {
            if (is_array($role_field)) {
                $role_title = array_map(function ($post) {
                    return is_numeric($post) ? get_the_title($post) : (is_object($post) ? get_the_title($post->ID) : $post);
                }, $role_field);
            } else {
                $role_title = is_numeric($role_field) ? get_the_title($role_field) : (is_object($role_field) ? get_the_title($role_field->ID) : $role_field);
            }
        }

        // Payment platform handling
        $payment_field = get_field('payment_platform');
        $payment_title = '';
        if ($payment_field) {
            if (is_array($payment_field)) {
                $first = reset($payment_field);
                $payment_title = is_numeric($first) ? get_the_title($first) : (is_object($first) ? get_the_title($first->ID) : $first);
            } else {
                $payment_title = is_numeric($payment_field) ? get_the_title($payment_field) : (is_object($payment_field) ? get_the_title($payment_field->ID) : $payment_field);
            }
        }

        // Currency handling
        $currency_field = get_field('currency');
        $currency_title = '';
        if ($currency_field) {
            if (is_array($currency_field)) {
                $first = reset($currency_field);
                $currency_title = is_numeric($first) ? get_the_title($first) : (is_object($first) ? get_the_title($first->ID) : $first);
            } else {
                $currency_title = is_numeric($currency_field) ? get_the_title($currency_field) : (is_object($currency_field) ? get_the_title($currency_field->ID) : $currency_field);
            }
        }

        $teamContract[] = [
            'id'              => get_the_ID(),
            'name'            => get_field('fullname'),
            'channel'         => get_field('channel'),
            'role'            => $role_title,
            'status'          => get_field('team_status'),
            'discord'         => get_field('discord_name'),
            // 'email'           => get_field('main_email'),
            'email'           => get_field('email_address'),
            'address'         => get_field('full_address'),
            'paymentPlatform' => $payment_title,
            'currency'        => $currency_title,
            'image'           => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/02/Placeholder-Image-13.png',
            'payid'           => get_field('email_pay_id'),
            'fullName'       => get_field('fullname') ?? ''
        ];
    endwhile;
    wp_reset_postdata();
endif;


// Prepare member's channels
$channels_data = [];
$member_channels = get_field('your_channel', get_the_ID()) ?: [];

foreach ($member_channels as $channel_id) {
    $image = get_field('channel_profile_photo', $channel_id);
    $desc  = get_field('channel_description', $channel_id) ?: '';
    $channels_data[] = [
        'id'    => (int) $channel_id,
        'name'  => get_field('channel_name', $channel_id),
        'desc'  => wp_trim_words($desc, 15, '...'),
        'since' => get_the_date('', $channel_id),
        'image' => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg',
        'link'  => get_permalink($channel_id),
    ];
}

// Active channels
$all = [];
$all_channels_raw = get_posts([
    'post_type'      => 'channel',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'channel_status',
            'value'   => 'active',
            'compare' => '='
        ]
    ]
]);
foreach ($all_channels_raw as $ch) {
    $image = get_field('channel_profile_photo', $ch->ID);
    $all[] = [
        'id'    => (int) $ch->ID,
        'name'  => get_field('channel_name', $ch->ID),
        'image' => $image['sizes']['medium'],
        'link'  => get_permalink($ch->ID),
    ];
}


// Invoice table query
$args = array(
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => 'status',
            'value'   => array('PAID', 'PENDING'),
            'compare' => 'IN'
        ),
        array(
            'relation' => 'OR',
            array(
                'key'     => 'user_id',
                'value'   => $userId,
                'compare' => '='
            ),
            array(
                'key'     => 'fullname',
                'value'   => $fullName,
                'compare' => '='
            )
        )
    )
);
// Paid invoice for chart
$args_chart = array(
    'post_type'      => 'invoice-creator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => 'status',
            'value'   => 'PAID',
            'compare' => '='
        ),
        array(
            'relation' => 'OR',
            array(
                'key'     => 'user_id',
                'value'   => $userId,
                'compare' => '=',
                'type'    => 'NUMERIC'
            ),
            array(
                'key'     => 'fullname',
                'value'   => $fullName,
                'compare' => '='
            )
        )
    )
);


$invoices_query = new WP_Query($args);
$invoice_data   = [];

if ($invoices_query->have_posts()) {
    while ($invoices_query->have_posts()) {
        $invoices_query->the_post();

        $tasks      = get_field('your_tasks') ?: [];
        $deductions = get_field('your_deductions') ?: [];

        $channel_id   = $tasks[0]['tasks_channel'] ?? '';
        $channel_name = '';

        if ($channel_id) {
            if (is_array($channel_id)) {
                // Relationship field (array of IDs or WP_Post objects)
                $first = reset($channel_id);

                if (is_numeric($first)) {
                    $channel_name = get_the_title((int) $first);
                } elseif (is_object($first) && isset($first->ID)) {
                    $channel_name = get_the_title($first->ID);
                }
            } elseif (is_numeric($channel_id)) {
                // Just an ID
                $channel_name = get_the_title((int) $channel_id);
            } elseif (is_object($channel_id) && isset($channel_id->ID)) {
                // Single post object
                $channel_name = get_the_title($channel_id->ID);
            } else {
                // Fallback plain text
                $channel_name = $channel_id;
            }
        }
        // Get payment method(s)
        $platforms = get_field('payment_method');
        $platform_titles = [];

        if ($platforms) {
            if (is_array($platforms)) {
                foreach ($platforms as $platform_id) {
                    $platform_titles[] = get_the_title($platform_id);
                }
            } else {
                $platform_titles[] = get_the_title($platforms);
            }
        }

        $invoice_data[] = [
            'id'             => get_the_ID(),
            'invoiceNumber'  => get_field('invoice_number'),
            'date'         => get_field('date_submitted'),
            'status'         => get_field('status'),
            // 'platform'       => get_field('payment_method'),
            'platform'       => $platform_titles,
            'channel'        => $channel_name,
            'hours'          => $tasks[0]['tasks_hours'] ?? '',
            'rate'           => $tasks[0]['tasks_rate'] ?? '',
            'deduct_channel' => $deductions[0]['deduct_channel'] ?? '',
            'deduct_hours'   => $deductions[0]['deduct_hours'] ?? '',
            'deduct_rate'    => $deductions[0]['deduct_rate'] ?? '',
            'amount'    => floatval(get_field('amount')) ?: 0,
            'fullName'       => get_field('fullname') ?? ''
        ];

    }
    wp_reset_postdata();
}
// Paid invoice for chart
$chart_query = new WP_Query($args_chart);
$chart_data  = [];

if ($chart_query->have_posts()) {
    while ($chart_query->have_posts()) {
        $chart_query->the_post();

        $amount = floatval(get_field('amount')) ?: 0;
        $date   = get_field('date_submitted');

        $chart_data[] = [
            'date'   => $date ? date('Y-m-d', strtotime($date)) : '',
            'amount' => $amount
        ];
    }
    wp_reset_postdata();
}


?>
<script type="application/json" id="team-contract-data">
    <?php echo wp_json_encode($teamContract); ?>
</script>
<script type="application/json" id="channels-data">
    <?php echo wp_json_encode($channels_earn); ?>
</script>
<script type="application/json" id="invoices-data">
    <?php echo wp_json_encode([ 'invoices' => $invoice_data ]); ?>
</script>
<script type="application/json" id="chart-data">
    <?php echo wp_json_encode([ 'chart' => $chart_data ]); ?>
</script>




<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar');  ?>
    <!-- <div class="main-content"> -->
    <div class="main-content ">
        <main class="flex-grow-1 p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/team/">TEAM MEMBERS</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="text-transform: uppercase;">
                        <?php echo get_field('fullname', get_the_ID()) ?></li>
                </ol>
            </nav>
            <!-- Profile Section -->
            <div class="container-fluid mt-4 p-0" x-data="teamContractor()">
                <div class="card profile-container">
                    <div class="profile-header position-relative">
                    </div>
                    <div class="row p-4 m-0 z-0">
                        <div class="row m-0 z-0 main-prof">
                            <div class="col-lg-10 col-md-12">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-5 col-md-12 d-md-flex justify-content-lg-start justify-content-md-center">
                                        <img class="img-fluid profile-img" 
                                            :src="profile.image" alt="Profile Picture">
                                    </div>
                                    <div class="col-xl-9 col-lg-7 col-md-12 profile-edit">
                                        <h4 class="mt-2" x-text="profile.discord ?? 'No discord name indicated.'"
                                            :class="!profile.discord ? 'text-danger' : ''"></h4>
                                        <p class="text-muted" x-text="profile.fullName ?? 'No fullname indicated'"
                                            :class="!profile.fullName ? 'text-danger' : ''"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <php 
                            echo "<pre>";
                            print_r(get_fields(get_the_ID()));
                            echo "</pre>";
                        ?> -->
                        
                        <div class="row m-0 row-gap-4 profile-details">
                            <div class="col-lg-6 col-md-12 d-flex">
                                <div class="card w-100">
                                    <div class="card-body card-details">
                                        <h5>Details</h5>
                                        <p>Discord: <span x-text="profile.discord ?? 'No discord name indicated.'"
                                            :class="!profile.discord ? 'text-danger' : ''"></span></p>
                                        <p>Status: <span>Contractor</span></p>
                                        <p>Email: <span x-text="profile.email ?? 'No email indicated.'"
                                                :class="!profile.email ? 'text-danger' : ''"></span></p>
                                        <p>Address: <span x-text="profile.address ?? 'No address indicated.'"
                                                :class="!profile.address ? 'text-danger' : ''"></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="card w-100">
                                    <div class="card-body card-details">
                                        <h5>Payment Details</h5>
                                        <p>Payment Method: <span x-text="profile.paymentPlatform ?? 'No method indicated.'"
                                            :class="!profile.paymentPlatform ? 'text-danger' : ''"></span></p>
                                        <p>Currency: <span x-text="profile.currency ?? 'No currency indicated.'"
                                                :class="!profile.currency ? 'text-danger' : ''"></span></p>
                                        <p>Email/Pay ID: <span x-text="profile.payid ?? 'No Pay ID indicated.'"
                                                :class="!profile.payid ? 'text-danger' : ''"></span></p>
                                        <p>Role: <span x-text="profile.role ?? 'No role indicated.'"
                                            :class="!profile.role ? 'text-danger' : ''"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function teamContractor() {
                    return {
                        teamContract: [],
                        profile: {},

                        init() {
                            const el = document.getElementById('team-contract-data');
                            if (el) {
                                try {
                                    this.teamContract = JSON.parse(el.textContent);
                                    if (this.teamContract.length > 0) {
                                        this.profile = this.teamContract[0]; // first contractor
                                    }
                                } catch (e) {
                                    console.error("Failed to parse teamContract JSON", e);
                                }
                            }
                            // console.log('data', this.profile);
                        }
                    };
                }
            </script>
            <!-- Active Roster Section -->
            <div class="container-fluid mt-4 p-0"  x-data="channelsData()">
                <div class="card earnings-container p-4">
                    <div class="col-md-12 channel-option">
                        <h5 class="fw-bold">Channels</h5>
                        <div>
                            <button class="add-rmv-btn" @click="showModal = true">
                                <img class="add-rmv-icon" src="/wp-content/uploads/2025/02/edit.svg" alt="">Add Channels
                            </button>
                        </div>
                    </div>
                    <div class="hidden confirmation-modal" x-show="showModal" 
                        x-transition x-bind:class="{ 'hidden': !showModal }">
                        <div class="modal-content">
                            <div>
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                    <i class="fas fa-bell" style="font-size: 46px; color: #2194FF;"></i>
                                    <p style="text-align: center;">
                                        Assigned <span style="color: #2194FF; font-weight: 700;">
                                            <?php echo get_field('fullname', get_the_ID()) ?>
                                        </span> to the new Channel?
                                    </p>
                                </div>
                            </div>
                            <form>
                                <div class="mb-3">
                                    <select class="form-control" multiple x-model="selectedChannels">
                                        <template x-for="channel in unjoinedChannels" :key="channel.id">
                                            <option :value="channel.id" x-text="channel.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="mb-3" x-show="selectedChannels.length > 0">
                                    <label class="input-label">Selected Channels</label>
                                    <input type="text" class="form-control" readonly :value="selectedChannelNames">
                                </div>
                                <div class="modal-actions">
                                    <button type="button" @click="submitConfirmed" class="confirm-btn">Save Changes</button>
                                    <button type="button" @click="showModal = false" class="cancel-btn">Cancel</button>

                                </div>
                            </form>
                        </div>
                    </div>

                    
                    <!-- Channel Carousel -->
                    <div class="row">
                        <div class="col-md-12">
                            <template x-if="channels.length === 0">
                                <div class="text-center mt-4">
                                    <p style="text-center py-4 text-muted">No channel assigned.</p>
                                </div>
                            </template>

                            <div id="news-slider" class="owl-carousel" x-show="channels.length > 0">
                                <template x-for="channel in channels" :key="channel.id">
                                    <a :href="channel.link">
                                        <div class="post-slide">
                                            <div class="card">
                                                <div class="card-desc">
                                                    <div>
                                                        <img class="channel-photo" width="auto" height="70" :src="channel.image" alt="Profile Photo">
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h3 x-text="channel.name"></h3>
                                                        <p x-text="channel.desc"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <script>
            function channelsData() {
                return {
                    showModal: false,
                    // Existing channels this user is already assigned to
                    channels: <php
                        $channels_data = [];
                        $member_channels = get_field('your_channel', get_the_ID()) ?: [];

                        // foreach ($member_channels as $channel_id) {
                        //     $image = get_field('channel_profile_photo', $channel_id);
                        //     $channels_data[] = [
                        //         'id'    => (int) $channel_id,
                        //         'name'  => get_field('channel_name', $channel_id),
                        //         'desc'  => get_field('channel_description', $channel_id),
                        //         'since' => get_the_date('', $channel_id),
                        //         'image' => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg',
                        //         'link'  => get_permalink($channel_id),
                        //     ];
                        // }
                        foreach ($member_channels as $channel_id) {
                            $image = get_field('channel_profile_photo', $channel_id);
                            $desc  = get_field('channel_description', $channel_id) ?: '';
                            $channels_data[] = [
                                'id'    => (int) $channel_id,
                                'name'  => get_field('channel_name', $channel_id),
                                'desc'  => wp_trim_words($desc, 15, '...'), // limit to 30 words
                                'since' => get_the_date('', $channel_id),
                                'image' => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg',
                                'link'  => get_permalink($channel_id),
                            ];
                        }
                        echo json_encode($channels_data);
                    ?>,
                    // All available channels
                    allChannels: <php
                        $all = [];
                        $all_channels_raw = get_posts([
                            'post_type' => 'channel',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                            'meta_query'     => [
                                [
                                    'key'     => 'channel_status',
                                    'value'   => 'active',
                                    'compare' => '='
                                ]
                            ]
                        ]);
                        foreach ($all_channels_raw as $ch) {
                            $image = get_field('channel_profile_photo', $ch->ID);
                            $all[] = [
                                'id'    => (int) $ch->ID,
                                'name'  => get_field('channel_name', $ch->ID),
                                'image' => $image['sizes']['medium'],
                                'link'  => get_permalink($ch->ID),
                            ];
                        }
                        echo json_encode($all);
                    ?>,
                    selectedChannels: [],
                    submitConfirmed(event) {
                        event.preventDefault();
                        Swal.fire({
                            title: "Assign Channels?",
                            text: "Do you want to assign the selected channel to <php echo get_field('fullname') ?>?",
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: "Yes, assign",
                            customClass: {
                                confirmButton: 'my-confirm-btn',
                                cancelButton: 'my-cancel-btn'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: "Assigning...",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                        const formData = new FormData();
                                        formData.append('action', 'assign_channels_to_team');
                                        formData.append('team_id', <php echo get_the_ID(); ?>);
                                        formData.append('selected_channels', JSON.stringify(this.selectedChannels));
                                        fetch('/wp-admin/admin-ajax.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(res => res.json())
                                        .then(response => {
                                            if (response.success) {
                                                this.showModal = false;
                                                // Proceed to reload after short delay
                                                setTimeout(() => {
                                                    location.reload();
                                                }, 1000);
                                            } else {
                                                Swal.fire("Error", response.data || "Failed to assign channels.", "error");
                                            }
                                        })
                                        .catch(() => {
                                            Swal.fire("Error", "Something went wrong.", "error");
                                        });
                                    }
                                });
                            }
                        });
                    },
                    // Channels not yet assigned to the member
                    get unjoinedChannels() {
                        const currentIDs = this.channels.map(c => parseInt(c.id));
                        return this.allChannels.filter(ch => !currentIDs.includes(parseInt(ch.id)));
                    },
                    // Display selected channel names
                    get selectedChannelNames() {
                        return this.selectedChannels.map(id => {
                            const ch = this.allChannels.find(c => c.id == id);
                            return ch ? ch.name : '';
                        }).join(', ');
                    }
                };
            }
            </script> -->
            <script>
                function channelsData() {
                    return {
                        showModal: false,
                        channels: <?php echo json_encode($channels_data); ?>,
                        allChannels: <?php echo json_encode($all); ?>,
                        selectedChannels: [],
                        
                        submitConfirmed(event) {
                            event.preventDefault();
                            Swal.fire({
                                title: "Assign Channels?",
                                text: "Do you want to assign the selected channel to <?php echo esc_js(get_field('fullname')); ?>?",
                                icon: "question",
                                showCancelButton: true,
                                confirmButtonText: "Yes, assign",
                                customClass: {
                                    confirmButton: 'my-confirm-btn',
                                    cancelButton: 'my-cancel-btn'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire({
                                        title: "Assigning...",
                                        allowOutsideClick: false,
                                        allowEscapeKey: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                            const formData = new FormData();
                                            formData.append('action', 'assign_channels_to_team');
                                            formData.append('team_id', <?php echo get_the_ID(); ?>);
                                            formData.append('selected_channels', JSON.stringify(this.selectedChannels));

                                            fetch('/wp-admin/admin-ajax.php', {
                                                method: 'POST',
                                                body: formData
                                            })
                                            .then(res => res.json())
                                            .then(response => {
                                                if (response.success) {
                                                    this.showModal = false;
                                                    setTimeout(() => location.reload(), 1000);
                                                } else {
                                                    Swal.fire("Error", response.data || "Failed to assign channels.", "error");
                                                }
                                            })
                                            .catch(() => {
                                                Swal.fire("Error", "Something went wrong.", "error");
                                            });
                                        }
                                    });
                                }
                            });
                        },

                        get unjoinedChannels() {
                            const currentIDs = this.channels.map(c => parseInt(c.id));
                            return this.allChannels.filter(ch => !currentIDs.includes(parseInt(ch.id)));
                        },

                        get selectedChannelNames() {
                            return this.selectedChannels.map(id => {
                                const ch = this.allChannels.find(c => c.id == id);
                                return ch ? ch.name : '';
                            }).join(', ');
                        }
                    };
                }
            </script>
            <!-- Invoice History Section -->
            <div class="container-fluid mt-4 p-0 history-section">
                <div class="card p-4 invoice-section">
                    <div x-data="invoiceTable()">
                        <div class="btn-dl-invoice">
                            <h5 class="fw-bold">Invoice History</h5>
                            <button class="btn-outline-secondary dl-btn" @click="downloadReport()">
                                <img class="dl-icon" src="/wp-content/uploads/2025/03/dl-icon.png" alt="Download Icon"> Download Report
                            </button>

                        </div>
                        <div class="invoice-table-wrapper">
                            <template x-if="filteredInvoices().length === 0">
                                <div class="text-center py-4 text-muted">No invoice available.</div>
                            </template>

                            <template x-if="filteredInvoices().length > 0">
                                <table border="1" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Invoice Number</th>
                                            <th>Channel</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Platform</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="invoice in paginatedInvoices()" :key="invoice.id">
                                            <tr>
                                                <td class="styl-inv-txt" x-text="invoice.invoiceNumber ?? 'No invoice number'"
                                                    :class="!invoice.invoiceNumber ? 'text-danger' : ''"></td>
                                                <td class="styl-inv-txt" x-text="invoice.channel ?? 'No channel indicated'"
                                                    :class="!invoice.channel ? 'text-danger' : ''"></td>
                                                <td class="styl-inv-txt" x-text="invoice.date ?? 'No date indicated'"
                                                    :class="!invoice.date ? 'text-danger' : ''"></td>
                                                <td class="styl-inv-txt"x-text="'$' + invoice.amount.toLocaleString() ?? '0'"
                                                    :class="!invoice.amount ? 'text-danger' : ''"></td>
                                                <td class="styl-inv-txt" x-text="invoice.platform ?? 'No method indicated'"
                                                    :class="!invoice.platform ? 'text-danger' : ''"></td>
                                                <td>
                                                    <span :class="invoice.status === 'PAID' ? 'badge bg-success' : 'badge bg-danger'" 
                                                    x-text="invoice.status"></span>
                                                </td>
                                                <td class="tblActions">
                                                    <span @click="downloadInvoice(invoice)">
                                                        <img class="dl-icon" src="/wp-content/uploads/2025/03/dl-icon.png" alt="Download Icon">
                                                    </span>
                                                    <span @click="deleteInvoice(invoice.id)">
                                                        <img class="dlt-icon" src="/wp-content/uploads/2025/03/dlt-icon.png" alt="Delete Icon">
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </template>
                        </div>

                        <div class="pagination">
                            <button class="btn" @click="prevPage()" :disabled="currentPage === 1">
                                <img class="prev-icon" src="/wp-content/uploads/2025/02/Vector-5.svg" alt="Previous"> Previous</button>
                            <template x-for="page in totalPages()" :key="page">
                                <button class="btn" :class="currentPage === page ? 'active' : ''" @click="currentPage = page" x-text="page"></button>
                            </template>
                            <button class="btn" @click="nextPage()" :disabled="currentPage === totalPages()">Next
                                <img class="next-icon" src="/wp-content/uploads/2025/03/next.png" alt="Next"></button>
                        </div>
                    </div>
                </div>
                <script>
                    // function invoiceTable() {
                    //     return {
                    //         invoices: <php
                    //             $args = array(
                    //                 'post_type'      => 'invoice-creator',
                    //                 'posts_per_page' => -1,
                    //                 'post_status'    => 'publish',
                    //                 'meta_query'     => array(
                    //                     array(
                    //                         'key'     => 'status',
                    //                         'value'   => array('PAID', 'PENDING'),
                    //                         'compare' => 'IN'
                    //                     ),
                    //                     array(
                    //                         'key'     => 'user_id',
                    //                         'value'   =>  $userId,
                    //                         'compare' => '='
                    //                     )
                    //                 )
                    //             );
                    //             $invoices_query = new WP_Query($args);
                    //             $invoice_data = [];
                    //             if ($invoices_query->have_posts()) {
                    //                 while ($invoices_query->have_posts()) {
                    //                     $invoices_query->the_post();
                    //                     $invoice_data[] = [
                    //                         'id'         => get_the_ID(),
                    //                         'invoiceNumber'   => get_field('invoice_number'),
                    //                         'date'       => get_field('date_submitted'),
                    //                         'status'     => get_field('status'),
                    //                         'platform'   => get_field('payment_method'),
                    //                         'channel'    => get_field('tasks')[0]['tasks_channel'],
                    //                         'hours'    => get_field('tasks')[0]['tasks_hours'],
                    //                         'rate'    => get_field('tasks')[0]['tasks_rate'],
                    //                         'deduct_channel'    => get_field('deductions')[0]['deduct_channel'],
                    //                         'deduct_hours'    => get_field('deductions')[0]['deduct_hours'],
                    //                         'deduct_rate'    => get_field('deductions')[0]['deduct_rate'],
                    //                         'amount'   => get_field('amount'),
                    //                         'fullName'   => $fullName
                    //                     ];
                    //                 }
                    //                 wp_reset_postdata();
                    //             }
                    //             echo json_encode($invoice_data);
                    //         ?>,
                    //         filterStatus: '',
                    //         currentPage: 1,
                    //         itemsPerPage: 5,
                    //         sortKey: '',
                    //         sortAsc: true,
                    //         sortBy(key) {
                    //             if (this.sortKey === key) {
                    //                 this.sortAsc = !this.sortAsc;
                    //             } else {
                    //                 this.sortKey = key;
                    //                 this.sortAsc = true;
                    //             }
                    //         },
                    //         get sortedInvoices() {
                    //             let sorted = [...this.filteredInvoices()];
                    //             if (!this.sortKey) return sorted;
                    //             return sorted.sort((a, b) => {
                    //                 let valA = a[this.sortKey];
                    //                 let valB = b[this.sortKey];

                    //                 if (this.sortKey === 'amount') {
                    //                     valA = parseFloat(valA);
                    //                     valB = parseFloat(valB);
                    //                 } else if (this.sortKey === 'date') {
                    //                     valA = new Date(valA);
                    //                     valB = new Date(valB);
                    //                 } else {
                    //                     valA = valA?.toString().toLowerCase() || '';
                    //                     valB = valB?.toString().toLowerCase() || '';
                    //                 }
                    //                 return (valA > valB ? 1 : valA < valB ? -1 : 0) * (this.sortAsc ? 1 : -1);
                    //             });
                    //         },
                    //         filteredInvoices() {
                    //             return this.filterStatus ?
                    //                 this.invoices.filter(inv => inv.status === this.filterStatus) :
                    //                 this.invoices;
                    //         },
                    //         paginatedInvoices() {
                    //             let start = (this.currentPage - 1) * this.itemsPerPage;
                    //             let end = start + this.itemsPerPage;
                    //             return this.sortedInvoices.slice(start, end); // 🆕 use sortedInvoices
                    //         },
                    //         totalPages() {
                    //             return Math.ceil(this.filteredInvoices().length / this.itemsPerPage);
                    //         },

                    //         prevPage() {
                    //             if (this.currentPage > 1) this.currentPage--;
                    //         },

                    //         nextPage() {
                    //             if (this.currentPage < this.totalPages()) this.currentPage++;
                    //         },

                    //         deleteInvoice(id) {
                    //             this.invoices = this.invoices.filter(invoice => invoice.id !== id);
                    //             if (this.currentPage > this.totalPages()) this.currentPage = this.totalPages();
                    //         },
                    //         downloadInvoice(invoice) {
                    //             let header = "Invoice Number, Channel, Date, Amount, Platform, Status";
                    //             let content = `${invoice.invoiceNumber}, ${invoice.channel}, ${invoice.date}, ${invoice.amount}, ${invoice.platform}, ${invoice.status}`;
                    //             // Sanitize and uppercase fullName
                    //             let fullName = (invoice.fullName || 'Unknown').replace(/[^a-z0-9]/gi, '_').toUpperCase();
                    //             let csvContent = `${header}\n${content}`;
                    //             let blob = new Blob([csvContent], {
                    //                 type: "text/csv;charset=utf-8"
                    //             });
                    //             saveAs(blob, `${invoice.invoiceNumber}_${fullName}.csv`);
                    //         },
                    //         downloadReport() {
                    //             let header = "Invoice Number, Channel, Date, Amount, Platform, Status";
                    //             let data = this.filteredInvoices().map(inv =>
                    //                 `${inv.invoiceNumber}, ${inv.channel}, ${inv.date}, ${inv.amount}, ${inv.platform}, ${inv.status}`
                    //             ).join("\n");
                    //             let csvContent = `${header}\n${data}`;
                    //             let fullName = this.filteredInvoices()[0]?.fullName || 'Unknown';
                    //             fullName = fullName.replace(/[^a-z0-9]/gi, '_').toUpperCase();
                    //             let blob = new Blob([csvContent], {
                    //                 type: "text/csv;charset=utf-8"
                    //             });
                    //             saveAs(blob, `INVOICE_${fullName}.csv`);
                    //         },
                    //         deleteInvoice(id) {
                    //             Swal.fire({
                    //                 title: 'Are you sure?',
                    //                 text: "This will permanently delete the invoice.",
                    //                 icon: 'warning',
                    //                 showCancelButton: true,
                    //                 confirmButtonText: 'Yes, delete it!',
                    //                 customClass: {
                    //                     confirmButton: 'my-confirm-btn',
                    //                     cancelButton: 'my-cancel-btn'
                    //                 }
                    //             }).then((result) => {
                    //                 if (result.isConfirmed) {
                    //                     // AJAX request to delete invoice post
                    //                     fetch('<php echo admin_url("admin-ajax.php"); ?>', {
                    //                         method: 'POST',
                    //                         headers: {
                    //                             'Content-Type': 'application/x-www-form-urlencoded',
                    //                         },
                    //                         body: new URLSearchParams({
                    //                             action: 'delete_invoice_post',
                    //                             post_id: id
                    //                         })
                    //                     })
                    //                     .then(response => response.json())
                    //                     .then(result => {
                    //                         if (result.success) {
                    //                             // Remove from list
                    //                             this.invoices = this.invoices.filter(invoice => invoice.id !== id);
                    //                             if (this.currentPage > this.totalPages()) {
                    //                                 this.currentPage = this.totalPages();
                    //                             }
                    //                             Swal.fire({
                    //                                 title: 'Deleted!',
                    //                                 text: 'Invoice has been deleted.',
                    //                                 icon: 'success',
                    //                                 timer: 1500,
                    //                                 showConfirmButton: false
                    //                             });
                    //                         } else {
                    //                             Swal.fire('Error', result.data || 'Failed to delete invoice.', 'error');
                    //                         }
                    //                     })
                    //                     .catch(error => {
                    //                         console.error('Delete failed:', error);
                    //                         Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                    //                     });
                    //                 }
                    //             });
                    //         }

                    //     };
                    // }
                    
                    function invoiceTable() {
                        return {
                            filterStatus: '',
                            currentPage: 1,
                            itemsPerPage: 5,
                            sortKey: '',
                            sortAsc: true,
                            invoices: [],
                            
                            init() {
                                try {
                                    const raw = document.getElementById('invoices-data').textContent;
                                    const parsed = JSON.parse(raw);
                                    this.invoices = parsed.invoices || [];  
                                } catch (e) {
                                    this.invoices = [];
                                    console.error("Failed to parse invoices-data JSON:", e);
                                }
                                // console.log('Loaded invoices:', this.invoices);
                            },


                            sortBy(key) {
                                if (this.sortKey === key) {
                                    this.sortAsc = !this.sortAsc;
                                } else {
                                    this.sortKey = key;
                                    this.sortAsc = true;
                                }
                            },

                            get sortedInvoices() {
                                let sorted = [...this.filteredInvoices()];
                                if (!this.sortKey) return sorted;
                                return sorted.sort((a, b) => {
                                    let valA = a[this.sortKey];
                                    let valB = b[this.sortKey];

                                    if (this.sortKey === 'amount') {
                                        valA = parseFloat(valA);
                                        valB = parseFloat(valB);
                                    } else if (this.sortKey === 'date') {
                                        valA = new Date(valA);
                                        valB = new Date(valB);
                                    } else {
                                        valA = valA?.toString().toLowerCase() || '';
                                        valB = valB?.toString().toLowerCase() || '';
                                    }
                                    return (valA > valB ? 1 : valA < valB ? -1 : 0) * (this.sortAsc ? 1 : -1);
                                });
                            },

                            filteredInvoices() {
                                return this.filterStatus ?
                                    this.invoices.filter(inv => inv.status === this.filterStatus) :
                                    this.invoices;
                            },

                            paginatedInvoices() {
                                let start = (this.currentPage - 1) * this.itemsPerPage;
                                let end = start + this.itemsPerPage;
                                return this.sortedInvoices.slice(start, end);
                            },

                            totalPages() {
                                return Math.ceil(this.filteredInvoices().length / this.itemsPerPage);
                            },

                            prevPage() {
                                if (this.currentPage > 1) this.currentPage--;
                            },

                            nextPage() {
                                if (this.currentPage < this.totalPages()) this.currentPage++;
                            },

                            deleteInvoice(id) {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "This will permanently delete the invoice.",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, delete it!',
                                    customClass: {
                                        confirmButton: 'my-confirm-btn',
                                        cancelButton: 'my-cancel-btn'
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                            body: new URLSearchParams({
                                                action: 'delete_invoice_post',
                                                post_id: id
                                            })
                                        })
                                        .then(res => res.json())
                                        .then(result => {
                                            if (result.success) {
                                                this.invoices = this.invoices.filter(invoice => invoice.id !== id);
                                                if (this.currentPage > this.totalPages()) this.currentPage = this.totalPages();
                                                Swal.fire({ title: 'Deleted!', text: 'Invoice deleted.', icon: 'success', timer: 1500, showConfirmButton: false });
                                            } else {
                                                Swal.fire('Error', result.data || 'Failed to delete invoice.', 'error');
                                            }
                                        })
                                        .catch(err => {
                                            console.error('Delete failed:', err);
                                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                                        });
                                    }
                                });
                            },

                            downloadInvoice(invoice) {
                                let header = "Invoice Number, Channel, Date, Amount, Platform, Status";
                                let content = `${invoice.invoiceNumber}, ${invoice.channel}, ${invoice.date}, ${invoice.amount}, ${invoice.platform}, ${invoice.status}`;
                                let fullName = (invoice.fullName || 'Unknown').replace(/[^a-z0-9]/gi, '_').toUpperCase();
                                let csvContent = `${header}\n${content}`;
                                let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8" });
                                saveAs(blob, `${invoice.invoiceNumber}_${fullName}.csv`);
                            },

                            downloadReport() {
                                let header = "Invoice Number, Channel, Date, Amount, Platform, Status";
                                let data = this.filteredInvoices().map(inv =>
                                    `${inv.invoiceNumber}, ${inv.channel}, ${inv.date}, ${inv.amount}, ${inv.platform}, ${inv.status}`
                                ).join("\n");
                                let csvContent = `${header}\n${data}`;
                                let fullName = this.filteredInvoices()[0]?.fullName || 'Unknown';
                                fullName = fullName.replace(/[^a-z0-9]/gi, '_').toUpperCase();
                                let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8" });
                                saveAs(blob, `INVOICE_${fullName}.csv`);
                            }
                        };
                    }
                    
                    document.querySelectorAll(".dropdown-item").forEach(item => {
                        item.addEventListener("click", function() {
                            document.getElementById("dropdownButton").innerText = this.getAttribute("data-value");
                        });
                    });
                </script>
                <!-- total earnings section -->
                <div class="earning-chart" x-data="channelsEarn()">
                    <div class="head-option">
                        <h2>Total Earnings</h2>
                        <div class="table-filter">
                            <img src="/wp-content/uploads/2025/03/calendar.png" alt="">
                            <select x-model="filterMonth" @change="renderChart">
                                <option value="">Month</option>
                                <template x-for="month in availableMonths" :key="month">
                                    <option :value="month" x-text="new Date(2000, month-1).toLocaleString('default', {month: 'long'})"></option>
                                </template>
                            </select>
                            <select x-model="filterYear" @change="renderChart">
                                <option value="">Year</option>
                                <template x-for="year in availableYears" :key="year">
                                    <option :value="year" x-text="year"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="earning">
                        <h2>$<span x-text="totalAmount.toLocaleString()"></span></h2>
                        <canvas id="earningsChartProfile"></canvas>
                    </div>
                </div>
                <script>
                    function channelsEarn() {
                        return {
                            open: false,
                            chart: null,
                            filterMonth: '',
                            filterYear: '',
                            filteredInvoices: [],
                            totalAmount: 0,
                            invoices: [],
                            channels: [],

                            init() {
                                try {
                                    const invoicesEl = document.getElementById("invoices-data");
                                    const chartEl = document.getElementById("chart-data");

                                    if (invoicesEl) {
                                        const parsed = JSON.parse(invoicesEl.textContent);
                                        this.invoices = parsed?.invoices || [];
                                    }

                                    if (chartEl) {
                                        const parsedChart = JSON.parse(chartEl.textContent);
                                        this.chartData = parsedChart?.chart || [];
                                    }
                                } catch (e) {
                                    console.error("Failed loading profile data:", e);
                                    this.invoices = [];
                                    this.chartData = [];
                                }

                                this.filteredInvoices = [...this.invoices];
                                this.$nextTick(() => this.renderChart());
                                // console.log('Invoices:', this.filteredInvoices);
                                // console.log('Chart data:', this.chartData);
                            },

                            get availableMonths() {
                                return Array.from({ length: 12 }, (_, i) =>
                                    String(i + 1).padStart(2, '0')
                                );
                            },

                            get availableYears() {
                                const years = this.invoices.map(i => {
                                    const [day, month, year] = i.date.split('/');
                                    return new Date(`${month}/${day}/${year}`).getFullYear();
                                });
                                return [...new Set(years)].sort((a, b) => a - b);
                            },

                            channelTotals() {
                                const totals = {};
                                this.filteredInvoices.forEach(invoice => {
                                    const channel = invoice.channel;
                                    const amount = parseFloat(invoice.amount) || 0;
                                    if (!totals[channel]) totals[channel] = 0;
                                    totals[channel] += amount;
                                });
                                return totals;
                            },

                            overallTotal() {
                                return this.filteredInvoices.reduce((total, invoice) => {
                                    return total + (parseFloat(invoice.amount) || 0);
                                }, 0);
                            },

                            getMonthYearFilteredData() {
                                const month = parseInt(this.filterMonth);
                                const year = parseInt(this.filterYear);

                                this.filteredInvoices = this.invoices.filter(invoice => {
                                    const [day, m, y] = invoice.date.split('/');
                                    return (!month || parseInt(m) === month) &&
                                        (!year || parseInt(y) === year);
                                });
                                this.updateChart();
                            },

                            get filteredexpensesData() {
                                if (!this.filterMonth || !this.filterYear) return this.invoices;

                                const month = parseInt(this.filterMonth);
                                const year = parseInt(this.filterYear);

                                return this.invoices.filter(invoice => {
                                    const [day, m, y] = invoice.date.split('/');
                                    return parseInt(m) === month && parseInt(y) === year;
                                });
                            },

                            get totalAmountPerChannel() {
                                const totals = {};
                                this.filteredexpensesData.forEach(invoice => {
                                    const channel = invoice.channel;
                                    const amount = parseFloat(invoice.amount) || 0;
                                    if (!totals[channel]) totals[channel] = 0;
                                    totals[channel] += amount;
                                });
                                this.totalAmount = Object.values(totals).reduce((s, v) => s + v, 0);
                                return totals;
                            },

                            updateChart() {
                                if (!this.chart) return;
                                const totals = this.channelTotals();
                                this.chart.data.labels = Object.keys(totals);
                                this.chart.data.datasets[0].data = Object.values(totals);
                                this.chart.update();
                            },

                            renderChart() {
                                const totals = this.totalAmountPerChannel;
                                const labels = Object.keys(totals);
                                const dataValues = Object.values(totals);
                                const ctx = document.getElementById('earningsChartProfile').getContext('2d');

                                if (Chart.getChart("earningsChartProfile")) {
                                    Chart.getChart("earningsChartProfile").destroy();
                                }

                                if (!dataValues.length || !labels.length) {
                                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                                    ctx.font = '16px Arial';
                                    ctx.textAlign = 'center';
                                    ctx.fillText('No data found', ctx.canvas.width / 2, ctx.canvas.height / 2);
                                    return;
                                }

                                this.chart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels,
                                        datasets: [{
                                            data: dataValues,
                                            backgroundColor: [
                                                'rgb(33, 148, 235)',
                                                'rgb(163, 210, 235)',
                                                'rgb(142, 157, 252)',
                                                'rgb(131, 120, 250)',
                                                'rgb(38, 130, 142)',
                                            ],
                                            hoverOffset: 4
                                        }]
                                    },
                                    options: {
                                        plugins: {
                                            legend: { display: true, position: 'bottom' }
                                        }
                                    }
                                });
                            }
                        };
                    }
                </script>

            </div>
        </main>
    </div>
</div>

<style>
.breadcrumb {
    font-weight: 300;
    font-size: 14px;
    column-gap: 24px;
    margin-bottom: 40px;
}

.breadcrumb-item a {
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

.breadcrumb-item.active {
    color: #101010;
    background-color: transparent;
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
}
.active-roster .card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    padding: 20px;
    transition: 0.3s;
    min-height: 300px;
    background: transparent;
}
.owl-controls .owl-buttons {
    text-align: center;
    margin-top: 20px;
}

.owl-controls .owl-buttons .owl-prev {
    background: #fff;
    position: absolute;
    top: 40%;
    left: -40px;
    padding: 10px 18px;
    border-radius: 50px;
    box-shadow: 3px 14px 25px -10px #92b4d0;
    transition: background 0.5s ease 0s;
}

.owl-controls .owl-buttons .owl-next {
    background: #fff;
    position: absolute;
    top: 40%;
    right: -40px;
    padding: 10px 18px;
    border-radius: 50px;
    box-shadow: -3px 14px 25px -10px #92b4d0;
    transition: background 0.5s ease 0s;
}

/* .owl-controls .owl-buttons .owl-prev:after,
.owl-controls .owl-buttons .owl-next:after {
    font-family: FontAwesome;
    color: #333;
    font-size: 30px;
} */
.owl-controls .owl-buttons .owl-prev:after {
    content: url('/wp-content/uploads/2025/07/prev.svg');
}
.owl-controls .owl-buttons .owl-next:after {
    /* content: "\f105"; */
    content: url('/wp-content/uploads/2025/07/nxt.svg');
}

/* #news-slider {
margin-top: 20px;
} */
.post-slide {
    /* background: #fff; */
    margin: 20px 15px 20px;
    border-radius: 15px;
    padding-top: 1px;
    /* box-shadow: 0px 14px 22px -9px #bbcbd8; */
}

.channel-option button {
    border: solid 1px #D0D0D0;
    border-radius: 4px !important;
    padding: 10px;
    font-size: 14px;
    font-weight: 300;
}

.channel-option a {
    color: #101010 !important;
}

.card-desc img.channel-photo {
    border-radius: 50%;
    width: 80px;
    height: 80px;
}

.channel-option .add-rmv-btn:focus {
    background-color: transparent;
}
.channel-option button:hover {
    background-color: #2194FF80;
}
.add-rmv-btn {
    color: #101010 !important;
}

.add-rmv-btn img.add-rmv-icon {
    margin-right: 10px;
}

.channel-option {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}

.card-desc {
    padding: 25px;
    display: flex;
    flex-direction: row;
    gap: 10px;
}

.card-desc h3 {
    font-size: 20px;
    font-weight: 600;
    color: #101010;
    margin-bottom: 10px;
}

.card-desc p {
    font-size: 14px;
    font-weight: 300;
    color: #101010;
    margin: 0;
}
.history-section {
    display: flex;
    flex-direction: row;
    gap: 20px;
}

.history-section .earning-chart {
    padding: 40px;
    background-color: #fff;
    border-radius: 10px;
}

.dl-btn {
    border: solid 1px #D0D0D0;
    border-radius: 4px;
    color: #101010;
    padding: 13px 10px;
    margin: 0;
    font-size: 14px;
    font-weight: 300;
}

.dl-btn i {
    margin-right: 10px;
}

.dl-btn:hover {
    background-color: transparent;
    color: #101010;
    padding: 13px 10px;
}

button:focus:not(:focus-visible) {
    color: #101010;
}

.hidden {
    display: none;
}

.btn-green {
    background-color: green;
    color: white;
}

.btn-red {
    background-color: red;
    color: white;
}

.btn-blue {
    background-color: blue;
    color: white;
}

.active {
    background-color: #007bff;
    color: white;
}

.dl-btn:focus {
    background-color: transparent;
}

.tbl-invoice-history td {
    color: #717171;
}

.tbl-invoice-history td span i {
    color: #2A2A2A;
}

table tbody tr:hover>td,
table tbody tr:hover>th {
    background-color: transparent;
}

img.dl-icon {
    margin-right: 10px;
}

.pagination .btn.disabled,
.btn:disabled,
fieldset:disabled .btn {
    border-color: transparent;
}

.pagination .btn-check:checked+.btn,
.btn.active,
.btn.show,
.btn:first-child:active,
:not(.btn-check)+.btn:active {
    background-color: #FFEE94;
    border-radius: 4px;
    border-color: transparent;
}

.pagination {
    margin: 40px 0 0 0;
    width: 100%;
    background: white;
    padding: 10px 0;
    justify-content: end;
}

.pagination .btn img {
    height: 12px;
}

.pagination .btn .prev-icon {
    margin-right: 5px;
}

.pagination .btn .next-icon {
    margin-left: 5px;
}

.invoice-section {
    width: 70%;
    max-width: 100%;
    position: relative;
    overflow: hidden;
}

/* 🔹 Scrollable Table */
.invoice-table-wrapper {
    overflow-x: auto;
}

.tbl-invoice-history thead {
    position: sticky;
    top: 0;
    background: white;
    z-index: 99;
}

.invoice-table-wrapper::-webkit-scrollbar {
    height: 5px;
}

.invoice-table-wrapper::-webkit-scrollbar-thumb {
    background: #336;
    border-radius: 4px;
}

.invoice-table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.invoice-section .btn-dl-invoice {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: end;
    /* position: sticky; */
    top: 0;
    z-index: 100;
    background: white;
    padding: 10px;
}
.rotate-180 {
    transform: rotate(180deg);
}
.my-confirm-btn, .okay-confirm-btn {
    background-color: #2194FF !important;
    color: #fff !important;
    border: solid 1px #2194FF;
    border-radius: 4px;
    padding: 10px !important;
    font-size: 14px;
}

.my-confirm-btn:hover, .okay-confirm-btn:hover {
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
.earning-chart {
    width: 30%;
}

.head-option {
    display: flex;
    flex-direction: row;
    margin-bottom: 24px;
    justify-content: space-between;
    margin-bottom: 40px;
}

.head-option input[type=text] {
    width: 50%;
    border: solid 1px #D0D0D0;
    border-radius: 4px;
    color: #101010 !important;
    font-size: 14px;
    font-weight: 300;
}

.head-option h2 {
    font-size: 16px;
}

.head-option select.drop-option {
    border: solid 1px #D0D0D0;
    border-radius: 4px;
    padding: 15px 10px;
    width: 30%;
}

select.drop-option-channel {
    border: solid 1px #D0D0D0;
    border-radius: 4px;
    padding: 15px 10px;
}

ul {
    list-style-type: none;
    background-color: #fff;
    padding: 20px;
}

li button {
    border: unset;
    color: #101010;
    font-weight: 300;
    font-size: 14px;
}

li button.btn {
    padding: 16px 20px;
}

li button.btn.active,
button:hover,
button:focus {
    background-color: transparent;
    color: #101010;
}

.date-iconic {
    padding: 10px;
    border: solid 1px #D0D0D0;
    border-radius: 4px;
    width: 50px;
}

img.dateIcon {
    height: 18px;
    width: 18px;
}

.drop-option3 {
    font-family: Sora;
    font-size: 14px;
    font-weight: 300;
    color: #717171;
    text-transform: capitalize;
    position: absolute;
    z-index: 50;
    /* top: 275px; */
    width: 370px;
}

.active {
    @apply bg-blue-100 text-blue-600;
}

.drop-option-channel {
    width: 100%;
    padding: 15px;
    margin: 0;
    text-align: left;
    border: solid 1px #D0D0D0;
    border-radius: 4px;
    color: #717171;
    font-size: 14px;
    font-weight: 300;
}

.ui-datepicker-calendar {
    display: none;
}

.ui-datepicker .ui-datepicker-buttonpane {
    display: none;
}

.ui-datepicker {
    width: 200px;
    padding: 10px;
}

.ui-datepicker .ui-datepicker-title {
    display: flex;
    flex-direction: row;
    margin: 30px 0 0 0;
    justify-content: space-between;
}

.ui-widget.ui-widget-content {
    border: unset;
}

.ui-widget-header {
    border: unset;
    background: transparent;
}

.ui-datepicker .ui-datepicker-header {
    padding: 0;
}

.ui-datepicker select.ui-datepicker-month,
.ui-datepicker select.ui-datepicker-year {
    width: 50%;
    border: unset;
}

.earning h2 {
    text-align: center;
    font-weight: 600;
}

.table-filter {
    display: flex;
    flex-direction: row;
    align-items: center;
    border: solid 1px #D0D0D0;
    border-radius: 4px;
    padding: 10px;
    gap: 10px;
    width: 60%;
}

.table-filter select {
    border: unset;
    padding: 0;
}

.table-filter img {
    width: 12px;
    height: 13px;
}
.text-danger,
.fallback {
    color: red;
    font-weight: 500;
    font-style: italic;
    text-transform: uppercase;
}
/* <!---------------------------- media queries ---------------------> */
    @media only screen and (max-width: 1440px) {
        .history-section .earning-chart {
            padding: 24px;
        }
        .ui-datepicker {
            left: 1195px !important;
        }
        .drop-option3 {
            width: 270px;
        }
        .invoice-table-wrapper table tbody td, 
        .invoice-table-wrapper table thead th {
            padding: 10px;
        }
    }

    @media only screen and (max-width: 1024px) {
        .earning-chart .head-option {
            display: flex;
            flex-direction: column;
        }

        .head-option select.drop-option {
            width: 100%;
        }

        .profile-container .row-gap-4 {
            display: flex;
            flex-direction: column;
        }

        .profile-container .col-lg-6 {
            width: 100%;
        }
        .head-option input[type=text] {
            width: 100%;
        }

        .ui-datepicker {
            padding: 5px;
            width: 190px;
            left: 785px !important;
        }

        .ui-datepicker .ui-datepicker-title {
            display: flex;
            flex-direction: column;
            margin: 30px 0 0 0;
        }

        .ui-datepicker .ui-datepicker-title select {
            width: 100%;
        }

        .drop-option3 {
            width: 150px;
        }

        ul.drop-option3 {
            padding: 10px;
        }
        table .tblActions {
            display: flex;
        }
    }

    @media only screen and (max-width: 960px) {
        .invoice-section .btn-dl-invoice {
            display: flex;
            flex-direction: column;
            align-items: unset;
        }

        .invoice-section .dl-btn {
            width: 180px;
        }
    }

    @media only screen and (max-width: 768px) {
        .history-section {
            display: flex;
            flex-direction: column;
        }

        .invoice-section {
            width: 100%
        }

        .invoice-section .btn-dl-invoice {
            display: flex;
            flex-direction: row;
        }

        .earning-chart {
            width: 100%;
        }

        .earning-chart .head-option select.drop-option {
            width: 50%;
        }

        .profile-details {
            padding: 40px 10px;
        }
        .profile-edit-button {
            justify-content: center;
        }

        .profile-details p {
            display: flex;
            flex-direction: column;
        }

        .ui-datepicker {
            width: 350px;
            left: 350px !important;
        }

        .ui-datepicker .ui-datepicker-title select {
            width: 50%;
        }

        .ui-datepicker .ui-datepicker-title {
            display: flex;
            flex-direction: row;
        }

        .drop-option3 {
            width: 350px;
        }
    }

    @media only screen and (max-width: 767px) {
        .parent-main {
            display: flex;
            flex-direction: row;
        }
        .p-4 {
            padding: 0px !important;
        }
        .earnings-container {
            padding: 20px 10px !important;
        }
        .menuLogoImgText {
            width: 180px;
        }
        .main-content {
            width: 520px;
        }
        .history-section {
            display: flex !important;
            flex-direction: column !important;
        }
        .history-section .invoice-section,
        .history-section .earning-chart {
            width: 100% !important;
        }
        .profile-details .card-body.card-details {
            padding: 20px;
        }
        .profile-details {
            padding: 20px 10px;
        }
        div.owl-item {
            width: 300px !important;
        }
    }

    @media only screen and (max-width: 425px) {
        .invoice-section .btn-dl-invoice {
            display: flex;
            flex-direction: column;
            align-items: unset;
        }

        .invoice-section .dl-btn {
            width: 180px;
        }

        .profile-details {
            padding: 20px 10px;
        }

        .profile-edit h4.mt-2 {
            font-size: 20px;
        }

        .profile-details .card-body.card-details {
            padding: 10px;
            overflow: hidden;
        }

        .channel-option {
            flex-direction: column;
        }

        .channel-option button {
            width: 190px;
        }

        .earnings-container {
            padding: 20px 10px !important;
        }

        .history-section .invoice-section,
        .history-section .earning-chart {
            padding: 20px 10px !important;
        }

        .earning-chart .head-option {
            flex-direction: column;
        }

        .earning-chart .head-option select.drop-option {
            width: 100%;
        }

        .pagination .btn {
            font-size: 14px;
        }

        .post-slide {
            margin: 10px;
        }

        .post-slide h3 {
            font-size: 18px;
        }

        .post-slide .row p {
            font-size: 14px;
        }

        .drop-option3 {
            width: 220px;
        }
    }
</style>

<?php get_footer('admin'); ?>