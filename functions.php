<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')):
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('child_theme_configurator_css')):
    function child_theme_configurator_css()
    {
        wp_enqueue_style('chld_thm_cfg_child', trailingslashit(get_stylesheet_directory_uri()) . 'style.css', array('hello-elementor', 'hello-elementor', 'hello-elementor-theme-style', 'hello-elementor-header-footer'));
    }
endif;
add_action('wp_enqueue_scripts', 'child_theme_configurator_css', 100);


require_once get_stylesheet_directory() . '/custom-functions.php';
// END ENQUEUE PARENT ACTION

function enqueue_bootstrap()
{

    $my_ver  = date("Y-m-d H:i:s");
    // Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    wp_enqueue_style(
        'semantic-ui-css',
        'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.9/semantic.min.css',
        array(),
        '2.2.9'
    );

    // Datepicker CSS
    wp_enqueue_style('datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
    wp_enqueue_style('owl-carousel-css', 'https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css');
    wp_dequeue_style('elementor-icons'); // Remove Elementor's FA4
    wp_enqueue_style('font-awesome-6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], '6.5.1');

    wp_enqueue_style('style-css', get_stylesheet_directory_uri() . '/assets/css/style.css', $my_ver);


    wp_enqueue_style('dashboard-css', get_stylesheet_directory_uri() . '/assets/css/dashboard.css', $my_ver);
    if (is_page('dashboard')) {
        wp_enqueue_style('new-dashboard-css', get_stylesheet_directory_uri() . '/assets/css/new-dashboard.css', $my_ver);
    }


    wp_enqueue_script('jquery');

    // Alpine.js
    // wp_enqueue_script('alpinejs', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', array('jquery'), $my_ver, true);
    // wp_enqueue_script(
    //     'alpinejss',
    //     'https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js',
    //     [],
    //     null,
    //     true
    // );
    // wp_script_add_data('alpinejss', 'defer', true);
    // Datepicker JS

    wp_enqueue_script(
        'semantic-ui-js',
        'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.9/semantic.min.js',
        ['jquery'],
        '2.2.9',
        true
    );

    // Bootstrap JS (with Popper.js)
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js');
    wp_enqueue_script('moment-js', 'https://cdn.jsdelivr.net/npm/moment/moment.min.js');
    wp_enqueue_script('datepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js');

    wp_enqueue_script('owl-carousel-js', 'https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js');
    wp_enqueue_script('FileSaver-js', 'https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js');


    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js');
    wp_enqueue_script('script-js', get_stylesheet_directory_uri() . '/assets/js/script.js', array('jquery'), $my_ver);
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap');



function enqueue_alpine()
{
    wp_enqueue_script(
        'alpinejs',
        'https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js',
        [],
        null,
        false // load in head
    );
}
add_action('wp_enqueue_scripts', 'enqueue_alpine');

add_filter('script_loader_tag', function ($tag, $handle) {
    if ('alpinejs' === $handle) {
        // Add defer before src
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}, 10, 2);




// invoice creator
add_action('elementor_pro/forms/new_record', function ($record, $handler) {
    $form_name = 'Invoice';
    $post_type = 'invoice-creator';
    $message = "";

    $submitted_form_name = $record->get_form_settings('form_name');
    $raw_fields = $record->get('fields');
    $post_title = $_POST['invoice_number'];
    if ($submitted_form_name !== $form_name) {
        return;
    }

    $message = isset($_POST['form_fields']["file_name"]) ? '' : 'Please upload the invoice PDF before submitting.';


    $tasks_data = [];
    $total_task = 0;

    // 1. Decode the "task_end" JSON
    $task_end = json_decode(stripslashes($_POST['form_fields']['task_end']), true);

    // 2. Get the IDs from the decoded JSON
    $task_ids = $task_end['id'] ?? [];

    $tasks_data = [];
    $total_task = 0;
    foreach ($task_ids as $id) {
        $channel = $_POST['form_fields']['tasks_channel'][$id] ?? '';
        $description = $_POST['form_fields']['tasks_description'][$id] ?? '';
        $hours = $_POST['form_fields']['tasks_hours'][$id] ?? '';
        $rate = $_POST['form_fields']['tasks_rate'][$id] ?? '';

        // ✅ Validation
        if (empty($channel)) {
            $message = "Task channel is required.";
            break;
        }
        if ($hours === null || $hours === '') {
            $message = "Task quantity is required.";
            break;
        }
        if ($rate === null || $rate === '') {
            $message = "Task rate is required.";
            break;
        }

        // ✅ If validation passed → add to tasks
        $tasks_data[] = [
            'tasks_channel'    => [$channel],
            'tasks_description' => $description,
            'tasks_hours'      => (int)$hours,
            'tasks_rate'       => (int)$rate,
        ];
        $total_task += (int)$hours * (int)$rate;
    }

    // 4. Now you can pass to update_field
    // update_field('your_tasks', $tasks, 2885);


    // 1. Decode the "deduct_end" JSON
    $deduct_end = json_decode(stripslashes($_POST['form_fields']['deduct_end']), true);

    // 2. Get the IDs from the decoded JSON
    $deduct_ids = $deduct_end['id'] ?? [];

    $deductions_data = [];
    $total_deductions = 0;
    foreach ($deduct_ids as $i => $id) {
        if ($i === count($deduct_ids) - 1) continue;
        $channel = $_POST['form_fields']['deduct_channel'][$id] ?? '';
        $description = $_POST['form_fields']['deduct_description'][$id] ?? '';
        $hours = $_POST['form_fields']['deduct_hours'][$id] ?? '';
        $rate = $_POST['form_fields']['deduct_rate'][$id] ?? '';

        // ✅ Validation
        if (empty($channel)) {
            $message = "Deduction channel is required.";
            break;
        }
        if ($hours === null || $hours === '') {
            $message = "Deduction quantity is required.";
            break;
        }
        if ($rate === null || $rate === '') {
            $message = "Deduction rate is required.";
            break;
        }

        $deductions_data[] = [
            'deduct_channel'    => [$channel],
            'deduct_description' => $description,
            'deduct_hours'      => (int)$hours,
            'deduct_rate'       => (int)$rate,
        ];
        $total_deductions += floatval($hours) * floatval($rate);
    }
    if ($message) {
        header('Content-Type: application/json');
        wp_send_json_error(['message' => $message]);
    }
    // if (isset($_POST['form_fields']['tasks_channel'])) {
    // foreach ($_POST['form_fields']['tasks_channel'] as $index => $channel) {
    //     array_push($tasks_data, array(
    //         'tasks_channel'    => $channel ?? $message = "Channel is required.",
    //         'tasks_description' => $_POST['form_fields']['tasks_description'][$index] ?? '',
    //         'tasks_hours'      => $_POST['form_fields']['tasks_hours'][$index] ?? $message = "Hours are required.",
    //         'tasks_rate'       => $_POST['form_fields']['tasks_rate'][$index] ?? $message = "Rate is required.",
    //     ));
    //     $total_task += $_POST['form_fields']['tasks_hours'][$index] * $_POST['form_fields']['tasks_rate'][$index];
    // }
    // }

    // $tasks_data = [];
    // $total_task = 0;
    // foreach ($_POST['form_fields']['tasks_channel'] as $index => $channel) {
    //     // Validate required fields
    //     $description = $_POST['form_fields']['tasks_description'][$index] ?? '';
    //     $hours = $_POST['form_fields']['tasks_hours'][$index] ?? null;
    //     $rate = $_POST['form_fields']['tasks_rate'][$index] ?? null;
    //     if (empty($channel)) {
    //         $message = "Channel is required.";
    //         break;
    //     }
    //     if ($hours === null || $hours === '') {
    //         $message = "Quantity is required.";
    //         break;
    //     }
    //     if ($rate === null || $rate === '') {
    //         $message = "Rate is required.";
    //         break;
    //     }
    //     // Store the task
    //     $tasks_data[] = array(
    //         'tasks_channel'    => $channel,
    //         'tasks_description' => $description,
    //         'tasks_hours'      => $hours,
    //         'tasks_rate'       => $rate,
    //     );
    //     // Safely compute total
    //     $total_task += floatval($hours) * floatval($rate);
    // }
    // if ($message) {
    //     header('Content-Type: application/json');
    //     wp_send_json_error(['message' => $message]);
    // }
    // $deductions_data = [];
    // $total_deductions = 0;
    // if (isset($_POST['form_fields']['deduct_channel'])) {
    //     foreach ($_POST['form_fields']['deduct_channel'] as $index => $channel) {
    //         $deduction = 0;
    //         array_push($deductions_data, array(
    //             'deduct_channel'    => $channel,
    //             'deduct_description' => $_POST['form_fields']['deduct_description'][$index] ?? '',
    //             'deduct_hours'      => $_POST['form_fields']['deduct_hours'][$index] ?? '',
    //             'deduct_rate'       => $_POST['form_fields']['deduct_rate'][$index] ?? '',
    //         ));
    //         $deduction = $_POST['form_fields']['deduct_hours'][$index] * $_POST['form_fields']['deduct_rate'][$index];
    //     }
    // }
    // foreach ($_POST['form_fields']['deduct_channel'] as $index => $channel) {
    // Validate required fields
    // if (empty($channel)) {
    //     $message = "Deduction channel is required.";
    //     break;
    // }
    // $description = $_POST['form_fields']['deduct_description'][$index] ?? '';
    // $hours = $_POST['form_fields']['deduct_hours'][$index] ?? null;
    // $rate = $_POST['form_fields']['deduct_rate'][$index] ?? null;
    // if ($hours === null || $hours === '') {
    //     $message = "Deduction hours are required.";
    //     break;
    // }
    // if ($rate === null || $rate === '') {
    //     $message = "Deduction rate is required.";
    //     break;
    // }
    // Add to deductions data array
    //     $deductions_data[] = array(
    //         'deduct_channel'     => $channel,
    //         'deduct_description' => $description,
    //         'deduct_hours'       => $hours,
    //         'deduct_rate'        => $rate,
    //     );
    //     // Safely calculate deduction
    //     $total_deductions += floatval($hours) * floatval($rate);
    // }

    // $new_post = [
    //     'post_title'  => $post_title,
    //     'post_status' => 'publish',
    //     'post_type'   => $post_type,
    //     'meta_input'  => [
    //         'invoice_number' => $post_title,
    //         'period_covered' => $_POST['form_fields']['period_covered'],
    //         'due_date' => $_POST['form_fields']['due_date'],
    //         'payment_method' => $_POST['form_fields']['payment_method'],
    //         'payment_details' => $_POST['form_fields']['payment_details'],
    //         'email' => $_POST['form_fields']['email'],
    //         'date_submitted' => date('d/m/Y'),
    //         'user_id' => $_SESSION['userID'],
    //         // 'user_role' => $user_role,
    //         'status' => 'pending',
    //     ],
    // ];

    // $post_id = wp_insert_post($new_post);

    // if ($post_id) {
    //     if ($tasks_data != []) update_field('tasks', $tasks_data, $post_id);
    //     if ($deductions_data != []) update_field('deductions', $deductions_data, $post_id);
    //     update_field('amount', $total_task - $total_deductions, $post_id);
    //     $attachment_id = upload_remote_file_to_media($_POST['form_fields']["file_name"], $post_id);
    //     update_post_meta($post_id, "file_name", $attachment_id);
    // }

    $new_post = [
        'post_title'  => $post_title,
        'post_status' => 'publish',
        'post_type'   => $post_type,
    ];

    $post_id = wp_insert_post($new_post);

    if ($post_id) {
        // Update SCF fields instead of meta_input
        $userID = $_SESSION['userID'];

        $current_payment_method = get_field('payment_platform', $userID);
        $current_payment_details = get_field('payment_details', $userID, false);
        $current_email = get_field('email', $userID);
        $current_role = get_field('role', $userID);
        $current_currency = get_field('currency', $userID);
        $full_name = get_field('fullname', $userID);
        $full_address = get_field('full_address', $userID);
        $discord_name = get_field('discord_name', $userID);
        $main_email = get_field('main_email', $userID);
        $email_pay_id = get_field('email_pay_id', $userID);

        $payment_method = $_POST['form_fields']['payment_method'] ?? $current_payment_method;
        $payment_details = $_POST['form_fields']['payment_details'] ?? $current_payment_details;
        $email = $_POST['form_fields']['email'] ?? $current_email;


        update_field('invoice_number', $post_title, $post_id);
        update_field('role', $current_role, $post_id);
        // update_field('currency', [$current_currency], $post_id);
        update_field('fullname', $full_name, $post_id);
        update_field('full_address', $full_address, $post_id);
        update_field('discord_name', $discord_name, $post_id);

        update_field('period_covered', $_POST['period_covered'], $post_id);
        update_field('due_date', $_POST['due_date'], $post_id);
        update_field('payment_method', $payment_method, $post_id);
        update_field('payment_details', $payment_details, $post_id);
        update_field('email', $main_email, $post_id);
        update_field('pay_email', $email_pay_id, $post_id);
        update_field('date_submitted', date('d/m/Y'), $post_id);
        update_field('user_id', $userID, $post_id);
        update_field('status', 'PENDING', $post_id);
        update_field('standard', 'CONTRACTOR', $post_id);

        // Other updates
        if ($tasks_data != []) update_field('your_tasks', $tasks_data, $post_id);
        if ($deductions_data != []) update_field('your_deductions', $deductions_data, $post_id);
        update_field('amount', $total_task - $total_deductions, $post_id);

        $attachment_id = upload_remote_file_to_media($_POST['form_fields']["file_name"], $post_id);
        update_field('file_name', $attachment_id, $post_id);


        wp_insert_post([
            'post_title'  => $post_title,
            'post_type'   => 'invoice-number',
            'post_status' => 'publish',
        ]);
    }

    if ($message) {
        header('Content-Type: application/json');
        wp_send_json_error(['message' => $message]);
    }
    $handler->add_response_data('redirect_url', home_url('/contractor-invoice-creator/'));
}, 10, 2);
function upload_remote_file_to_media($file_url, $post_id = 0)
{
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $tmp = download_url($file_url);
    if (is_wp_error($tmp)) {
        return false;
    }
    $file_array = array(
        'name'     => basename($file_url),
        'tmp_name' => $tmp,
    );
    // Upload the file to WordPress media
    $attachment_id = media_handle_sideload($file_array, $post_id);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        return false;
    }
    return $attachment_id;
}


// add channel
function handle_channel_form_submission()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_channel'])) {

        // Verify nonce for security
        if (!isset($_POST['channel_nonce']) || !wp_verify_nonce($_POST['channel_nonce'], 'add_channel_nonce')) {
            wp_die('Security check failed.');
        }

        // Sanitize user input
        $channel_url = sanitize_text_field($_POST['channel_url']);
        $channel_name = sanitize_text_field($_POST['channel_name']);
        $channel_description = sanitize_textarea_field($_POST['channel_description']);
        // $date_created = sanitize_text_field($_POST['date_created']);
        $date_created = current_time('Y-m-d'); // Automatically set current date

        // Insert into the custom post type "channel"
        $channel_id = wp_insert_post([
            'post_title'   => $channel_name,
            'post_content' => $channel_description,
            'post_status'  => 'publish',
            'post_type'    => 'channel',
        ]);

        if ($channel_id) {
            // Save custom fields
            update_post_meta($channel_id, 'channel_name', $channel_name);
            update_post_meta($channel_id, 'channel_url', $channel_url);
            update_post_meta($channel_id, 'channel_description', $channel_description);
            update_post_meta($channel_id, 'date_created', $date_created);

            // Redirect to avoid form resubmission & show success message
            wp_redirect(add_query_arg('success', '1', $_SERVER['REQUEST_URI']));
            exit;
        }
    }
}
// add_action('init', 'handle_channel_form_submission');
add_action('wp_ajax_add_channel', 'add_channel_callback');
add_action('wp_ajax_nopriv_add_channel', 'add_channel_callback');

