<?php

/**
 * Template Name: Team Template
 */
get_header('admin');

// TEAM MEMBERS
$members = [];
$team_query = new WP_Query([
    'post_type'      => 'team',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'contractor_status',
            'value'   => 'active',
            'compare' => '='
        ],
        [
            'key'     => 'team_status',
            'value'   => 'CONTRACTOR',
            'compare' => '='
        ]
    ]
]);
while ($team_query->have_posts()) : $team_query->the_post();
    $image        = get_field('profile_photo');
    $your_channel = get_field('your_channel');
    $role_field   = get_field('role');

    // Always store roles as array
    $role_titles = [];
    if ($role_field) {
        if (is_array($role_field)) {
            $role_titles = array_map(fn($post) => get_the_title($post), $role_field);
        } else {
            $role_titles[] = get_the_title($role_field);
        }
    }

    // Always store channels as array
    if (is_array($your_channel)) {
        $channels = array_map(fn($post) => get_the_title($post), $your_channel);
    } elseif (is_object($your_channel)) {
        $channels = [get_the_title($your_channel)];
    } else {
        $channels = [];
    }

    $members[] = [
        'id'         => get_the_ID(),
        'name'       => get_field('fullname'),
        'channel'    => $channels,
        'teamStatus' => get_field('contractor_status'),
        'role'       => $role_titles,
        'image'      => $image ? $image['sizes']['medium'] : '/wp-content/uploads/2025/02/Placeholder-Image-13.png',
        'link'       => get_permalink(),
    ];
endwhile;
wp_reset_postdata();

// CHANNELS
$channels = [];
$channel_query = new WP_Query([
    'post_type'      => 'channel',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'channel_status',
            'value'   => 'Active',
            'compare' => '='
        ]
    ]
]);
while ($channel_query->have_posts()) : $channel_query->the_post();
    $channel_name = get_field('channel_name');
    if ($channel_name) {
        $channels[] = $channel_name;
    }
endwhile;
wp_reset_postdata();
$channels = array_values(array_unique($channels));

// Sort alphabetically 
natcasesort($channels);
$channels = array_values($channels); 

// ROLES
$roles = [];
$role_query = new WP_Query([
    'post_type'      => 'team-roles',
    'posts_per_page' => -1,
    'post_status'    => 'publish'
]);
while ($role_query->have_posts()) : $role_query->the_post();
    $roles[] = get_the_title();
endwhile;
wp_reset_postdata();
$roles = array_unique($roles);
sort($roles);
$roles = array_values($roles);

?>
<script type="application/json" id="team-members">
    <?php echo json_encode($members); ?>
</script>

<script type="application/json" id="channels-data">
    <?php echo json_encode(array_values(array_unique($channels))); ?>
</script>

<script type="application/json" id="roles-data">
    <?php echo json_encode(array_values($roles)); ?>
</script>


