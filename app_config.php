<?php 

	//application details
    $apps[$x]['name'] = "Ringotel";
    $apps[$x]['uuid'] = "f19f8e69-491b-46d0-9b4f-6599b1756e55";
    $apps[$x]['category'] = "";
    $apps[$x]['subcategory'] = "";
    $apps[$x]['version'] = "1.1";
    $apps[$x]['license'] = "Mozilla Public License 1.1";
    $apps[$x]['url'] = "http://www.fusionpbx.com";
    $apps[$x]['description']['en-us'] = "";
    $apps[$x]['description']['en-gb'] = "";
    $apps[$x]['description']['ar-eg'] = "";
    $apps[$x]['description']['de-at'] = "";
    $apps[$x]['description']['de-ch'] = "";
    $apps[$x]['description']['de-de'] = "";
    $apps[$x]['description']['el-gr'] = "";
    $apps[$x]['description']['es-cl'] = "";
    $apps[$x]['description']['es-mx'] = "";
    $apps[$x]['description']['fr-ca'] = "";
    $apps[$x]['description']['fr-fr'] = "";
    $apps[$x]['description']['he-il'] = "";
    $apps[$x]['description']['it-it'] = "";
    $apps[$x]['description']['nl-nl'] = "";
    $apps[$x]['description']['pl-pl'] = "";
    $apps[$x]['description']['pt-br'] = "";
    $apps[$x]['description']['pt-pt'] = "";
    $apps[$x]['description']['ro-ro'] = "";
    $apps[$x]['description']['ru-ru'] = "";
    $apps[$x]['description']['sv-se'] = "";
    $apps[$x]['description']['uk-ua'] = "";

    //permissions
	$y=0;
	$apps[$x]['permissions'][$y]['name'] = "ringotel";
	$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
    $apps[$x]['permissions'][$y]['groups'][] = "admin";
    $y++;
	$apps[$x]['permissions'][$y]['name'] = "ringotel_extensions";
	$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
    $apps[$x]['permissions'][$y]['groups'][] = "admin";
    $y++;
	$apps[$x]['permissions'][$y]['name'] = "ringotel_superadmin";
	$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
	$y++;
	$apps[$x]['permissions'][$y]['name'] = "extension_ringotel";
	$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
	$apps[$x]['permissions'][$y]['groups'][] = "admin";

    //default settings
    $y=0;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "912a2569-a6ca-4048-acab-fd730d3f38d0";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_api";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "https://shell.ringotel.co/api";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "All API calls should be made to the admin portal URL";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "c5940ff8-ff79-4596-b31c-a25a264c52e9";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_integration_api";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "https://shell.ringotel.co/integrations";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Integration API Calls";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "5fe459d9-2876-4bbc-ae81-4d39464f1fc2";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_bandwidth_integration_username";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Bandwidth Username";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "59337d9d-3079-4c95-a006-aa0f46d8cd42";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_bandwidth_integration_password";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Bandwidth Password";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "5b26b6e5-9cb4-484e-addd-09e1aaf4f948";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_bandwidth_integration_account_id";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Bandwidth Account ID";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "a240151d-db89-4a0a-b8b1-c854e66a8491";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_bandwidth_integration_application_id";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Bandwidth Application ID";
    $y++;

    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "e0d6b38e-91aa-40cc-a788-c53e4f14d8f3";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_token";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Authorization Bearer Token";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "1c4891d3-8c20-4315-97f1-9d0650364e5a";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_organization_port";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "5060";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Default Ringotel Account Port";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "042bce58-d22d-41bb-819b-f6478a11c84a";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "ringotel_organization_region";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "2";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Default Ringotel Organization Region";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "613c011c-9750-4128-9dab-806416bc7e4f";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "server_name";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Default Server Name Tag For Organization";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "6a9e92bc-56e1-11f0-8bbe-17e9b34daecd";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "domain_name_postfix";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Default Postfix Domain Name";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "7fa46e42-56e2-11f0-ba39-d793942425bb";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "max_registration";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "1";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Maximum Registration Limit";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "ecc6fe4c-5771-11f0-9e91-efebaa5722da";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "default_connection_protocol";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "sip-tcp";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Default Connection Protocol";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "763bbe72-5774-11f0-90ca-234fcc57bf89";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "organization_default_emailcc";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Default Email CC Address for Organization";
    $y++;
    $apps[$x]['default_settings'][$y]['default_setting_uuid'] = "252088f2-5778-11f0-92e5-ffa249fb014f";
    $apps[$x]['default_settings'][$y]['default_setting_category'] = "ringotel";
    $apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "default_connection_regexpires";
    $apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
    $apps[$x]['default_settings'][$y]['default_setting_value'] = "120";
    $apps[$x]['default_settings'][$y]['default_setting_enabled'] = "true";
    $apps[$x]['default_settings'][$y]['default_setting_description'] = "Set Default Connection Registration Expires";
    $y++;
    
?>