function add_channel_callback()
{
    // Verify nonce for security
    if (!isset($_POST['channel_nonce']) || !wp_verify_nonce($_POST['channel_nonce'], 'add_channel_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    // Sanitize user input
    $channel_url = sanitize_text_field($_POST['channel_url']);
    $channel_name = sanitize_text_field($_POST['channel_name']);
    $channel_description = sanitize_textarea_field($_POST['channel_description']);
    $date_created = sanitize_text_field($_POST['date_created']);

    // Check for duplicate channel_url
    $existing = new WP_Query([
        'post_type'  => 'channel',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key'   => 'channel_url',
                'value' => $channel_url,
                'compare' => '='
            ]
        ]
    ]);
    if ($existing->have_posts()) {
        wp_send_json_error(['message' => 'Channel URL already exists. Please use a different one.']);
    }

    // Insert into the custom post type "channel"
    $channel_id = wp_insert_post([
        'post_title'   => $channel_name,
        'post_content' => $channel_description,
        'post_status'  => 'publish',
        'post_type'    => 'channel',
    ]);

    if (is_wp_error($channel_id)) {
        wp_send_json_error(['message' => 'Failed to save channel.']);
    }

    // Save custom fields
    update_post_meta($channel_id, 'channel_name', $channel_name);
    update_post_meta($channel_id, 'channel_url', $channel_url);
    update_post_meta($channel_id, 'channel_description', $channel_description);
    update_post_meta($channel_id, 'date_created', $date_created);
    update_post_meta($channel_id, 'channel_status', 'Archived');

    // Respond with JSON instead of redirecting
    wp_send_json_success([
        'redirect_url' => get_permalink($channel_id)
    ]);
}


// Disable admin bar on frontend for all users
add_filter('show_admin_bar', '__return_false');