<div class="container-fluid p-0 parent-main">
    <?php get_template_part('admin-sidebar'); ?>
    <div class="main-content">
        <!-- <php 
        echo "<pre>";
        print_r(get_fields(2276));
        echo "</pre>";
        ?> -->
        <div class="team-desc">
            <div class="descript-heading">
                <h1>Team Members</h1>
                <!-- <p>Lorem ipsum dolor sit amet, consect etur adipiscing elit.</p> -->
            </div>
            <!-- Filtering and Search Buttons-->
            <div x-data="teamFilter()">
                <div class="team-navoption">
                    <div class="filter-opt">
                        <button @click="open = !open" class="iconic">
                            <img src="/wp-content/uploads/2025/02/sidebar-size.svg" alt="Filter Icon" class="h-6 w-6">
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="drop-content">
                            <div class="drop-div custom-channel" x-data="{ channelDropdown: false }">
                                <label class="drop-title mb-1">Channel:</label>
                                <button class="drop-option2 w-full text-left flex justify-between items-center" @click="channelDropdown = !channelDropdown">
                                    <span x-text="selectedChannel || 'All'"></span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul class="drop-option3 mt-1" x-show="channelDropdown" x-transition @click.away="channelDropdown = false">
                                    <li>
                                        <button
                                            class="btn w-full text-left"
                                            :class="selectedChannel == '' ? 'active' : ''"
                                            @click="selectedChannel = ''; filterItems(); channelDropdown = false">
                                            All
                                        </button>
                                    </li>
                                    <template x-for="channel in channels" :key="channel">
                                        <li>
                                            <button
                                                class="btn w-full text-left"
                                                :class="selectedChannel == channel ? 'active' : ''"
                                                @click="selectedChannel = channel; filterItems(); channelDropdown = false"
                                                x-text="channel">
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="drop-div " x-data="{ roleDropdown: false }">
                                <label class="drop-title mb-1">Role:</label>
                                <button class="drop-option2 w-full text-left flex justify-between items-center" @click="roleDropdown = !roleDropdown">
                                    <span x-text="selectedRole || 'All'"></span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul class="drop-option3 mt-1 custom-roledrop" x-show="roleDropdown" x-transition @click.away="roleDropdown = false">
                                    <li>
                                        <button
                                            class="btn w-full text-left"
                                            :class="selectedRole == '' ? 'active' : ''"
                                            @click="selectedRole = ''; filterItems(); roleDropdown = false">
                                            All
                                        </button>
                                    </li>
                                    <template x-for="role in roles" :key="role">
                                        <li>
                                            <button
                                                class="btn w-full text-left"
                                                :class="selectedRole === role ? 'active' : ''"
                                                @click="selectedRole = role; filterItems(); roleDropdown = false"
                                                x-text="role">
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="search-container">
                        <img class="search-icon" src="/wp-content/uploads/2025/02/Vector-4.svg" alt="Search Icon">
                        <input type="text" class="searchbtn" placeholder="Search name here..." x-model="searchQuery" @input="filterItems">
                    </div>
                </div>
                <script>
                    document.querySelectorAll(".dropdown-item").forEach(item => {
                        item.addEventListener("click", function() {
                            document.getElementById("dropdownButton").innerText = this.getAttribute("data-value");
                        });
                    });
                </script>
                <!-- Teams in a rows -->
                <div class="team-members row row-gap-4">
                    <template x-for="member in paginatedMembers" :key="member.id">
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a :href="member.link">
                                <div class="team-member card">
                                    <div class="card-body text-center">
                                        <div 
                                            class="team-prof" 
                                            :style="`background-image: url('${member.image}'); background-size: cover; 
                                            background-position: center; background-repeat: no-repeat;`">
                                        </div>
                                        <div class="team-details">
                                            <h3 x-text="member.name" class="card-name"></h3>
                                            <p x-text="member.role ?? 'No role indicated.'"
                                                :class="!member.role ? 'text-danger' : ''"" class="card-role"></p>
                                            <p x-text="member.channel" class="hidden"></p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </template>
                </div>
                <div x-show="filteredMembers.length === 0" class="no-results text-center py-10 text-muted">
                    No team members found matching your search.
                </div>
                <div class="pagination-controls">
                    <button class="prev-btn" @click="prevPage()" :disabled="currentPage === 1"><img class="prev-icon" src="/wp-content/uploads/2025/02/Vector-5.svg" alt="Previous"> Previous</button>
                    <template x-for="page in totalPages()" :key="page">
                        <button class="btn" :class="currentPage === page ? 'active' : ''" @click="currentPage = page" x-text="page"></button>
                    </template>
                    <button class="nxt-btn" @click="nextPage()" :disabled="currentPage === totalPages()">Next <img class="next-icon" src="/wp-content/uploads/2025/03/next.png" alt="Next"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function teamFilter() {
    return {
        open: false,
        selectedChannel: '',
        selectedRole: '',
        searchQuery: '',
        currentPage: 1,
        itemsPerPage: window.innerWidth <= 1199 ? 6 : 8,

        teamMembers: [],
        channels: [],
        roles: [],

        init() {
            try {
                this.teamMembers = JSON.parse(document.getElementById('team-members').textContent);
                this.channels    = JSON.parse(document.getElementById('channels-data').textContent);
                this.roles       = JSON.parse(document.getElementById('roles-data').textContent);
            } catch (e) {
                console.error("Failed to parse team filter JSON:", e);
                this.teamMembers = [];
                this.channels = [];
                this.roles = [];
            }
            window.addEventListener('resize', () => this.updateItemsPerPage());
        },

        get filteredMembers() {
            return this.teamMembers.filter(member => {
                const matchesChannel = this.selectedChannel === '' ||
                    (Array.isArray(member.channel) && member.channel.includes(this.selectedChannel));
                const matchesRole = this.selectedRole === '' ||
                    (Array.isArray(member.role) && member.role.includes(this.selectedRole));
                const matchesSearch = member.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchesChannel && matchesRole && matchesSearch;
            });
        },
        get paginatedMembers() {
            let start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredMembers.slice(start, start + this.itemsPerPage);
        },
        totalPages() {
            return Math.ceil(this.filteredMembers.length / this.itemsPerPage);
        },
        nextPage() {
            if (this.currentPage < this.totalPages()) this.currentPage++;
        },
        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        filterItems() {
            this.currentPage = 1;
        },
        updateItemsPerPage() {
            this.itemsPerPage = window.innerWidth <= 1199 ? 6 : 8;
            this.currentPage = 1;
        }
        
    }
}
</script>


<style>
    .team-desc {
        padding: 20px 50px 20px 50px;
    }

    .descript-heading {
        margin-bottom: 40px;
    }

    .descript-heading h1 {
        font-weight: 600;
        font-size: 30px;
        letter-spacing: -0.03em;
        color: #101010;
        margin-bottom: 24px;
    }

    .descript-heading p {
        font-weight: 300;
        font-size: 16px;
        color: #101010;
    }
    .hidden {
        display: none;
    }
    .team-members {
        align-items: stretch; 
    }
    .team-members .team-member.card:hover {
        border: solid 1px #2194FF;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .team-members .team-member.card {
        padding: 25px 25px 32px 25px;
        height: 100%;
    }
    .team-members .team-member.card .team-prof {
        border-radius: 10px;
        transition: transform 0.3s ease-in-out;
        margin-bottom: 25px;
        width: 100%;
        height: 180px;
    }
    .team-members .team-member.card:hover .team-prof {
        transform: scale(1.03);
    }

    .team-members .team-member.card .text-center {
        text-align: center !important;
        padding: 0;
    }

    .card-name {
        font-weight: 600;
        font-size: 24px;
        color: #101010;
        margin-bottom: 18px;
        text-transform: capitalize;
    }

    .card-role {
        font-weight: 300;
        font-size: 16px;
        color: #717171;
        text-transform: capitalize;
    }

    /* Filter button & drop down */
    .team-navoption {
        margin-bottom: 24px;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: start;
    }

    button.iconic img {
        border-radius: 4px;
    }

    button.iconic {
        padding: 0;
        border: none !important;
        background-color: #fff !important;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    button.iconic:hover {
        padding: 0;
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
        align-items: flex-end;
        background-color: #fff;
        border-radius: 10px;
        padding: 10px 10px 15px 10px;
        width: 250px;
        justify-content: space-between;
        gap: 10px;
    }

    .drop-title {
        font-family: Sora;
        font-size: 14px;
        font-weight: 600;
        color: #101010;
    }

    .drop-option {
        font-family: Sora;
        font-size: 14px;
        font-weight: 300;
        color: #717171;
        text-transform: capitalize;
        @apply bg-white border border-gray-200 rounded shadow-md p-2;
        overflow-y: auto;
        s
    }

    /* Search button */
    .search-container {
        display: flex;
        flex-direction: row;
        width: 500px;
        height: 50px;
        justify-content: center;
        align-items: center;
        border: 1px solid #ddd;
        padding: 15px 24px 15px 24px;
        border-radius: 4px;
        background-color: white !important;
    }

    .search-container input {
        padding-left: 8px;
        border: none !important;
        border-radius: 0 !important;
        font-size: 16px;
    }

    .search-container input:focus {
        outline: none;
        border: 1px solid transparent;
        box-shadow: none;
    }

    .search-container img.search-icon {
        width: 20px;
        height: 20px;
    }

    /* Page controls */
    .pagination-controls {
        margin-top: 20px;
        text-align: right;
    }

    .pagination-controls .prev-btn,
    .nxt-btn {
        border: none !important;
        padding: 0 !important;
        color: #101010 !important;
        font-family: inherit;
        font-size: 14px;
        font-weight: 300;
    }

    /* .pagination-controls .prev-btn {
        margin-right: 30px;
    }
    .pagination-controls .nxt-btn {
        margin-left: 30px;
    } */
    .pagination-controls .prev-btn:hover,
    .nxt-btn:hover {
        background-color: transparent;
    }

    .pagination-controls .prev-btn:focus,
    .nxt-btn:focus {
        background-color: transparent;
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

    .team-details {
        text-align: left;
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
        width: 150px;
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
        top: 220px;
        width: 250px;
        border-radius: 10px;
        max-height: 300px;
        overflow-x: hidden;
        overflow-y: auto;
    }
    .drop-option3::-webkit-scrollbar{
        width: 2px;
    }
    .drop-option3::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .drop-option3::-webkit-scrollbar-thumb{
        background-color: #2194FF;
        border-radius: 10px; 
    }
    .active {
        @apply bg-blue-100 text-blue-600;
    }
    .text-danger {
        color: red;
        font-weight: 500;
        font-style: italic;
        text-transform: uppercase;
    }

/* <!--  media queries  --> */

@media only screen and (min-width:2500px) {
        .team-members .team-member.card .team-prof {
            height: 250px;
        }
    }
    
    @media only screen and (max-width:1500px) {
        .team-members .team-member.card .team-prof {
            height: 150px;
        }
        .card-name {
            font-size: 20px;
        }
    }
    @media only screen and (max-width:1440px) {
        .team-members .team-member.card .team-prof {
            height: 120px;
        }
    }

    @media only screen and (max-width: 1325px) {
        .team-members .team-member.card .team-prof {
            height: 100px;
        }
        div.drop-div {
            width: 230px;
        }
    }

    @media only screen and (max-width: 1262px) {
        .team-desc {
            padding: 0;
        }

        .search-container {
            padding: 5px 10px 5px 10px;
            width: 500px;
        }
        .drop-option3 {
            top: 200px;
        }
    }

    @media only screen and (max-width: 1024px) {
        .search-container {
            padding: 5px 10px 5px 10px;
            width: 350px;
        }

        .card-name {
            font-size: 20px;
        }
        .drop-content {
            display: flex;
            flex-direction: column;
            row-gap: 10px;
        }
        .drop-option3 {
            top: 195px;
        }
        .custom-roledrop {
            top: 250px;
        }
        ul.drop-option3.mt-1 {
            padding: 0;
        }
    }

    @media only screen and (max-width:975px) {
        .team-members .team-member.card.team-prof {
            height: 80px;
        }
    }

    @media only screen and (max-width: 768px) {
        .drop-content {
            display: flex;
            column-gap: 10px;
            row-gap: 10px;
            flex-direction: column;
            align-items: stretch;
        }

        .search-container {
            width: 250px;
        }

        /* .team-members .team-member.card.team-prof {
            height: 100px;
        } */
        
        ul.drop-option3.mt-1.custom-roledrop {
            top: 260px;
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
        }
        .team-navoption {
            display: flex;
            flex-direction: column-reverse;
            gap: 20px;
        }
        .drop-option3 {
            top: 260px;
        }
        ul.drop-option3.mt-1.custom-roledrop {
            top: 320px;
        }
    }

    @media only screen and (max-width: 425px) {
        .pagination-controls {
            text-align: center;
        }
        .team-members .team-member.card .team-prof {
            height: 150px;
        }
        .filter-opt {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        div.drop-div {
            width: 180px;
        }
        .drop-option3 {
            width: 200px;
            top: 275px;
        }
        ul.drop-option3.mt-1.custom-roledrop {
            top: 330px;
        }
    }
    @media only screen and (max-width:375px) {
        .team-members .team-member.card .team-prof {
            height: 100px;
        }
        .drop-option3 {
            left: 110px;
        }
        .drop-option3 {
            left: 125px;
        }
    }
    @media only screen and (max-width:320px) {
        .search-container {
            width: 180px;
        }
        .drop-option3 {
            top: 310px;
            width: 180px;
        }
        ul.drop-option3.mt-1.custom-roledrop {
            top: 370px;
        }
        .drop-option3 {
            left: 120px;
        }
    }
</style>

<?php get_footer('admin'); ?>