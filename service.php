<?php
/*
    Ringotel Integration for FusionPBX
    Version: 1.0

    The contents of this file are subject to the Mozilla Public License Version
    1.1 (the "License"); you may not use this file except in compliance with
    the License. You may obtain a copy of the License at
    http://www.mozilla.org/MPL/

    Software distributed under the License is distributed on an "AS IS" basis,
    WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
    for the specific language governing rights and limitations under the
    License.

    The Initial Developer of the Original Code is
    Vladimir Vladimirov <w@metastability.ai>
    Portions created by the Initial Developer are Copyright (C) 2022-2025
    the Initial Developer. All Rights Reserved.

    Contributor(s):
    Vladimir Vladimirov <w@metastability.ai>

    The Initial Developer of the Original Code is
    Mark J Crane <markjcrane@fusionpbx.com>
    Portions created by the Initial Developer are Copyright (C) 2008-2025
    the Initial Developer. All Rights Reserved.

    Contributor(s):
    Mark J Crane <markjcrane@fusionpbx.com>
*/

// load framework files
require_once dirname(__DIR__, 2) . '/resources/require.php';

// check auth
require_once dirname(__DIR__, 2) . '/resources/check_auth.php';

// check permissions
if (permission_exists('ringotel')) {
    //access granted
} else {
    echo "access denied";
    exit;
}

// func lib
function sanitizeInput($value) {
    if (is_array($value)) {
        return array_map('sanitizeInput', $value);
    } else {
        return htmlspecialchars(strip_tags($value));
    }
}

// Function for define and sanitize QUERY-params
// ...simple adding the others method for sanitazing
$queryParams = [];

if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        $queryParams[$key] = sanitizeInput($value);
    }
}
if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        $queryParams[$key] = sanitizeInput($value);
    }
}

if (!permission_exists('ringotel') || empty($queryParams['method'])) {
    die();
}
// Extract the requested method
$object_method = $queryParams['method'];
$valid_methods = [
    'get_organization', 'create_organization', 'delete_organization',
    'get_branches', 'create_branch', 'delete_branch',
    'get_users', 'create_users', 'delete_user', 'update_user',
    'resync_names', 'resync_password', 'detach_user', 'users_state',
    'update_branch_with_default_settings', 'update_branch_with_updated_settings',
    'update_organization_with_default_settings', 'update_parks_with_updated_settings',
    'activate_user', 'deactivate_user', 'reset_user_password', 'switch_organization_mode',
    'create_integration', 'delete_integration', 'get_integration',
    'get_sms_trunk', 'create_sms_trunk', 'update_sms_trunk', 'delete_sms_trunk'
];

$integration = in_array($object_method, $valid_methods) ? 'INTEGRATION' : null;

if (!empty($integration)) {

    $settings = new settings(["domain_uuid" => $_SESSION['domain_uuid'], "user_uuid" => $_SESSION['user_uuid']]);
    $ringotel_api_url = $settings->get('ringotel', 'ringotel_api', $integration);
    $ringotel_api_functions = new ringotel_api_functions($settings, $ringotel_api_url, null, null);
    $ringotel = new ringotel($settings, $ringotel_api_functions);
    if (method_exists($ringotel, $object_method)) {
        $output_format_converter = new ringotel_response_output_converter();
        echo $output_format_converter->associative_array_to_json($ringotel->{$object_method}($queryParams));
    } else {
        // method is not provided
        exit();
    }

}