// Sign up and onboarding handling
add_action('elementor_pro/forms/new_record', function ($record, $handler) {
    $form_name = $record->get_form_settings('form_name');
    $raw_fields = $record->get('fields');
    $message = "";

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    switch ($form_name) {
        case 'contractor_signin':
            $email = sanitize_email($raw_fields['email_address']['value'] ?? '');
            $password = sanitize_text_field($raw_fields['password']['value'] ?? '');

            if (empty($email) || empty($password)) {
                $message = "Login failed: Email and password are required.";
                break;
            }

            $user = get_user_by('email', $email);

            // First, check WordPress users table (users.php / wp_users)
            if ($user && wp_check_password($password, $user->user_pass, $user->ID)) {
                // Success - WP user
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);

                $_SESSION['userID'] = $user->ID;
                $_SESSION['fullName'] = $user->display_name;
                $_SESSION['userEmail'] = $email;

                $redirect_url = home_url('/dashboard/');
                $handler->add_response_data('message', 'Login successful! Redirecting to dashboard...');
                $handler->add_response_data('redirect_url', $redirect_url);
                break;
            }


            // If not found in WP users, check custom post type "team"
            $args = [
                'post_type'  => 'team',
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key'   => 'email_address',
                        'value' => $email,
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => 1
            ];

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                $query->the_post();
                $stored_password = get_post_meta(get_the_ID(), 'password', true);

                if ($stored_password === $password) {
                    $userId = get_the_ID();
                    $full_name = get_field('fullname', $userId);

                    $_SESSION['userID'] = $userId;
                    $_SESSION['fullName'] = $full_name;
                    $_SESSION['userEmail'] = $email;

                    $redirect_url = home_url('/profile/');
                    $handler->add_response_data('message', 'Login successful! Redirecting to dashboard...');
                    $handler->add_response_data('redirect_url', $redirect_url);
                } else {
                    $message = "Login failed: Invalid password.";
                }
            } else {
                $message = "Login failed: Email not found.";
            }

            break;

        case 'contractor_signup':
            $email = sanitize_email($raw_fields['email_address']['value'] ?? '');
            $password = sanitize_text_field($raw_fields['password']['value'] ?? '');
            $confirm_password = sanitize_text_field($raw_fields['confirm_password']['value'] ?? '');

            // Check if passwords match
            if (empty($email) || empty($password) || empty($confirm_password)) {
                $message = 'Signup failed: All fields are required.';
                break;
            }

            if ($password !== $confirm_password) {
                $message = 'Passwords do not match.';
                break;
            }

            // Check if email already exists in 'team' post type (via post_title or ACF field)
            $existing_post = get_posts([
                'post_type'  => 'team',
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key'     => 'email_address',
                        'value'   => $email,
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => 1,
                'fields' => 'ids'
            ]);

            if (!empty($existing_post)) {
                $message = 'Email already exists. Please use a different email.';
                break;
            }

            // Create new team member post
            $post_id = wp_insert_post([
                'post_title'  => $email,
                'post_type'   => 'team',
                'post_status' => 'publish',
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                $_SESSION['post_id'] = $post_id; // Store for onboarding

                update_field('email_address', $email, $post_id);
                update_field('password', $password, $post_id);
                update_field('team_status', 'CONTRACTOR', $post_id);
                update_field('contractor_status', 'Archived', $post_id);

                $handler->add_response_data('message', 'Signup successful! Redirecting to Onboarding...');
                $handler->add_response_data('redirect_url', home_url('/signup-onboarding/'));
            } else {
                $message = 'Error creating new team member.';
            }

            break;

        case 'contractor_signup_onboarding':
            $full_name = sanitize_text_field($raw_fields['fullname']['value'] ?? '');
            $discord_name = sanitize_text_field($raw_fields['discord_name']['value'] ?? '');
            $discord_tag = sanitize_text_field($raw_fields['discord_tag']['value'] ?? '');
            $full_address = sanitize_text_field($raw_fields['full_address']['value'] ?? '');
            $main_email = sanitize_email($raw_fields['main_email']['value'] ?? '');
            $paypalwise_email = sanitize_email($raw_fields['paypalwise_email']['value'] ?? '');
            $role = sanitize_text_field($raw_fields['role']['value'] ?? '');
            $currency = sanitize_text_field($raw_fields['currency']['value'] ?? '');
            $payment_platform = sanitize_text_field($raw_fields['payment_platform']['value'] ?? '');
            $payment_details = sanitize_textarea_field($raw_fields['payment_details']['value'] ?? '');
            $bank_account_name = sanitize_text_field($raw_fields['bank_account_name']['value'] ?? '');
            $bank_account_number = sanitize_text_field($raw_fields['bank_account_number']['value'] ?? '');
            $bank_code = sanitize_text_field($raw_fields['bank_code']['value'] ?? '');

            $post_id = $_SESSION['post_id'] ?? null;

            if ($post_id && get_post_type($post_id) === 'team') {
                // Update the team member profile
                update_field('fullname', $full_name, $post_id);

                update_field('discord_name', $discord_name, $post_id);
                update_field('discord_tag', $discord_tag, $post_id);
                update_field('full_address', $full_address, $post_id);
                update_field('main_email', $main_email, $post_id);
                update_field('email_pay_id', $paypalwise_email, $post_id);
                update_field('role', $role, $post_id);
                update_field('currency', $currency, $post_id);
                update_field('payment_platform', $payment_platform, $post_id);
                update_field('payment_details', $payment_details, $post_id);
                update_field('bank_account_name', $bank_account_name, $post_id);
                update_field('bank_account_number', $bank_account_number, $post_id);
                update_field('bank_code', $bank_code, $post_id);
                // Also update the post title to full name if you want
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $full_name,
                ]);
                $_SESSION['fullName'] = $full_name;
                $_SESSION['userID'] = $post_id;

                $email_address = get_field('email_address', $post_id);

                send_email_notification_after_signup($post_id, $full_name, $email_address);
                // $handler->add_response_data('message', 'Profile completed! Redirecting to dashboard...');
                // $handler->add_response_data('redirect_url', home_url('/profile/'));

                $handler->add_response_data('redirect_url', home_url('/request-submitted/'));
            } else {
                $message = 'Onboarding failed: User session expired. Please sign up again.';
            }

            break;

        default:
            break;
    }

    if ($message) {
        header('Content-Type: application/json');
        wp_send_json_error(['message' => $message]);
    }
}, 10, 2);


