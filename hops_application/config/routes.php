<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = 'welcome/page_not_found';
$route['translate_uri_dashes'] = FALSE;


/*  ===============--------------     REST API START   -----------------======================== */

// User controller
$route['registration'] = 'api/user/index'; // post method
$route['otp_verify'] = 'api/user/otp_verification'; // post method
$route['resend_otp'] = 'api/user/resend_otp'; // get method
$route['login'] = 'api/user/login'; // post method
$route['social_media_login'] = 'api/user/social_media_login'; // post method
$route['forgot_password'] = 'api/user/forgot_password'; // get method
$route['reset_password'] = 'api/user/reset_password'; // post method
$route['logout'] = 'api/user/logout'; // post method

// Profile controller
$route['profile_view'] = 'api/profile/profile_view'; // get method
$route['profile_update'] = 'api/profile/profile_update'; // post method
$route['assign_role'] = 'api/profile/assign_role'; // get method
$route['location_update'] = 'api/profile/location_update'; // post method
$route['add_card'] = 'api/profile/card_details_save'; // post method
$route['saved_cards'] = 'api/profile/saved_cards_list'; // get method
$route['delete_card'] = 'api/profile/card_details_delete'; // get method
$route['profile_otp_verify'] = 'api/profile/profile_otp_verification'; // get method
$route['profile_resend_otp'] = 'api/profile/profile_resend_otp'; // get method

// Document verification controller
$route['doc_status'] = 'api/document_verification/index'; // get method
$route['doc_submit'] = 'api/document_verification/document_submit'; // post method

// Settings controller
$route['settings_view'] = 'api/settings/settings_view'; // get method
$route['settings_update'] = 'api/settings/settings_update'; // post method
$route['change_password'] = 'api/settings/change_password'; // post method

// Package controller
$route['package_cost'] = 'api/package/package_cost'; // get method
$route['order_submit'] = 'api/package/order_submit'; // post method
$route['orders'] = 'api/package/orders_list'; // get method
$route['order_accept'] = 'api/package/accept_orders'; // post method

// Notification controller
$route['notification_list'] = 'api/notification/notification_list'; // get method
$route['notification_view'] = 'api/notification/notification_view'; // get method



$route['test_orders'] = 'api/package/order_submit_details'; // post method


/*  ===============--------------     REST API END   -----------------======================== */


/*  ===============--------------     WEBSITE START   -----------------======================== */


$route['contact_us_test'] = 'ajax/contact_us_test'; // post method

// Ajax routes
$route['contact_us'] = 'ajax/contact_us'; // post method
$route['get_package_cost'] = 'ajax/get_package_cost'; // get method






/*  ===============--------------     WEBSITE END   -----------------======================== */