<?php
if (! defined('ABSPATH')) exit; // Security check

// Load Google Client library
add_action('init', 'load_google_client');
function load_google_client()
{
    static $loaded = false;
    if ($loaded) return;

    $autoload = get_stylesheet_directory() . '/vendor/autoload.php';
    if (file_exists($autoload) && is_readable($autoload)) {
        require_once $autoload;
        $loaded = true;
    } else {
        error_log('Google Client autoload file not found or not readable: ' . $autoload);
    }
};

add_shortcode('google_login_button', 'render_google_login_button_shortcode');
function render_google_login_button_shortcode()
{
    load_google_client();

    if (!class_exists('Google_Client')) {
        echo is_readable(__DIR__ . '/vendor/autoload.php');
        return 'Google Client not loaded';
    }

    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_CLIENT_REDIRECTURI);
    $client->setState(wp_create_nonce('google_oauth'));
    $client->addScope(['email', 'profile']);

    return '<a href="' . esc_url($client->createAuthUrl())  . '">Continue with Google</a>';
};


add_action('template_redirect', function () {
    if (! is_page('google-login-callback')) return;

    load_google_client();

    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_CLIENT_REDIRECTURI);
    $client->setState(wp_create_nonce('google_oauth'));

    // Verify state nonce
    if (empty($_GET['state']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['state'])), 'google_oauth')) {
        wp_die('Invalid OAuth state.');
    }

    $auth_code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    if (empty($auth_code)) {
        wp_die('Missing code parameter.');
    }
    $token = $client->fetchAccessTokenWithAuthCode($auth_code);

    if (isset($token['error'])) {
        wp_die(__('Google OAuth error: ') . esc_html($token['error_description'] ?? $token['error']));
    }

    $client->setAccessToken($token);

    $oauth2 = new Google_Service_Oauth2($client);
    $googleUser = $oauth2->userinfo->get();

    // Find or create user in WP
    $user_exists = get_posts([
        'post_type'  => 'team',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key'     => 'email_address',
                'value'   => $googleUser->email,
                'compare' => '='
            ]
        ],
        'posts_per_page' => 1,
        'fields' => 'ids'
    ]);

    $redirect_url = home_url('/profile/');

    if (!$user_exists) {
        $user_id = wp_insert_post([
            'post_type'    => 'team',
            'post_title'   => sanitize_text_field($googleUser->name),
            'post_status'  => 'publish',
        ]);

        update_field('fullname', sanitize_text_field($googleUser->name), $user_id);
        update_field('email_address', sanitize_email($googleUser->email), $user_id);
        // update_field('password', '', $user_id);
        update_field('team_status', 'CONTRACTOR', $user_id);
        update_field('contractor_status', 'Archived', $user_id);

        send_email_notification_after_signup($user_id, $googleUser->name, $googleUser->email);

        // // Force wp_mail to send HTML emails
        // add_filter('wp_mail_content_type', function () {
        //     return 'text/html';
        // });

        // // Build dynamic edit link
        // $post_id    = $user_id;
        // $admin_url  = get_admin_url(null, 'post.php');
        // $edit_link  = add_query_arg([
        //     'post'   => $post_id,
        //     'action' => 'edit'
        // ], $admin_url);


        // // Notify WordPress admin
        // $admin_email = 'ed@opt.co.nz';
        // // $subject     = 'New Google Signup on ' . get_bloginfo('name');
        // $subject     = 'New Google Signup on Clickable';
        // $message     = sprintf(
        //     "Hello Admin,\n\nA new user has signed up via Google:\n\nName: %s\nEmail: %s\n\nYou can review them here: %s\n\nRegards,\nYour Website",
        //     sanitize_text_field($googleUser->name),
        //     sanitize_email($googleUser->email),
        //     esc_url($edit_link)
        // );

        // wp_mail($admin_email, $subject, $message);


        // // Remove the filter so it doesn’t affect other emails
        // remove_filter('wp_mail_content_type', 'set_html_content_type');

        $redirect_url = home_url('/signup-onboarding/');
        // exit;
    } else {

        $user_id = $user_exists[0];
        $redirect_url = home_url('/profile/');
    }
    $full_name = get_field('fullname', $user_id);
    $email = get_field('email_address', $user_id);

    $_SESSION['userID'] = $user_id;
    $_SESSION['fullName'] = $full_name;
    $_SESSION['userEmail'] = $email;

    wp_redirect($redirect_url); // Change as needed
    exit;
});