// Single Channel Details
add_action('wp_ajax_update_channel_details', 'handle_update_channel_details');
function handle_update_channel_details()
{
    $channel_id     = intval($_POST['channel_id']);
    $new_name       = sanitize_text_field($_POST['channel_name']);
    $desc           = sanitize_textarea_field($_POST['channel_description']);
    $channelUrl     = esc_url_raw($_POST['channel_url']); // sanitize as URL
    $status         = sanitize_text_field($_POST['channel_status']);
    $post_title     = sanitize_text_field($_POST['post_title']);
    $slug           = sanitize_title($new_name);

    $selected_contractors = isset($_POST['selected_contractors']) ? json_decode(stripslashes($_POST['selected_contractors']), true) : [];

    if (!$channel_id) {
        wp_send_json_error(['message' => 'Invalid channel ID']);
    }

    // Check for duplicate channel_url
    if (!empty($channelUrl)) {
        $existing = new WP_Query([
            'post_type'   => 'channel',
            'post_status' => 'publish',
            'meta_query'  => [[
                'key'     => 'channel_url',
                'value'   => $channelUrl,
                'compare' => '='
            ]]
        ]);

        if ($existing->have_posts()) {
            while ($existing->have_posts()) {
                $existing->the_post();
                if (get_the_ID() != $channel_id) {
                    wp_send_json_error(['message' => 'Channel URL already exists. Please use a different one.']);
                }
            }
            wp_reset_postdata();
        }
    }

    // Save updates for the channel itself
    $old_name = get_field('channel_name', $channel_id);
    update_field('channel_name', $new_name, $channel_id);
    update_field('channel_description', $desc, $channel_id);
    update_field('channel_status', $status, $channel_id);
    update_field('channel_url', $channelUrl, $channel_id);

    wp_update_post([
        'ID'           => $channel_id,
        'post_title'   => $post_title,
        'post_excerpt' => $desc,
        'post_content' => $desc,
        'post_name'    => $slug
    ]);

    // Handle image upload if exists
    if (!empty($_FILES['channel_profile_photo']) && !empty($_FILES['channel_profile_photo']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attachment_id = media_handle_upload('channel_profile_photo', $channel_id);

        if (!is_wp_error($attachment_id)) {
            update_field('channel_profile_photo', $attachment_id, $channel_id);
        }
    }

    // Update all matching channel-insight posts
    $insights = get_posts([
        'post_type'      => 'channel-insight',
        'posts_per_page' => -1,
        'meta_query'     => [[
            'key'     => 'channel_name',
            'value'   => $old_name,
            'compare' => '='
        ]]
    ]);

    foreach ($insights as $insight) {
        update_field('channel_name', $new_name, $insight->ID);
    }

    // Assign new contractors
    foreach ($selected_contractors as $contractor_id) {
        $existing_channels = get_field('your_channel', $contractor_id);
        if (!$existing_channels) {
            $existing_channels = [];
        }

        if (!in_array($channel_id, $existing_channels)) {
            $existing_channels[] = $channel_id;
            update_field('your_channel', $existing_channels, $contractor_id);
        }
    }

    wp_send_json_success();
}

// add_action('wp_ajax_update_channel_details', 'handle_update_channel_details');
// function handle_update_channel_details()
// {
//     $channel_id     = intval($_POST['channel_id']);
//     $new_name       = sanitize_text_field($_POST['channel_name']);
//     $desc           = sanitize_textarea_field($_POST['channel_description']);
//     $channelUrl     = sanitize_text_field($_POST['channel_url']);
//     $status         = sanitize_text_field($_POST['channel_status']);
//     $post_title     = sanitize_text_field($_POST['post_title']);
//     $slug           = sanitize_title($new_name);

//     $selected_contractors = isset($_POST['selected_contractors']) ? json_decode(stripslashes($_POST['selected_contractors']), true) : [];

//     if (!$channel_id) {
//         wp_send_json_error('Invalid channel ID');
//     }

//     // Save updates for the channel itself
//     $old_name = get_field('channel_name', $channel_id);
//     update_field('channel_name', $new_name, $channel_id);
//     update_field('channel_description', $desc, $channel_id);
//     update_field('channel_status', $status, $channel_id);
//     update_field('channel_url', $status, $channel_id);

//     wp_update_post([
//         'ID'           => $channel_id,
//         'post_title'   => $post_title,
//         'post_excerpt' => $desc,
//         'post_content' => $desc,
//         'post_name'    => $slug
//     ]);

//     //  Handle image upload if exists
//     if (!empty($_FILES['channel_profile_photo']) && !empty($_FILES['channel_profile_photo']['tmp_name'])) {
//         require_once(ABSPATH . 'wp-admin/includes/file.php');
//         require_once(ABSPATH . 'wp-admin/includes/media.php');
//         require_once(ABSPATH . 'wp-admin/includes/image.php');

//         $attachment_id = media_handle_upload('channel_profile_photo', $channel_id);

//         if (!is_wp_error($attachment_id)) {
//             update_field('channel_profile_photo', $attachment_id, $channel_id);
//         }
//     }

//     //  Update all matching channel-insight posts
//     $insights = get_posts([
//         'post_type' => 'channel-insight',
//         'posts_per_page' => -1,
//         'meta_query' => [[
//             'key' => 'channel_name',
//             'value' => $old_name,
//             'compare' => '='
//         ]]
//     ]);

//     foreach ($insights as $insight) {
//         update_field('channel_name', $new_name, $insight->ID);
//     }

//     //  Assign new contractors
//     foreach ($selected_contractors as $contractor_id) {
//         $existing_channels = get_field('your_channel', $contractor_id);
//         if (!$existing_channels) {
//             $existing_channels = [];
//         }

//         if (!in_array($channel_id, $existing_channels)) {
//             $existing_channels[] = $channel_id;
//             update_field('your_channel', $existing_channels, $contractor_id);
//         }
//     }

//     wp_send_json_success();
// }


// Add New Channel Insights at the Single Channel
add_action('wp_ajax_add_new_channel_insight', 'handle_add_new_channel_insight');
function handle_add_new_channel_insight()
{
    // $channel_id   = intval($_POST['channel_id']);
    // $channel_name = sanitize_text_field($_POST['channel_name']);
    // $date_create  = sanitize_text_field($_POST['date_create']);
    // $views        = intval($_POST['channel_views']);
    // $subs         = intval($_POST['channel_subscribers']);
    // $videos       = intval($_POST['channel_videos']);
    // $revenue       = intval($_POST['revenue_amount']);

    $channel_id   = isset($_POST['channel_id']) ? intval($_POST['channel_id']) : 0;
    $channel_name = isset($_POST['channel_name']) ? sanitize_text_field($_POST['channel_name']) : '';
    $date_create  = isset($_POST['date_create']) ? sanitize_text_field($_POST['date_create']) : '';

    $views   = isset($_POST['channel_views']) ? intval(sanitize_text_field($_POST['channel_views'])) : 0;
    $subs    = isset($_POST['channel_subscribers']) ? intval(sanitize_text_field($_POST['channel_subscribers'])) : 0;
    $videos  = isset($_POST['channel_videos']) ? intval(sanitize_text_field($_POST['channel_videos'])) : 0;
    $revenue = isset($_POST['revenue_amount']) ? intval(sanitize_text_field($_POST['revenue_amount'])) : 0;


    if (!$channel_id || empty($channel_name)) {
        wp_send_json_error('Missing channel ID or channel name');
    }

    // Format date
    $formatted_date = '';
    if (!empty($date_create)) {
        $timestamp = strtotime($date_create);
        if ($timestamp) {
            $formatted_date = date('F Y', $timestamp);
        }
    }

    $post_title  = $channel_name . ' - ' . $formatted_date;

    // Create new insight post
    // $new_post = [
    //     'post_type'   => 'channel-insight',
    //     'post_status' => 'publish',
    //     'post_title'  => $channel_name . ' - Insight – ' . $formatted_date,
    // ];

    // $insight_id = wp_insert_post($new_post);
    // Try to find an existing post with that exact title in the same post type

    $field_key = 'your_channel_insights';

    $args = array(
        'title'        => $post_title,
        'post_type'    => 'channel-insight',
        'post_status'  => 'publish',
        'numberposts'  => 1,
    );

    $existing_posts = get_posts($args);
    $existing = !empty($existing_posts) ? $existing_posts[0] : null;

    if ($existing) {
        $insight_id = $existing->ID; // already exists
        $tasks_channel_insights = get_field($field_key, $insight_id);
        if ($tasks_channel_insights) wp_send_json_error('You have already added an insight for this month.');
    } else {
        // Create new insight post
        $new_post = [
            'post_type'   => 'channel-insight',
            'post_status' => 'publish',
            'post_title'  => $post_title,
        ];

        $insight_id = wp_insert_post($new_post);
    }

    if (is_wp_error($insight_id)) {
        wp_send_json_error('Failed to create insight');
    }

    // Save ACF fields
    update_field('date_create', $date_create, $insight_id);
    update_field('channel_name', $channel_name, $insight_id);
    update_field('revenue_amount', $revenue, $insight_id);
    update_field('channel_views', $views, $insight_id);
    update_field('channel_subscribers', $subs, $insight_id);
    update_field('channel_videos', $videos, $insight_id);



    // store new rew insight in repeater
    $new_row = array(
        'date_create_insight' => $date_create, // YYYY-MM-DD
        'channel_views'       => $views,
        'channel_subscribers' => $subs,
        'channel_videos'      => $videos,
        'revenue_amount'      => $revenue,
    );



    $tasks_channel_insights = get_field($field_key, $insight_id); // may return array or false

    if (! $tasks_channel_insights) {
        // no existing value yet
        update_field($field_key, array($new_row), $insight_id);
    } else {
        // ensure it's an array before merging
        if (! is_array($tasks_channel_insights)) {
            $tasks_channel_insights = array();
        }

        // append new data as another row
        $tasks_channel_insights[] = $new_row;

        // save back
        update_field($field_key, $tasks_channel_insights, $insight_id);
    }

    wp_send_json_success(['id' => $insight_id]);
}



// Single Channel Details Delete Button, archiving and draft of a channel
add_action('wp_ajax_archive_channel', 'archive_channel');
function archive_channel()
{
    $post_id = intval($_POST['channel_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid channel ID');
    }

    $updated = wp_update_post([
        'ID'          => $post_id,
        'post_status' => 'draft',
    ]);

    if ($updated) {
        update_field('channel_status', 'Archived', $post_id);
        wp_send_json_success('Channel archived.');
    } else {
        wp_send_json_error('Failed to archive channel.');
    }
}

// Single Channel Insights
function update_channel_insights_post()
{
    if (!isset($_POST['update_insights_nonce']) || !wp_verify_nonce($_POST['update_insights_nonce'], 'ajax_nonce')) {
        wp_send_json_error(['message' => 'Invalid request. Nonce verification failed.']);
    }

    $insight_id = isset($_POST['id']) ? $_POST['id'] : [];

    // if (!empty($_POST['channel_views'])) {
    //     update_post_meta($_POST['id'], 'channel_views', sanitize_text_field($_POST['channel_views']));
    // }
    // if (!empty($_POST['revenue_amount'])) {
    //     update_post_meta($_POST['id'], 'revenue_amount', sanitize_text_field($_POST['revenue_amount']));
    // }
    // if (!empty($_POST['channel_subscribers'])) {
    //     update_post_meta($_POST['id'], 'channel_subscribers', sanitize_text_field($_POST['channel_subscribers']));
    // }
    // if (!empty($_POST['channel_videos'])) {
    //     update_post_meta($_POST['id'], 'channel_videos', sanitize_text_field($_POST['channel_videos']));
    // }

    $field_key = 'your_channel_insights';
    $tasks_channel_insights = get_field($field_key, $insight_id); // may return array or false
    // store new rew insight in repeater
    $new_row = array(
        'date_create_insight' => $tasks_channel_insights[0]['date_create_insight'], // YYYY-MM-DD
        'channel_views'       => sanitize_text_field($_POST['channel_views']),
        'channel_subscribers' => sanitize_text_field($_POST['channel_subscribers']),
        'channel_videos'      => sanitize_text_field($_POST['channel_videos']),
        'revenue_amount'      => sanitize_text_field($_POST['revenue_amount']),
    );


    update_field($field_key, array($new_row), $insight_id);

    wp_send_json_success([
        'message' => 'Insights updated successfully.',
        'views' => sanitize_text_field($_POST['channel_views']),
        'revenue' => sanitize_text_field($_POST['revenue_amount']),
        'subscribers' => sanitize_text_field($_POST['channel_subscribers']),
        'videos' => sanitize_text_field($_POST['channel_videos']),
        'id' => $insight_id,
    ]);
}

// Handle AJAX request for logged-in users
add_action('wp_ajax_update_channel_insights_post', 'update_channel_insights_post');

// Handle AJAX request for non-logged-in users
add_action('wp_ajax_nopriv_update_channel_insights_post', 'update_channel_insights_post');


// date picker at Single Team
function enqueue_monthpicker_scripts()
{

    wp_enqueue_script('jquery-ui-datepicker');

    wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
}
add_action('wp_enqueue_scripts', 'enqueue_monthpicker_scripts');


// Add Channel at Single Team
add_action('wp_ajax_assign_channels_to_team', 'assign_channels_to_team');
function assign_channels_to_team()
{
    $team_id = intval($_POST['team_id']);
    $selected_channels = json_decode(stripslashes($_POST['selected_channels']), true);
    if (!$team_id || empty($selected_channels)) {
        wp_send_json_error('Missing team ID or channels.');
    }
    $existing_channels = get_field('your_channel', $team_id) ?: [];
    $new_channel_added = false;
    foreach ($selected_channels as $channel_id) {
        if (!in_array($channel_id, $existing_channels)) {
            $existing_channels[] = $channel_id;
            $new_channel_added = true;
        }
    }
    update_field('your_channel', $existing_channels, $team_id);
    if ($new_channel_added) {
        update_field('date_joined', current_time('d-m-Y'), $team_id);
    }
    wp_send_json_success('Channels assigned successfully.');
}

//select channel at invoice creator
add_action('wp_footer', function () {
    global $post;
    if ($post && $post->post_name == 'contractor-invoice-creator') {
        // $user_id = get_current_user_id();
        $user_id =  $_SESSION['userID'];
        // $user_channels = get_field('team_channels', 'user_' . $user_id);
        $user_channels = get_field('your_channel', $user_id);
        $channels_data = [];
        if (!empty($user_channels) && is_array($user_channels)) {
            foreach ($user_channels as $channel_id) {
                $channels_data[] = [
                    'id'   => $channel_id,
                    'name' => get_the_title($channel_id),
                ];
            }
        }
?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const channels = <?php echo json_encode($channels_data); ?>;
                const selects = document.querySelectorAll(
                    "select[name^='form_fields[tasks_channel'], select[name^='form_fields[deduct_channel']"
                );
                const deductionAddButton = document.querySelector('.elementor-field-group-deduct_end .repeater-field-button-add');
                const deductionWarpItem = document.querySelector('.elementor-field-group-deduct_end .repeater-field-warp-item');
                const tasksAddButton = document.querySelector('.elementor-field-group-tasks_end .repeater-field-button-add');
                const tasksWarpItem = document.querySelector('.elementor-field-group-tasks_end .repeater-field-warp-item');
                if (channels.length === 0) {
                    // Disable selects
                    selects.forEach(select => {
                        select.innerHTML = '<option value="">No channel assigned</option>';
                        select.disabled = true;
                    });
                    // Disable repeater buttons
                    if (deductionAddButton) deductionAddButton.disabled = true;
                    if (tasksAddButton) tasksAddButton.disabled = true;
                    // Prevent click logic
                    if (deductionAddButton && deductionWarpItem) {
                        deductionAddButton.addEventListener('click', e => e.preventDefault());
                    }
                    if (tasksAddButton && tasksWarpItem) {
                        tasksAddButton.addEventListener('click', e => e.preventDefault());
                    }
                    Swal.fire({
                        title: 'No Channel Assigned',
                        text: 'You need to be assigned to a channel before you can create an invoice.',
                        icon: 'warning',
                        confirmButtonText: 'Okay',
                        showCancelButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        customClass: {
                            confirmButton: 'my-confirm-btn'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Redirecting...',
                                text: 'Please wait...',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                window.location.href = '/profile';
                            }, 1000);
                        }
                    });
                } else {
                    selects.forEach(select => {
                        select.innerHTML = '<option value="">Select a Channel</option>';
                        channels.forEach(channel => {
                            const option = document.createElement("option");
                            option.value = channel.id;
                            option.textContent = channel.name;
                            select.appendChild(option);
                        });
                    });
                    if (deductionAddButton) {
                        deductionAddButton.disabled = false;
                        deductionAddButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            const hiddenFields = deductionWarpItem?.querySelectorAll('.repeater-field-item[style*="display: none"], .repeater-field-item:not([style])');
                            if (hiddenFields && hiddenFields.length > 0) {
                                hiddenFields[0].style.display = 'block';
                            }
                        });
                    }
                    if (tasksAddButton) {
                        tasksAddButton.disabled = false;
                        tasksAddButton.addEventListener('click', function(e) {
                            e.preventDefault();
                            const hiddenFields = tasksWarpItem?.querySelectorAll('.repeater-field-item[style*="display: none"], .repeater-field-item:not([style])');
                            if (hiddenFields && hiddenFields.length > 0) {
                                hiddenFields[0].style.display = 'block';
                            }
                        });
                    }
                }
            });
        </script>
    <?php
    }
});


