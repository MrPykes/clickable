<?php

/**
 * Template Name:  Channels Template
 */

get_header('admin');

// Insight Subscribers & Videos per Channel
$insight_data_map = [];
$insight_query = new WP_Query([
    'post_type'      => 'channel-insight',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);
if ($insight_query->have_posts()) {
    while ($insight_query->have_posts()) {
        $insight_query->the_post();
        $channel_name        = get_field('channel_name');
        $channel_subscribers = (int) get_field('channel_subscribers');
        $channel_videos      = (int) get_field('channel_videos');

        if (!isset($insight_data_map[$channel_name])) {
            $insight_data_map[$channel_name] = [
                'subscribers' => 0,
                'videos'      => 0,
            ];
        }

        // Sum up all posts belonging to same channel
        $insight_data_map[$channel_name]['subscribers'] += $channel_subscribers;
        $insight_data_map[$channel_name]['videos']      += $channel_videos;
    }
}
wp_reset_postdata();

// Insight Subscribers per Channel
// $insight_data_map = [];
// $insight_query = new WP_Query([
//     'post_type'      => 'channel-insight',
//     'posts_per_page' => -1,
//     'post_status'    => 'publish',
// ]);
// if ($insight_query->have_posts()) {
//     while ($insight_query->have_posts()) {
//         $insight_query->the_post();
//         $channel_name = get_field('channel_name');
//         $channel_subscribers = get_field('channel_subscribers');
//         if (!isset($insight_data_map[$channel_name])) {
//             $insight_data_map[$channel_name] = $channel_subscribers ?? 0;
//         }
//     }
// }
// wp_reset_postdata();

// All Channels
$channels_data = [];
$channels_query = new WP_Query([
    'post_type'      => 'channel',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);
if ($channels_query->have_posts()) {
    while ($channels_query->have_posts()) {
        $channels_query->the_post();
        $image = get_field('channel_profile_photo');
        $image_url = $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/03/Glow-Icon-_-flower-300x300.jpg';
        $channel_name = get_field('channel_name');

        // $channels_data[] = [
        //     'id'        => get_the_ID(),
        //     'name'      => $channel_name,
        //     'desc'      => get_field('channel_description'),
        //     // 'videos'    => get_field('channel_videos') ?? '0',
        //     'subscribe' => get_field('channel_subscriber') ?? '0',
        //     'status'    => get_field('channel_status'),
        //     'image'     => $image_url,
        //     'link'      => get_permalink(),
        //     'channelSubscribers' => $insight_data_map[$channel_name] ?? '0', // Injected from insight
        // ];
        $channels_data[] = [
            'id'        => get_the_ID(),
            'name'      => $channel_name,
            'desc'      => get_field('channel_description'),
            'videos'    => $insight_data_map[$channel_name]['videos'] ?? 0,
            'subscribe' => $insight_data_map[$channel_name]['subscribers'] ?? 0,
            'status'    => get_field('channel_status'),
            'image'     => $image_url,
            'link'      => get_permalink(),
        ];

    }
}
wp_reset_postdata();

// Sort channels alphabetically
usort($channels_data, function($a, $b) {
    return strcmp(strtolower($a['name']), strtolower($b['name']));
});

?>
<script type="application/json" id="channels-data">
    <?php echo wp_json_encode($channels_data); ?>
</script>


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar');  ?>
    <div class="main-content">
        <div class="channel-section" x-data="channelsData()">
            <div class="channel-heading">
                <h1>Channels</h1>
                <!-- <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p> -->
            </div>
            <div class="channel-nav">
                <div class="channel-filter-search">
                    <div x-data="{ open: false }" class="drop-filter">
                        <button @click="open = !open" class="iconic">
                            <img src="/wp-content/uploads/2025/02/sidebar-size.svg" alt="Filter Icon" class="h-6 w-6">
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="drop-content">
                            <div class="drop-div" x-data="{ statusDropdown: false }">
                                <label class="drop-title">Status:</label>
                                <button class="drop-option2 w-full text-left flex justify-between items-center" @click="statusDropdown = !statusDropdown">
                                    <span x-text="selectedStatus || 'All'"></span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul class="drop-option3 mt-1" x-show="statusDropdown" x-transition @click.away="statusDropdown = false">
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedStatus === 'All' ? 'active' : ''"
                                            @click="selectedStatus = 'All'; applyFilters(); statusDropdown = false">
                                            All
                                        </button>
                                    </li>
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedStatus === 'Active' ? 'active' : ''"
                                            @click="selectedStatus = 'Active'; applyFilters(); statusDropdown = false">
                                            Active
                                        </button>
                                    </li>
                                    <li>
                                        <button class="btn w-full text-left"
                                            :class="selectedStatus === 'Archived' ? 'active' : ''"
                                            @click="selectedStatus = 'Archived'; applyFilters(); statusDropdown = false">
                                            Archived
                                        </button>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="channel-filter-search">
                        <div class="search-container">
                            <img class="search-icon" src="/wp-content/uploads/2025/02/Vector-4.svg" alt="Search Icon">
                            <input type="text" class="searchbtn" placeholder="Search channel here..." @input="filterChannelsBySearch($event.target.value)">
                        </div>
                    </div>
                </div>
                <div class="btn-channel">
                    <button class="btn-add">
                        <a href="/add-channel/">
                            <img src="/wp-content/uploads/2025/02/Vector-6.svg"> Add Channel
                        </a>
                    </button>
                </div>
            </div>
            <!-- Channel Grid -->
            <div class="channel-grid">
                <template x-for="channel in displayedChannels" :key="channel.id">
                    <a :href="channel.link" class="link-channel-card">
                        <div class="channel-card">
                            <div>
                                <div>
                                    <img class="channel-photo" :src="channel.image" alt="Profile Photo">
                                </div>
                                <div class="channel-infos">
                                    <p x-text="channel.subscribe + ' subscribers'"></p>
                                    <i class="fa fa-square"></i>
                                    <p x-text="channel.videos + ' videos'"></p>
                                </div>
                                <div class="channel-more-info">
                                    <h3 x-text="channel.name"></h3>
                                    <p x-text="channel.desc"></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>

            <!-- <div class="channel-grid">
                <template x-for="channel in displayedChannels" :key="channel.id">
                    <a :href="channel.link" class="link-channel-card">
                        <div class="channel-card">
                            <div>
                                <div>
                                    <img class="channel-photo" :src="channel.image" alt="Profile Photo">
                                </div>
                                <div class="channel-infos">
                                    <p x-text="channel.channelSubscribers + ' subscribers'"></p>
                                    <i class="fa fa-square"></i>
                                    <p x-text="channel.videos + ' videos'"></p>
                                </div>
                                <div class="channel-more-info">
                                    <h3 x-text="channel.name"></h3>
                                    <p x-text="channel.desc"></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div> -->

            <!-- <php
                $args = array(
                    'post_type'      => 'channel-insight',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                );
                $channels_query = new WP_Query($args);
                if ($channels_query->have_posts()) {
                    while ($channels_query->have_posts()) {
                        $channels_query->the_post();
                        // $image = get_field('channel_profile_photo');
                        $channels_data[] = [
                            'id'    => get_the_ID(),
                            'name'  => get_field('channel_name'),
                            // 'desc'       => get_field('channel_description'),
                            // 'videos'     => get_field('channel_videos'),
                            'subscribe'  => get_field('channel_subscribers'),
                            // 'status'     => get_field('channel_status'),
                            // 'image' => $image['sizes']['medium'],
                        ];
                    }
                    wp_reset_postdata();
                }
                echo "<pre>";
                print_r($channels_data);
                echo "</pre>"; 
            ?> -->


            <div class="pagination-controls">
                <button class="prev-btn" @click="prevPage()" :disabled="currentPage === 1"><img class="prev-icon" src="/wp-content/uploads/2025/02/Vector-5.svg" alt="Previous"> Previous</button>
                <template x-for="page in totalPages()" :key="page">
                    <button class="btn" :class="currentPage === page ? 'active' : ''" @click="goToPage(page)" x-text="page"></button>
                </template>
                <button class="nxt-btn" @click="nextPage()" :disabled="currentPage === totalPages()">Next <img class="next-icon" src="/wp-content/uploads/2025/03/next.png" alt="Next"></button>
            </div>
        </div>
    </div>
</div>
<!-- <php
    $args = array(
        'post_type'      => 'channel-insight',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );
    $channels_query = new WP_Query($args);
    if ($channels_query->have_posts()) {
        while ($channels_query->have_posts()) {
            $channels_query->the_post();
            // $image = get_field('channel_profile_photo');
            $channels_data[] = [
                'id'    => get_the_ID(),
                // 'name'  => get_field('channel_name'),
                // 'desc'       => get_field('channel_description'),
                // 'videos'     => get_field('channel_videos'),
                'subscribe'  => get_field('channel_subscribers'),
                // 'status'     => get_field('channel_status'),
                // 'image' => $image['sizes']['medium'],
            ];
        }
        wp_reset_postdata();
    }
    echo "<pre>";
    print_r($channels_data);
    echo "</pre>"; 
?> -->
<script>
    document.querySelectorAll(".dropdown-item").forEach(item => {
        item.addEventListener("click", function() {
            document.getElementById("dropdownButton").innerText = this.getAttribute("data-value");
        });
    });

    function channelsData() {
        return {
            channels: [],
            allChannels: [],
            displayedChannels: [],
            selectedStatus: 'All',
            searchQuery: '',
            itemsPerPage: 6,
            currentPage: 1,

            init() {
                try {
                    this.allChannels = JSON.parse(document.getElementById('channels-data').textContent);
                } catch (e) {
                    console.error("Failed to parse channels-data JSON:", e);
                    this.allChannels = [];
                }

                this.channels = this.allChannels;
                this.applyFilters();
            },

            // Filters by status
            filterChannelsByStatus(event) {
                this.selectedStatus = event.target.value;
                this.applyFilters();
            },

            // Filters by search query
            filterChannelsBySearch(query) {
                this.searchQuery = query.toLowerCase();
                this.applyFilters();
            },

            // Apply filters
            applyFilters() {
                let filtered = this.allChannels.filter(channel => {
                    const matchesStatus = this.selectedStatus === 'All' || channel.status === this.selectedStatus;
                    const matchesSearch = this.searchQuery === '' || channel.name.toLowerCase().includes(this.searchQuery);
                    return matchesStatus && matchesSearch;
                });
                this.channels = filtered;
                this.currentPage = 1;
                this.updateDisplayedChannels();
            },

            // Pagination
            updateDisplayedChannels() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                const end = start + this.itemsPerPage;
                this.displayedChannels = this.channels.slice(start, end);
            },
            totalPages() {
                return Math.max(1, Math.ceil(this.channels.length / this.itemsPerPage));
            },
            nextPage() {
                if (this.currentPage < this.totalPages()) {
                    this.currentPage++;
                    this.updateDisplayedChannels();
                }
            },
            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.updateDisplayedChannels();
                }
            },
            goToPage(page) {
                if (page >= 1 && page <= this.totalPages()) {
                    this.currentPage = page;
                    this.updateDisplayedChannels();
                }
            }
        };
    }
    
