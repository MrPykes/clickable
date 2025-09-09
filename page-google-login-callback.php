<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// require_once __DIR__ . '/vendor/autoload.php';

// $client = new Google_Client();
// $client->setClientId(GOOGLE_CLIENT_ID);
// $client->setClientSecret(GOOGLE_CLIENT_SECRET);
// $client->setRedirectUri(GOOGLE_CLIENT_REDIRECTURI);
// // $client->setState(wp_create_nonce('google_oauth'));

// if (empty($_GET['code'])) {
//     wp_die('Missing "code" parameter from Google OAuth callback.');
// }
// $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

// if (isset($token['error'])) {
//     wp_die(__('Google OAuth error: ') . esc_html($token['error_description'] ?? $token['error']));
// }

// $client->setAccessToken($token);

// $oauth2 = new Google_Service_Oauth2($client);
// $googleUser = $oauth2->userinfo->get();

// // Find or create user in WP
// $user_exists = get_posts([
//     'post_type'  => 'team',
//     'post_status' => 'publish',
//     'meta_query' => [
//         [
//             'key'     => 'email_address',
//             'value'   => $googleUser->email,
//             'compare' => '='
//         ]
//     ],
//     'posts_per_page' => 1,
//     'fields' => 'ids'
// ]);

// if (!$user_exists) {
//     $user_id = wp_insert_post([
//         'post_type'    => 'team',
//         'post_title'   => sanitize_text_field($googleUser->name),
//         'post_status'  => 'publish',
//         'meta_input'   => [
//             'email_address'    => sanitize_email($googleUser->email),
//             'fullname'         => sanitize_text_field($googleUser->name),
//             'profile_picture'  => esc_url_raw($googleUser->picture),
//             'team_status'      => 'Contractor'
//         ]
//     ]);
// } else {

//     $user_id = $user_exists[0];
// }
// $full_name = get_field('fullname', $user_id);
// $email = get_field('email_address', $user_id);

// $_SESSION['userID'] = $user_id;
// $_SESSION['fullName'] = $full_name;
// $_SESSION['userEmail'] = $email;

// $redirect_url = home_url('/profile/');

// wp_redirect($redirect_url); // Change as needed
// exit;