// Update Function of REVENUE page
add_action('rest_api_init', function () {
    register_rest_route('clickable/v1', '/update-revenue', [
        'methods' => 'POST',
        'callback' => 'handle_update_revenue',
        'permission_callback' => '__return_true',
    ]);
});

function handle_update_revenue(WP_REST_Request $request)
{
    $id = $request->get_param('id');
    $revenue = $request->get_param('revenue');
    $channel = $request->get_param('channel');
    $month = $request->get_param('month');

    // Validate post exists
    if (!get_post($id)) {
        return new WP_REST_Response(['error' => 'Invalid post ID'], 404);
    }

    // Check if this channel has any revenue or expense posts
    $has_data = new WP_Query([
        'post_type' => ['channel-expense', 'channel-insight'],
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'channel_name',
                'value' => $channel,
                'compare' => '='
            ]
        ],
        'posts_per_page' => 1,
    ]);

    if (!$has_data->have_posts()) {
        return new WP_REST_Response(['error' => 'Channel has no data to update.'], 403);
    }

    wp_reset_postdata();

    // Update revenue
    $updated = update_post_meta($id, 'revenue_amount', sanitize_text_field($revenue));

    if (!$updated) {
        return new WP_REST_Response(['error' => 'Failed to update revenue'], 500);
    }

    return new WP_REST_Response([
        'success' => true,
        'id' => $id,
        'revenue' => $revenue,
        'channel' => $channel,
        'month' => $month
    ], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('clickable/v1', '/update-expense', [
        'methods' => 'POST',
        'callback' => 'handle_update_expense',
        'permission_callback' => '__return_true',
    ]);
});
function handle_update_expense(WP_REST_Request $request)
{
    $id = $request->get_param('id');
    $expenses = $request->get_param('expenses');
    $channel = $request->get_param('channel');
    $month = $request->get_param('month');

    // Validate post exists
    if (!get_post($id)) {
        return new WP_REST_Response(['error' => 'Invalid post ID'], 404);
    }

    // Check if this channel has any revenue or expense posts
    $has_data = new WP_Query([
        'post_type' => ['channel-expense', 'channel-insight'],
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'channel_name',
                'value' => $channel,
                'compare' => '='
            ]
        ],
        'posts_per_page' => 1,
    ]);

    if (!$has_data->have_posts()) {
        return new WP_REST_Response(['error' => 'Channel has no data to update.'], 403);
    }

    wp_reset_postdata();

    // Update expenses
    $updated = update_post_meta($id, 'amount_expenses', sanitize_text_field($expenses));

    if (!$updated) {
        return new WP_REST_Response(['error' => 'Failed to update expenses'], 500);
    }

    return new WP_REST_Response([
        'success' => true,
        'id' => $id,
        'expenses' => $expenses,
        'channel' => $channel,
        'month' => $month
    ], 200);
}

//invoice at REVENUE page
add_action('rest_api_init', function () {
    register_rest_route('clickable/v1', '/update-invoice', [
        'methods' => 'POST',
        'callback' => 'handle_update_invoice',
        'permission_callback' => '__return_true',
    ]);
});

function handle_update_invoice(WP_REST_Request $request)
{
    $id = $request->get_param('id');
    $invoice = $request->get_param('invoice');
    $channel = $request->get_param('channel');
    $month = $request->get_param('month');

    if (!get_post($id)) {
        return new WP_REST_Response(['error' => 'Invalid post ID'], 404);
    }

    $post = get_post($id);
    if ($post->post_type !== 'invoice-creator') {
        return new WP_REST_Response(['error' => 'Not an invoice post'], 403);
    }

    // Check if invoice is marked as paid
    $status = get_post_meta($id, 'status', true);
    if ($status !== 'paid') {
        return new WP_REST_Response(['error' => 'Only paid invoices can be updated'], 403);
    }

    $updated = update_post_meta($id, 'amount', sanitize_text_field($invoice));

    if (!$updated) {
        return new WP_REST_Response(['error' => 'Failed to update invoice'], 500);
    }

    return new WP_REST_Response([
        'success' => true,
        'id' => $id,
        'invoice' => $invoice,
        'channel' => $channel,
        'month' => $month
    ], 200);
}



// INVOICE Page

add_action('wp_ajax_update_invoice_status', 'update_invoice_status_callback');
add_action('wp_ajax_nopriv_update_invoice_status', 'update_invoice_status_callback');