</script>


<style>
    .channel-section {
        padding: 20px 50px;
    }

    .channel-heading {
        margin-bottom: 20px;
    }

    .channel-section .channel-heading h1 {
        font-weight: 600;
        font-size: 30px;
        letter-spacing: -0.03em;
        color: #101010;
        margin-bottom: 24px;
    }

    .channel-section .channel-heading p {
        font-weight: 300;
        font-size: 16px;
        color: #101010;
        width: 500px;
    }

    /* filter */
    button.iconic {
        padding: 4.5px;
        border: none !important;
        background-color: #fff !important;
        border-radius: 10px;
        margin: 0;
    }

    button.iconic:hover {
        padding: 4.5px;
        border: none !important;
        background-color: #fff !important;
    }

    .drop-content {
        display: flex;
        align-items: center;
        column-gap: 10px;
    }

    div.drop-div {
        display: flex;
        align-items: center;
        background-color: #fff;
        border-radius: 10px;
        padding: 13px 20px 13px 20px;
    }

    .drop-title {
        font-family: Sora;
        font-size: 14px;
        font-weight: 600;
        color: #101010;
        margin-right: 8px;
    }

    .drop-option {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
    }

    .drop-filter {
        display: flex;
        flex-direction: row;
        gap: 10px;
    }

    select.drop-option {
        border: none;
        background-color: #fff;
        border-radius: 10px;
    }

    .channel-nav {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .channel-nav .channel-filter-search {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        column-gap: 20px;
    }

    .channel-nav .channel-filter-search .search-container {
        width: 500px;
        height: 50px;
    }

    .channel-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, auto);
        gap: 20px;
        justify-content: center;
        align-items: start;
    }

    .channel-card {
        background-color: #fff;
        padding: 25px 25px 55px 25px;
        border-radius: 10px;
        height: 100%;
        border: solid 1px transparent;
    }

    img.channel-photo {
        border-radius: 50px;
        margin-bottom: 25px;
        max-width: 100px;
        max-height: 100px;
    }

    .channel-card .channel-infos {
        display: flex;
        margin-top: 20px;
        margin-bottom: 45px;
    }

    .channel-card .channel-infos {
        font-size: 14px;
        font-weight: 300;
        color: #101010;
        gap: 10px;
    }

    .channel-card .channel-infos i {
        margin-top: 10px;
        font-size: 5px;
    }

    .channel-card .channel-more-info h3 {
        font-size: 24px;
        font-weight: 600;
        letter-spacing: -0.01em;
        color: #101010;
        margin-bottom: 25px;
    }

    .channel-card .channel-more-info p {
        font-size: 14px;
        font-weight: 300;
        line-height: 150%;
        color: #717171;
    }

    .channel-card:hover {
        border: solid 1px #2194FF;
    }

    .link-channel-card {
        height: 100%
    }

    button.btn-add {
        background-color: #2194FF;
        border-radius: 10px;
    }

    button.btn-add a {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
    }

    button.btn-add a:hover {
        color: #fff;
    }

    button.btn-add:hover, button.btn-add:focus {
        background-color: #0165C2;
    }

    [type=button],
    [type=submit],
    button {
        border: none;
    }

    .pagination-controls button img {
        height: 12px;
    }

    .pagination-controls .prev-btn .prev-icon {
        margin-right: 5px;
    }

    .pagination-controls .nxt-btn .next-icon {
        margin-left: 5px;
    }

    .pagination-controls .btn.disabled,
    .btn:disabled,
    fieldset:disabled .btn {
        border-color: transparent;
    }

    .pagination-controls .btn-check:checked+.btn,
    .btn.active,
    .btn.show,
    .btn:first-child:active,
    :not(.btn-check)+.btn:active {
        background-color: #FFEE94;
        border-radius: 4px;
        border-color: transparent;
    }

    ul.drop-option3.mt-1 {
        padding: 20px;
    }

    ul {
        list-style-type: none;
        background-color: #fff;
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

    .drop-div .drop-option2 {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
        width: 120px;
        padding: 0;
        @apply bg-white border border-gray-200 rounded shadow-md p-2;
        overflow-y: auto;
        display: flex;
        justify-content: space-between;
    }

    button.drop-option2 {
        border: unset;
    }

    .drop-option2 svg.w-4.h-4.ml-2 {
        width: 20px;
    }

    .drop-option3 {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
        position: absolute;
        z-index: 50;
        top: 160px;
        left: 410px;
        width: 220px;
        border-radius: 10px;
    }
    .active {
        @apply bg-blue-100 text-blue-600;
    }

    /* <!-- Media Queries --> */

    @media (max-width: 1172px) {
        .channel-grid {
            grid-template-columns: repeat(2, 1fr);
            /* 2 columns on 1024px */
            grid-template-rows: repeat(3, auto);
            /* 3 rows */
        }

        .channel-nav {
            display: flex;
            flex-direction: column-reverse;
        }

        .btn-channel {
            display: flex;
            flex-direction: row;
            width: 100%;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .channel-nav .channel-filter-search .search-container {
            width: 500px;
        }
    }

    @media only screen and (max-width: 1024px) {
        .channel-section {
            padding: 20px;
        }

        .channel-nav .channel-filter-search .search-container {
            width: 100%;
        }

        .drop-option3 {
            top: 220px;
            left: 380px;
        }
    }

    @media (max-width: 881px) {
        .main-content {
            overflow-x: hidden;
        }

        .channel-grid {
            grid-template-columns: repeat(1, 1fr);
            /* 2 columns on 1024px */
            grid-template-rows: repeat(6, auto);
            /* 3 rows */
        }

        button.iconic {
            width: 50px;
        }

        .drop-filter {
            display: flex;
            flex-direction: column;
        }

        div.drop-div {
            width: 150px;
        }

        .drop-option3 {
            top: 280px;
            left: 320px;
            padding: 10px;
            width: 150px;
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
            padding-bottom: 0px;
            padding-right: 0px;
            padding-left: 0px;
            padding-top: 20px;
        }

        .channel-section .channel-heading p {
            width: 100%;
        }
    }

    @media only screen and (max-width: 425px) {
        .channel-nav .channel-filter-search {
            display: flex;
            flex-direction: column-reverse;
        }

        .channel-nav .channel-filter-search .search-container {
            margin-bottom: 24px;
        }

        .pagination-controls .prev-btn {
            margin-right: 0;
        }

        .pagination-controls .nxt-btn {
            margin-left: 0;
        }

        .drop-filter {
            display: flex;
            flex-direction: row;
        }

        .drop-option3 {
            top: 295px;
            left: 180px;
            padding: 5px;
            width: 150px;
        }

        .channel-card {
            padding: 20px;
        }
    }

    @media only screen and (max-width: 320px) {
        .channel-nav .channel-filter-search .search-container {
            width: 180px;
            padding: 10px;
        }

        .search-container input {
            font-size: 14px;
        }

        .drop-filter {
            display: flex;
            flex-direction: column;
        }

        .drop-option3 {
            top: 355px;
            left: 125px;
        }

    }
</style>


<?php get_footer('admin'); ?>