// add_action('template_redirect', function () {});

add_action('wp_footer', function () {

    if (! is_page('contractor-invoice-creator')) return;
    if (! isset($_SESSION['userID'])) return;

    $paymentPlatformID = get_field('payment_platform', $_SESSION['userID']) ?: '';
    $paymentDetails = get_field('payment_details', $_SESSION['userID']) ?: '';
    $emailPayID = get_field('email_pay_id', $_SESSION['userID']) ?: '';

    $paymentInformation = [
        'paymentPlatformID' => is_array($paymentPlatformID) ? $paymentPlatformID[0] : $paymentPlatformID,
        'paymentDetails'    => $paymentDetails,
        'emailPayID'        => $emailPayID
    ];

    echo '<script id="user-data-script">window.userData = ' . json_encode($paymentInformation) . ';</script>';
?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chkPaymentInfo = document.getElementById("form-field-current_payment_information-0");
            const selectPaymentPlatform = document.querySelector("select[name='form_fields[payment_method]']");
            const textareaPaymentDetails = document.querySelector("textarea[name='form_fields[payment_details]']");
            const inputEmailPayID = document.querySelector("input[name='form_fields[email]']");

            if (!chkPaymentInfo) return;

            chkPaymentInfo.addEventListener("change", function() {
                if (this.checked) {
                    // ✅ Select correct option
                    if (selectPaymentPlatform) {
                        selectPaymentPlatform.value = window.userData.paymentPlatformID;
                        selectPaymentPlatform.dispatchEvent(new Event("change", {
                            bubbles: true
                        }));
                        selectPaymentPlatform.setAttribute("disabled", "disabled");
                    }

                    // ✅ Clean HTML -> plain text with line breaks
                    if (textareaPaymentDetails) {
                        let tempDiv = document.createElement("div");
                        tempDiv.innerHTML = window.userData.paymentDetails;

                        // Replace <br> with line breaks
                        tempDiv.querySelectorAll("br").forEach(br => br.replaceWith("\n"));

                        // Extract text
                        let cleanText = tempDiv.textContent.trim();

                        // Append to textarea with line break if not empty
                        textareaPaymentDetails.value += (textareaPaymentDetails.value ? "\n" : "") + cleanText;
                        textareaPaymentDetails.setAttribute("disabled", "disabled");
                    }

                    // ✅ Fill email text field
                    if (inputEmailPayID) {
                        inputEmailPayID.value = window.userData.emailPayID;
                        inputEmailPayID.setAttribute("disabled", "disabled");
                    }
                } else {
                    // optional: clear values when unchecked
                    if (selectPaymentPlatform) selectPaymentPlatform.value = "";
                    if (textareaPaymentDetails) textareaPaymentDetails.value = "";
                    if (inputEmailPayID) inputEmailPayID.value = "";
                    inputEmailPayID.removeAttribute("disabled");
                    textareaPaymentDetails.removeAttribute("disabled");
                    selectPaymentPlatform.removeAttribute("disabled");
                }
            });

        });
    </script>
<?php
});


function send_email_notification_after_signup($user_id, $name, $email)
{
    // Force wp_mail to send HTML emails
    add_filter('wp_mail_content_type', function () {
        return 'text/html';
    });

    // Build dynamic edit link
    $post_id    = $user_id;
    $admin_url  = get_admin_url(null, 'post.php');
    $edit_link  = add_query_arg([
        'post'   => $post_id,
        'action' => 'edit'
    ], $admin_url);


    // Notify WordPress admin
    $admin_email = 'ed@opt.co.nz';
    // $subject     = 'New Google Signup on ' . get_bloginfo('name');
    $subject     = 'New Google Signup on Clickable';
    $message     = sprintf(
        "Hello Admin,\n\nA new user has signed up via Google:\n\nName: %s\nEmail: %s\n\nYou can review them here: %s\n\nRegards,\nYour Website",
        sanitize_text_field($name),
        sanitize_email($email),
        esc_url($edit_link)
    );

    wp_mail($admin_email, $subject, $message);


    // Remove the filter so it doesn’t affect other emails
    remove_filter('wp_mail_content_type', 'set_html_content_type');
}