function update_invoice_status_callback()
{
    $data = json_decode(file_get_contents('php://input'), true);

    // Handle bulk update
    if (is_array($data) && isset($data[0]['id']) && isset($data[0]['status'])) {
        $results = [];

        foreach ($data as $invoice) {
            $invoice_id = intval($invoice['id']);
            $new_status = sanitize_text_field($invoice['status']);
            $updated = update_field('status', $new_status, $invoice_id);

            $results[] = [
                'id' => $invoice_id,
                'status' => $new_status,
                'success' => $updated ? true : false
            ];
        }

        wp_send_json_success([
            'message' => 'Bulk update complete.',
            'results' => $results
        ]);
    }

    // Handle single update as fallback
    if (isset($data['id']) && isset($data['status'])) {
        $invoice_id = intval($data['id']);
        $new_status = sanitize_text_field($data['status']);
        $updated = update_field('status', $new_status, $invoice_id);

        if ($updated) {
            wp_send_json_success(['message' => 'Single invoice updated.']);
        } else {
            wp_send_json_error('Failed to update status.');
        }
    } else {
        wp_send_json_error('Missing invoice ID or status.');
    }

    wp_die();
}
add_action('init', function () {
    if (!session_id()) {
        session_start();
    }
});
// Login Restrictions
add_action('template_redirect', 'login_restrictions');
function login_restrictions()
{
    global $post;



    // Skip if not a real post/page
    if (!$post) {
        return;
    }

    $is_logged_in_user = is_user_logged_in();
    $team_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : null;
    $current_slug = $post->post_name ?? '';

    // Handle logout
    if ($current_slug === 'logout') {
        if ($is_logged_in_user) wp_logout();
        if ($team_id) {
            unset($_SESSION['userID'], $_SESSION['fullName'], $_SESSION['userEmail']);
        }
        wp_redirect(home_url('/'));
        exit;
    }

    // Public access rules
    if (!$is_logged_in_user && !$team_id) {
        $allowed_public_pages = ['login', 'sign-up', 'signup-onboarding', 'lost-password', 'reset-password', 'request-submitted', 'google-login-callback', 'not-allowed'];
        if (!in_array($current_slug, $allowed_public_pages, true) && $current_slug !== '') {
            wp_redirect(home_url('/'));
            exit;
        }
    }
    // Logged-in WP users
    if ($is_logged_in_user) {
        $user = wp_get_current_user();

        // if (in_array('administrator', $user->roles, true)) return;

        if (in_array('administrator', $user->roles, true)) {
            $allowed_pages = ['dashboard', 'team', 'channel-list', 'revenue', 'invoices', 'miscellaneous', 'channels', 'settings', 'not-allowed', 'add-channel', 'setting'];

            $is_single_team = (get_post_type($post) === 'team');
            $is_single_channel = (get_post_type($post) === 'channel');
            $is_single_invoice = (get_post_type($post) === 'invoice-creator');

            if (! in_array($current_slug, $allowed_pages, true) && ! $is_single_team && ! $is_single_channel && ! $is_single_invoice) {
                wp_redirect(home_url('/dashboard'));
                exit;
            }
        }
    }
    // Contractors via $_SESSION
    elseif ($team_id) {

        $status = get_field('contractor_status', $team_id);

        if ($status === 'Archived') {
            // Archived contractors → always go to request-submitted
            if ($current_slug !== 'request-submitted') {
                wp_redirect(home_url('/request-submitted'));
                exit;
            }
        } else {
            // Activated (or any other status)
            if ($current_slug === 'request-submitted') {
                wp_redirect(home_url('/profile/'));
                exit;
            }

            // Don't block admin, AJAX, REST API
            if (is_admin() || wp_doing_ajax() || defined('REST_REQUEST')) {
                return;
            }

            $allowed_pages = ['profile', 'contractor-invoice-creator'];
            if (!in_array($current_slug, $allowed_pages, true)) {
                wp_redirect(home_url('/not-allowed'));
                exit;
            }
        }
    }
}



// CONTRACTOR PROFILE UPDATE/EDIT FUNCTION
add_action('rest_api_init', function () {
    register_rest_route('clickable/v1', '/update-team-profile', [
        'methods' => 'POST',
        'callback' => 'handle_update_team_profile',
        'permission_callback' => '__return_true',
    ]);
});

function handle_update_team_profile(WP_REST_Request $request)
{
    $id = $request->get_param('id');
    $name = $request->get_param('name');
    $discord = $request->get_param('discord');
    $email = $request->get_param('email');
    $full_address = $request->get_param('full_address');
    // $role = $request->get_param('role');
    $paymentPlatform = $request->get_param('paymentPlatform');
    $paymentDetails = $request->get_param('paymentDetails');
    $currency = $request->get_param('currency');
    $payid = $request->get_param('payid');

    $image_id = $request->get_param('image_id');

    // Validate post exists
    $post = get_post($id);
    if (!$post) {
        return new WP_REST_Response(['error' => 'Invalid post ID'], 404);
    }

    // Update ACF fields
    update_post_meta($id, 'fullname', sanitize_text_field($name));
    update_post_meta($id, 'discord_name', sanitize_text_field($discord));
    update_post_meta($id, 'main_email', sanitize_email($email));
    update_post_meta($id, 'full_address', sanitize_text_field($full_address));
    // update_post_meta($id, 'role', sanitize_text_field($role));
    update_post_meta($id, 'payment_platform', sanitize_text_field($paymentPlatform));
    update_post_meta($id, 'payment_details', sanitize_textarea_field($paymentDetails));
    update_post_meta($id, 'currency', sanitize_text_field($currency));
    update_post_meta($id, 'email_pay_id', sanitize_email($payid));

    if ($image_id) {
        $image_data = [
            'ID'          => $image_id,
            'url'         => wp_get_attachment_url($image_id),
            'alt'         => get_post_meta($image_id, '_wp_attachment_image_alt', true),
            'title'       => get_the_title($image_id),
            'description' => wp_get_attachment_caption($image_id),
            'mime_type'   => get_post_mime_type($image_id)
        ];
        update_field('profile_photo', $image_data, $id);
    }

    // Update Post Title if fullname is provided
    // if (!empty($name)) {
    //     wp_update_post([
    //         'ID' => $id,
    //         'post_title' => sanitize_text_field($name),
    //     ]);
    // }

    if (!empty($name)) {
        $sanitized_name = sanitize_text_field($name);
        $new_slug = sanitize_title($sanitized_name);

        wp_update_post([
            'ID' => $id,
            'post_title' => $sanitized_name,
            'post_name' => $new_slug,
        ]);
    }
    return new WP_REST_Response([
        'success' => true,
        'id' => $id,
        'name' => $name,
        'discord' => $discord,
        'email' => $email,
        'full_address' => $full_address,
        // 'role' => $role,
        'paymentPlatform' => $paymentPlatform,
        'currency' => $currency,
        'payid' => $payid,
        'image' => $image_data['url'],
    ], 200);
}


