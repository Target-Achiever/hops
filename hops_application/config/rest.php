<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| REST Message Field Name
|--------------------------------------------------------------------------
|
| The field name for the message inside the response
|
*/
$config['rest_data_field_name'] = 'server_data';
/*
|--------------------------------------------------------------------------
| REST keys table Name
|--------------------------------------------------------------------------
|
| The table name for keys table
|
*/
$config['rest_keys_table'] = 'hs_keys';
/*
|--------------------------------------------------------------------------
| REST keys enable
|--------------------------------------------------------------------------
|
| The boolean value to check whether the key is enabled or not
|
*/
$config['rest_enable_keys'] = TRUE;
/*
|--------------------------------------------------------------------------
| REST key field name
|--------------------------------------------------------------------------
|
| The field name for the api-key
|
*/
$config['rest_key_column'] = 'api_key';
/*
|--------------------------------------------------------------------------
| Auth key table name
|--------------------------------------------------------------------------
|
| Table name for auth key
|
*/
$config['auth_keys_table'] = 'hs_user_device_logs';
/*
|--------------------------------------------------------------------------
| Session key field name
|--------------------------------------------------------------------------
|
| Initialize key name to store session keyvalue and session token
|
*/
$config['auth_key_column'] = 'unique_id';
/*
|--------------------------------------------------------------------------
| Key initialize
|--------------------------------------------------------------------------
|
| Initialize key name to store api keyvalue and session token
|
*/
$config['rest_key_name'] = 'API-KEY';
$config['auth_key_name'] = 'SESSION-KEY';
/*
|--------------------------------------------------------------------------
| Rest Language
|--------------------------------------------------------------------------
|
| Rest Language - english
|
*/
$config['rest_language'] = 'english';
/*
|--------------------------------------------------------------------------
| Controller Name
|--------------------------------------------------------------------------
|
| Controller name which controller doesn't contain session validation
|
*/
$config['ignore_session_controller'] = 'user';