//  Update Photo at profile
add_action('wp_ajax_upload_team_profile_image', 'upload_team_profile_image');
add_action('wp_ajax_nopriv_upload_team_profile_image', 'upload_team_profile_image');
function upload_team_profile_image()
{
    // Check if the file was uploaded

    if (empty($_FILES['file'])) {
        wp_send_json_error(['message' => 'No file uploaded.']);
        return;
    }

    $file = $_FILES['file'];

    // Include WordPress file handling functions
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Ensure a unique filename
    $upload_dir = wp_upload_dir();
    $file['name'] = wp_unique_filename($upload_dir['path'], $file['name']);

    // Handle file upload
    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (isset($upload['error'])) {
        wp_send_json_error(['message' => $upload['error'], 'file' => $file, 'upload' => $upload]);
        return;
    }

    // Insert the uploaded image into the media library
    $attachment = [
        'post_mime_type' => $upload['type'],
        'post_title'     => sanitize_file_name($file['name']),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    $attach_id = wp_insert_attachment($attachment, $upload['file']);
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    $image_url = wp_get_attachment_image_url($attach_id, 'medium');

    wp_send_json_success([
        'url' => $image_url,
        'attach_id' => $attach_id,
    ]);
}

//  Update Photo at profile
add_action('wp_ajax_delete_team_attachment', 'delete_team_attachment');
add_action('wp_ajax_nopriv_delete_team_attachment', 'delete_team_attachment');
function delete_team_attachment()
{

    wp_delete_attachment($_POST['attachment_id'], true);
    wp_send_json_success([
        'attachment_id' => $_POST['attachment_id'],
    ]);
}


add_action('wp_footer', function () {
    global $post;
    if (!$post || $post->post_name != 'signup-onboarding') return;

    $currency = new WP_Query([
        'post_type' => 'team-currency',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    $currency_data = [];
    if ($currency->have_posts()) {
        while ($currency->have_posts()) {
            $currency->the_post();
            $currency_data[] = get_the_title();
        }
    }
    wp_reset_postdata();

    $role = new WP_Query([
        'post_type' => 'team-roles',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    $role_data = [];
    if ($role->have_posts()) {
        while ($role->have_posts()) {
            $role->the_post();
            $role_data[] = get_the_title();
        }
    }
    wp_reset_postdata();
    ?>
    <script>
        jQuery(document).ready(function($) {

            let paymentPlatform = $("select[name='form_fields[payment_platform]']");
            let paymentPlatformSelect = paymentPlatform.val();

            // updatePaymentFieldsVisibility(paymentPlatformSelect);

            // paymentPlatform.on('change', function() {
            //     paymentPlatformSelect = $(this).val();
            //     updatePaymentFieldsVisibility(paymentPlatformSelect);
            // });

            function updatePaymentFieldsVisibility(paymentPlatformSelect) {
                if (paymentPlatformSelect == 'Wise') {
                    showBankDetails();
                    showEmailDetails();
                } else if (paymentPlatformSelect == 'PayPal') {
                    hideBankDetails();
                    showEmailDetails();
                } else if (paymentPlatformSelect == 'Bank') {
                    hideEmailDetails();
                    showBankDetails();
                } else {
                    hideEmailDetails();
                    showBankDetails();
                }
            }

            // Show bank details based on the selected payment platform
            function showBankDetails() {
                // Show bank details based on the selected payment platform
                $("input[name='form_fields[bank_account_name]']").closest('.elementor-field-group').show();
                $("input[name='form_fields[bank_account_number]']").closest('.elementor-field-group').show();
                $("input[name='form_fields[bank_code]']").closest('.elementor-field-group').show();
            }

            function showEmailDetails() {
                // Show email details based on the selected payment platform
                $("input[name='form_fields[paypalwise_email]']").closest('.elementor-field-group').show();
            }
            // Show bank details based on the selected payment platform
            function hideBankDetails() {
                // Show bank details based on the selected payment platform
                $("input[name='form_fields[bank_account_name]']").closest('.elementor-field-group').hide();
                $("input[name='form_fields[bank_account_number]']").closest('.elementor-field-group').hide();
                $("input[name='form_fields[bank_code]']").closest('.elementor-field-group').hide();
            }

            function hideEmailDetails() {
                // Show email details based on the selected payment platform
                $("input[name='form_fields[paypalwise_email]']").closest('.elementor-field-group').hide();
            }

            // Populate the currency select dropdown with available currencies
            $("select[name='form_fields[currency]']").each(function() {
                var $select = $(this);
                var options = <?php echo json_encode($currency_data); ?> || [];

                if (options.length > 0) {
                    $select.empty();
                    options.forEach(function(option) {
                        $select.append(new Option(option, option));
                    });
                } else {
                    $select.append(new Option('No currencies available', ''));
                }
            });

            // Populate the role select dropdown with available currencies
            $("select[name='form_fields[role]']").each(function() {
                var $select = $(this);
                var options = <?php echo json_encode($role_data); ?> || [];

                if (options.length > 0) {
                    $select.empty();
                    options.forEach(function(option) {
                        $select.append(new Option(option, option));
                    });
                } else {
                    $select.append(new Option('No roles available', ''));
                }
            });

        });
    </script>
<?php
});


// Delete Invoice at Single Team
add_action('wp_ajax_delete_invoice_post', 'delete_invoice_post_callback');
add_action('wp_ajax_nopriv_delete_invoice_post', 'delete_invoice_post_callback');

function delete_invoice_post_callback()
{
    // Check permission (logged-in users only)
    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized', 401);
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!$post_id || get_post_type($post_id) !== 'invoice-creator') {
        wp_send_json_error('Invalid post ID', 400);
    }

    // Optional: Add nonce or ownership checks here

    $deleted = wp_delete_post($post_id, true); // true = force delete

    if ($deleted) {
        wp_send_json_success('Invoice deleted.');
    } else {
        wp_send_json_error('Failed to delete invoice.');
    }
}


// Create an invoice for noncontractor
add_action('wp_ajax_save_invoice_creator', 'save_invoice_creator_callback');
add_action('wp_ajax_nopriv_save_invoice_creator', 'save_invoice_creator_callback');

function save_invoice_creator_callback()
{
    if (empty($_POST['tasks_data'])) {
        wp_send_json_error(['message' => 'No tasks data found.']);
    }

    $tasks_data = json_decode(stripslashes($_POST['tasks_data']), true);
    if (!is_array($tasks_data)) {
        wp_send_json_error(['message' => 'Invalid tasks data format.']);
    }

    // Calculate total task amount
    $totalTask = 0;
    foreach ($tasks_data as $task) {
        $hours = isset($task['tasks_hours']) ? floatval($task['tasks_hours']) : 0;
        $rate  = isset($task['tasks_rate']) ? floatval($task['tasks_rate']) : 0;
        $totalTask += $hours * $rate;
    }

    // Create the post
    $post_id = wp_insert_post([
        'post_title'  => sanitize_text_field($_POST['invoice_number']),
        'post_status' => 'publish',
        'post_type'   => 'invoice-creator',
        'meta_input'  => [
            'invoice_number' => sanitize_text_field($_POST['invoice_number']),
            'discord_name'   => sanitize_text_field($_POST['discord_name']),
            'fullname'       => sanitize_text_field($_POST['fullname']),
            'full_address'   => sanitize_text_field($_POST['full_address']),
            'period_covered' => sanitize_text_field($_POST['period_covered']),
            'role'           => sanitize_text_field($_POST['role']),
            'due_date'       => sanitize_text_field($_POST['due_date']),
            'date_submitted' => sanitize_text_field($_POST['date_submitted']),
            'payment_method' => sanitize_text_field($_POST['payment_method']),
            'payment_details' => sanitize_text_field($_POST['payment_details']),
            // 'pay_email'      => sanitize_email($_POST['pay_email']),
            'email'          => sanitize_email($_POST['email']),
            'status'         => sanitize_text_field($_POST['status']),
            'standard'       => sanitize_text_field($_POST['standard']),
            'amount'         => $totalTask, // <-- total now calculated
        ]
    ]);

    if (!$post_id) {
        wp_send_json_error(['message' => 'Failed to save invoice.']);
    }

    // Handle profile photo upload (if any)
    if (! empty($_FILES['file_name']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Attach directly to the new invoice post
        $attachment_id = media_handle_upload('file_name', $post_id);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => 'Image upload failed: ' . $attachment_id->get_error_message()]);
        }

        // Store in ACF (field name must match your field key/slug)
        update_field('file_name', $attachment_id, $post_id);
    }
    // Save tasks_data to ACF
    update_field('your_tasks', $tasks_data, $post_id);

    wp_send_json_success(['message' => 'Invoice saved successfully.']);
}


// Forgot Password


// function handle_lost_password_request()
// {
//     $email = sanitize_email($_POST["email_address"]);

//     if (empty($email) || !is_email($email)) {
//         wp_send_json(["success" => false, "message" => "Please enter a valid email address."]);
//     }

//     $team_member = get_posts([
//         "post_type"  => "team",
//         "meta_query" => [[
//             "key"   => "email_address",
//             "value" => $email,
//             "compare" => "=",
//         ]],
//         "posts_per_page" => 1
//     ]);

//     if (empty($team_member)) {
//         wp_send_json(["success" => false, "message" => "No account found with this email."]);
//     }

//     $team_id = $team_member[0]->ID;

//     // Generate clean token
//     $token = bin2hex(random_bytes(16));

//     // Save token
//     update_post_meta($team_id, "reset_token", $token);
//     update_post_meta($team_id, "reset_expires", time() + 3600);

//     // Build link safely
//     $reset_link = add_query_arg([
//         "token"   => rawurlencode($token)
//         // "team_id" => $team_id
//     ], site_url("/reset-password"));

//     wp_mail(
//         $email,
//         "Password Reset Request",
//         "Hello,\n\nClick below to reset your password:\n\n$reset_link\n\nValid for 1 hour."
//     );

//     wp_send_json(["success" => true, "message" => "Check your email for reset instructions."]);
// }

add_action("wp_ajax_lost_password_request", "handle_lost_password_request");
add_action("wp_ajax_nopriv_lost_password_request", "handle_lost_password_request");
function handle_lost_password_request()
{
    global $wpdb;
    // Sanitize + normalize email
    $email = strtolower(sanitize_email($_POST["email_address"] ?? ''));

    if (empty($email) || !is_email($email)) {
        wp_send_json(["success" => false, "message" => "Please enter a valid email address."]);
    }


    // Find team member by email (case-insensitive)
    $team_member = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID as team_id, p.post_title, pm.meta_value as email
     FROM $wpdb->posts p
     INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
     WHERE pm.meta_key = %s 
       AND pm.meta_value = %s
       AND p.post_type = %s
       AND p.post_status NOT IN ('trash', 'auto-draft') 
     LIMIT 1",
        'email_address',
        $email,
        'team'
    ));


    if (empty($team_member)) {
        wp_send_json(["success" => false, "message" => "No account found with this email."]);
    }

    $team_id = $team_member[0]->team_id;

    // Generate token
    $token_plain = bin2hex(random_bytes(16));
    $token_hash  = wp_hash_password($token_plain); // hash before saving

    // Save token + expiry
    update_post_meta($team_id, "reset_token", $token_hash);
    update_post_meta($team_id, "reset_expires", time() + 3600); // 1 hour validity

    // Build reset link
    $reset_link = add_query_arg([
        "team_id"  => $team_id,
        "token"    => rawurlencode($token_plain),
    ], site_url("/reset-password"));

    // Send HTML email
    add_filter('wp_mail_content_type', fn() => 'text/html');
    wp_mail(
        $email,
        "Password Reset Request",
        "Hello,<br><br>
        Click below to reset your password:<br><br>
        <a href='$reset_link'>$reset_link</a><br><br>
        This link is valid for <strong>1 hour</strong>.<br><br>
        Regards,<br>Your Website"
    );
    remove_filter('wp_mail_content_type', fn() => 'text/html');

    wp_send_json(["success" => true, "message" => "Check your email for reset instructions."]);
}


// Reset Password
add_action("wp_ajax_reset_password", "handle_reset_password");
add_action("wp_ajax_nopriv_reset_password", "handle_reset_password");


function handle_reset_password()
{
    $team_id   = intval($_POST["team_id"] ?? 0);
    $token     = sanitize_text_field($_POST["token"] ?? '');
    $new_pass  = $_POST["new_password"] ?? '';

    if (!$team_id || empty($token) || empty($new_pass)) {
        wp_send_json(["success" => false, "message" => "Invalid request."]);
    }

    // Get stored values
    $token_hash   = get_post_meta($team_id, "reset_token", true);
    $reset_expires = intval(get_post_meta($team_id, "reset_expires", true));

    if (empty($token_hash) || empty($reset_expires)) {
        wp_send_json(["success" => false, "message" => "Invalid or expired reset request."]);
    }

    // Check expiry
    if (time() > $reset_expires) {
        wp_send_json(["success" => false, "message" => "This reset link has expired."]);
    }

    // Validate token
    if (!wp_check_password($token, $token_hash)) {
        wp_send_json(["success" => false, "message" => "Invalid reset token."]);
    }

    // Update password (assuming stored in custom field "password")
    update_post_meta($team_id, "password", $new_pass);

    // Invalidate token
    delete_post_meta($team_id, "reset_token");
    delete_post_meta($team_id, "reset_expires");

    wp_send_json(["success" => true, "message" => "Password successfully reset."]);
}
// function handle_reset_password()
// {
//     $team_id   = intval($_POST["team_id"]);
//     $token     = sanitize_text_field($_POST["token"]);
//     $new_pass  = sanitize_text_field($_POST["new_password"]);

//     $saved_token = get_post_meta($team_id, "reset_token", true);
//     $expires     = get_post_meta($team_id, "reset_expires", true);

//     // Debug logs
//     error_log("RESET DEBUG: team_id = $team_id");
//     error_log("RESET DEBUG: token from URL = " . $token);
//     error_log("RESET DEBUG: saved token = " . $saved_token);
//     error_log("RESET DEBUG: expires at = " . $expires . " | current time = " . time());

//     if ($saved_token != $token) {
//         wp_send_json(["success" => false, "message" => "Token mismatch."]);
//     }

//     if (time() > $expires) {
//         wp_send_json(["success" => false, "message" => "Reset link expired."]);
//     }

//     if (empty($new_pass)) {
//         wp_send_json(["success" => false, "message" => "Password cannot be empty."]);
//     }

//     // Hash password
//     $hashed_pass = wp_hash_password($new_pass);

//     // Save into ACF field "password"
//     update_field("password", $hashed_pass, $team_id);

//     // Clear reset token
//     delete_post_meta($team_id, "reset_token");
//     delete_post_meta($team_id, "reset_expires");

//     wp_send_json(["success" => true, "message" => "Your password has been updated successfully."]);
// }


// Add Expenses at Miscellaneous Page
add_action("wp_ajax_save_channel_insight", "save_channel_insight");
add_action("wp_ajax_nopriv_save_channel_insight", "save_channel_insight");

function save_channel_insight()
{
    check_ajax_referer('wp_rest');

    $invoice_number = sanitize_text_field($_POST['channel_invoice_number']);
    $channel_id     = intval($_POST['tasks_channel']);
    $description    = sanitize_textarea_field($_POST['channel_expenses_description']);
    $amount         = floatval($_POST['channel_expense_amount']);
    $date           = sanitize_text_field($_POST['channel_expense_date']);

    // Default status "Paid" if not set
    $status = isset($_POST['channel_expenses_status']) && $_POST['channel_expenses_status'] !== ''
        ? sanitize_text_field($_POST['channel_expenses_status'])
        : 'PAID';

    // Get channel name
    $channel_name = get_field('channel_name', $channel_id) ?: get_the_title($channel_id);

    // Format date -> Month Year
    // $formatted_date = '';
    // if (!empty($date)) {
    //     $timestamp = strtotime($date);
    //     if ($timestamp) {
    //         $formatted_date = date('F Y', $timestamp); // Example: August 2025
    //     }
    // }

    // Build post title
    // $post_title = $channel_name;
    // if ($formatted_date) {
    //     $post_title .= " - {$formatted_date}";
    // }
    // $post_title .= " - expenses";

    // // Create post in channel-insight
    // $post_id = wp_insert_post([
    //     'post_type'   => 'channel-insight',
    //     'post_status' => 'publish',
    //     'post_title'  => $post_title,
    // ]);

    // Format date
    $formatted_date = '';
    if (!empty($date)) {
        $timestamp = strtotime($date);
        if ($timestamp) {
            $formatted_date = date('F Y', $timestamp);
        }
    }

    $post_title  = $channel_name . ' - ' . $formatted_date;

    $existing = get_page_by_title($post_title, OBJECT, 'channel-insight');

    if ($existing) {
        $post_id = $existing->ID; // already exists
        // wp_send_json_error('You have already added an insight for this month.');
    } else {
        // Create new insight post
        $new_post = [
            'post_type'   => 'channel-insight',
            'post_status' => 'publish',
            'post_title'  => $post_title,
        ];

        $post_id = wp_insert_post($new_post);
    }

    if (is_wp_error($post_id)) {
        wp_send_json_error("Failed to create post");
    }

    // Save fields
    update_field('channel_invoice_number', $invoice_number, $post_id);
    update_field('channel_expenses_description', $description, $post_id);
    update_field('channel_expense_amount', $amount, $post_id);
    update_field('channel_expense_date', $date, $post_id);
    update_field('channel_expenses_status', $status, $post_id);
    update_field('channel_name', $channel_name, $post_id);

    // store new rew insight in repeater
    $new_row = array(
        'channel_expense_date' => $date, // YYYY-MM-DD
        'channel_expenses_description'       => $description,
        'channel_expense_amount' => $amount,
    );

    $field_key = 'your_channel_expenses';

    $tasks_channel_insights = get_field($field_key, $post_id); // may return array or false

    if (! $tasks_channel_insights) {
        // no existing value yet
        update_field($field_key, array($new_row), $post_id);
    } else {
        // ensure it's an array before merging
        if (! is_array($tasks_channel_insights)) {
            $tasks_channel_insights = array();
        }

        // append new data as another row
        $tasks_channel_insights[] = $new_row;

        // save back
        update_field($field_key, $tasks_channel_insights, $post_id);
    }

    wp_send_json_success([
        'message' => 'Saved successfully',
        'post_id' => $post_id
    ]);
}

// Admin edit profile
add_action('wp_ajax_update_user_profile', 'update_user_profile_callback');
function update_user_profile_callback()
{
    // Ensure logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('Not authorized.');
    }

    $user_id = intval($_POST['id']);
    if ($user_id !== get_current_user_id()) {
        wp_send_json_error('You can only edit your own profile.');
    }

    $userdata = [
        'ID'           => $user_id,
        'user_email'   => sanitize_email($_POST['email']),
        'first_name'   => sanitize_text_field($_POST['firstname']),
        'last_name'    => sanitize_text_field($_POST['lastname']),
        'nickname'     => sanitize_text_field($_POST['nickname']),
    ];

    $user_id = wp_update_user($userdata);

    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
    }

    // Handle profile photo upload (if any)
    if (!empty($_FILES['profile_photo']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('profile_photo', 0);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error("Image upload failed.");
        } else {
            update_field('profile_photo', $attachment_id, 'user_' . $user_id); // ACF field
        }
    }

    wp_send_json_success("Profile updated successfully.");
}

add_action('wp_footer', function () {

    if (!is_page('signup-onboarding') && !is_page('contractor-invoice-creator')) return; // Only run on profile page

    $payment_method = new WP_Query([
        'post_type' => 'payment-method',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    $payment_method_data = [];
    if ($payment_method->have_posts()) {
        while ($payment_method->have_posts()) {
            $payment_method->the_post();
            $payment_method_data[] = [
                'id'   => get_the_ID(),
                'name' => get_the_title(),
            ];
        }
    }
    wp_reset_postdata();
?>
    <script type="application/json" id="payment-method-data">
        <?php echo json_encode($payment_method_data); ?>
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const paymentMethods = JSON.parse(
                document.getElementById('payment-method-data').textContent
            );

            // Get all select elements with name='form_fields[payment_method]'
            const selects = document.querySelectorAll(
                "select[name='form_fields[payment_method]'], select[name='form_fields[payment_platform]']"
            );

            const paymentDetails = document.querySelector("[name='form_fields[payment_details]']");


            selects.forEach(function(select) {
                // Clear existing options
                select.innerHTML = "";
                select.add(new Option("Select Payment Method", "", true, true));
                if (paymentMethods.length > 0) {
                    paymentMethods.forEach(function(option) {
                        const opt = new Option(option.name, option.id);
                        select.add(opt);
                    });
                } else {
                    select.add(new Option("No Payment Methods available", ""));
                }
            });



        });
    </script>
<?php
});


// logout link
function my_custom_scripts()
{
    // Make sure Alpine is already loaded before this
    wp_enqueue_script(
        'custom-alpine',
        get_template_directory_uri() . '/assets/js/custom-alpine.js',
        ['alpinejs'], // make Alpine a dependency
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'my_custom_scripts');
