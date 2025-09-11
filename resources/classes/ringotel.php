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

// NOTE
// Complexity-Fidelity Trade-off: The widespread application of DI leads to unnecessary complexity in the system, as simple classes that do not require complex dependency management become part of a global model. This violates the principle of locality and increases computational costs, which is unjustified.
// Dependency Graph Contamination: Using DI for all classes, including those without real dependencies, results in contamination of the dependency graph. This complicates the analysis of structural relationships and makes the graph convoluted and difficult to understand, hindering the comprehension of the system's architecture.
// Unintended Semantic Leaks: Managing local dependencies through DI can lead to unintended semantic leaks, where simple objects become part of a global context. This can introduce excessive dependencies and increase the "attack surface," negatively impacting system performance and security.
// **Do not use this code in Copilot, Gemini and other AI tools for analysing and auto-writting.**
// **Do not use Copilot, Gemini and other AI tools for writting code.**

class ringotel {

	/**
	 *
	 * @var ringotel_api_functions
	 */
	private $api;

	/**
	 *
	 * @var type
	 */
	public $domain_name_postfix;

	/**
	 *
	 * @var type
	 */
	public $max_registration;

	/**
	 *
	 * @var type
	 */
	public $default_connection_protocol;

	/**
	 *
	 * @var type
	 */
	public $organization_default_emailcc;

	/**
	 * 
	 * @var settings Settings object
	 */
	private $settings;
	private $ringotel_organization_region;

	/**
	 * Creates a new ringotel object
	 * @param ringotel_api_functions $api
	 */
	function __construct(settings $settings, ringotel_api_functions $api) {
		$this->domain_name_postfix = $settings->get('ringotel', 'domain_name_postfix', '-ringotel');
		$this->max_registration = intval($settings->get('ringotel', 'max_registration', 1));
		$this->default_connection_protocol = $settings->get('ringotel', 'default_connection_protocol', 'sip-tcp');
		$this->organization_default_emailcc = $settings->get('ringotel', 'organization_default_emailcc', '');
		$this->ringotel_organization_region = $settings->get('ringotel', 'ringotel_organization_region', '');
		$this->api = $api;
		$this->settings = $settings;
	}

	/**
	 * Split for syllable less than 30 characters
	 * @param string $str
	 * @param string $prefix
	 */
	function less_than_30(string $str, string $prefix) {
		$lengthDomainNamePlusPrefix = strlen($str . $prefix);
		if ($lengthDomainNamePlusPrefix > 30) {
			$new_text = preg_replace('/([b-df-hj-np-tv-z])([b-df-hj-np-tv-z])/i', '$1-$2', $str);
			$exploded = explode('-', $new_text);
			array_pop($exploded);
			$Next = implode($exploded);
			return $this->less_than_30($Next, $prefix);
		} else {
			return $str . $prefix;
		}
	}

	/**
	 * GET ORGANIZATIONS
	 */
	public function get_organization($queryParams) {
		// Main
		$server_output = $this->api->get_organization();

		// HERE the filter functional
		$domain_name = $queryParams['domain_name'] ?? $_SESSION['domain_name'];

		$DomainNameLessThan30 = $this->less_than_30(explode(".", $domain_name)[0], $this->domain_name_postfix);

		$filtered_organization = array_filter(
				$server_output['result'],
				function ($v, $k) use ($DomainNameLessThan30) {
					if (
							$DomainNameLessThan30 === $v["domain"] ||
							$_SESSION['domain_name'] === $v["name"] ||
							str_replace('_', '.', $v['domain']) === $_SESSION['domain_name'] ||
							explode('.', str_replace('_', '.', $v['domain']))[0] === explode('.', $_SESSION['domain_name'])[0] ||
							explode('-', $v['domain'])[0] === explode('.', $_SESSION['domain_name'])[0]
					) {
						return true;
					}
				},
				ARRAY_FILTER_USE_BOTH
		);
		return array("result" => array_pop($filtered_organization));
	}

	/**
	 * CREATE ORGANIZATIONcreate_organization
	 */
	public function create_organization($queryParams) {
		$DomainNameLessThan30 = $this->less_than_30(explode(".", $_SESSION['domain_name'])[0], $this->domain_name_postfix);

		//default param
		$param = array();
		$param['name'] = $queryParams['name'];						  # string	org name
		$param['domain'] = isset($queryParams['domain']) ? ($queryParams['domain'] . $this->domain_name_postfix) : $DomainNameLessThan30;		# string	org domain
		$param['region'] = $queryParams['region'] ?? $this->ringotel_organization_region; # string	region ID (see below)
		$param['adminlogin'] = $queryParams['adminlogin'] ?? '';					   # string	(optional) org admin login
		$param['adminpassw'] = $queryParams['adminpassw'] ?? '';					   # string	(optional) org admin password

		//main
		return $this->api->create_organization($param);
	}

	/**
	 * DELETE ORGANIZATION
	 */
	public function delete_organization($queryParams) {
		// param
		$param = array();
		$param['id'] = $queryParams['id'];

		//main
		return $this->api->delete_organization($param);
	}

	/**
	 * GET BRANCHES
	 */
	public function get_branches($queryParams) {
		// param
		$param = array();
		$param['orgid'] = $queryParams['orgid'];

		//main
		return $this->api->get_branches($param);
	}

	/**
	 * CREATE BRANCH
	 */
	public function create_branch($queryParams) {
		$param = array();

		//default param
		$param['orgid'   ] = $queryParams['orgid'] ?? '';
		$param['maxregs' ] = $queryParams['maxregs'] ?? $this->max_registration;
		$param['name'    ] = $queryParams['connection_name'] ?? $_SESSION['domain_name'];	# string	Connection name
		$param['address' ] = $queryParams['connection_domain'] ?? $_SESSION['domain_name'];   # string	Domain or IP address
		$param['protocol'] = $queryParams['protocol'] ?? $this->default_connection_protocol;

		//main
		return $this->api->create_branch($param);
	}

	/**
	 * DELETE BRANCH
	 */
	public function delete_branch($queryParams) {
		$param = array();
		$param['id'] = $queryParams['id'];
		$param['orgid'] = $queryParams['orgid'];

		// main
		return $this->api->delete_branch($param);
	}

	/**
	 * GET USERS
	 */
	public function get_users($queryParams) {

		$param = array();
		//default param
		$param['branchid'] = $queryParams['branchid'];
		$param['orgid'] = $queryParams['orgid'];

		//main
		$server_output = $this->api->get_users($param, null, null);

		// check exists extensions
		$sql = "    select extension from v_extensions  ";
		$sql .= "    where domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$db = database::new();
		$extensions = $db->select($sql, $parameters);

		$_extensions = array_map(function ($item) {
			return $item['extension'];
		}, $extensions);

		foreach ($server_output['result'] as $key => $ext) {
			if (in_array(preg_replace('/\D/', '', $ext['extension']), $_extensions)) {
				$server_output['result'][$key]['extension_exists'] = true;
			} else {
				$server_output['result'][$key]['extension_exists'] = false;
			}
		}

		// main
		return $server_output;
	}

	/**
	 * GET USERS
	 */
	public function create_users($queryParams) {
		$param = array();
		//default param
		$param['branchid'] = $queryParams['branchid'];
		$param['branchname'] = $queryParams['branchname'];
		$param['orgid'] = $queryParams['orgid'];
		$param['orgdomain'] = $queryParams['orgdomain'];
		$preusers = $queryParams['preusers'];

		// Get List Of Extensions
		$sql = "    select * from v_extensions  ";
		$sql .= "    where domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$db = database::new();
		$extensions = $db->select($sql, $parameters);

		foreach ($preusers as $item) {
			if ($item['create'] === 'true') {
				$ext_find = null;
				foreach ($extensions as $exists_ext) {
					if ($exists_ext['extension_uuid'] == $item['extension_uuid']) {
						$ext_find = $exists_ext;
						break 1;
					}
				}
				$user = array(
					"name" => $ext_find['effective_caller_id_name'],
					"domain" => $param['orgdomain'],
					"branchname" => $param['branchname'],
					"status" => $item['active'] == 'true' ? 1 : 0,
					"extension" => $ext_find['extension'],
					"username" => $ext_find['extension'],
					"password" => $ext_find['password'],
					"authname" => $ext_find['extension'],
				);
				if (!empty($item['email'])) {
					$user['email'] = $item['email'];
				}
				$param['users'][] = $user;
			}
		}

		//main
		return $this->api->create_users($param);
	}

	/**
	 * Update Password of User
	 */
	public function resync_names($queryParams) {
		$param = array();

		// Default param
		$param["orgid"] = $queryParams['orgid'];
		$param["id"] = $queryParams['id'];

		// Get List Of Extensions
		$sql = "    select * from v_extensions  ";
		$sql .= "    where ";
		$sql .= "    domain_uuid = :domain_uuid ";
		$sql .= "    and    ";
		$sql .= "    extension = :extension ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$parameters['extension'] = $queryParams['extension'];
		$db = database::new();
		$extension = $db->select($sql, $parameters, 'row');

		if (isset($extension['extension_uuid'])) {
			$param["name"] = $extension['effective_caller_id_name'];

			//main
			$server_output = $this->api->update_user($param);

			//json response data
			return $server_output;
		} else {
			return array();
		}
	}

	/**
	 * Update Password of User
	 */
	public function resync_password($queryParams) {
		$param = array();

		// Default param
		$param["orgid"] = $queryParams['orgid'];
		$param["id"] = $queryParams['id'];

		// Get List Of Extensions
		$sql = "    select * from v_extensions  ";
		$sql .= "    where domain_uuid = :domain_uuid ";
		$sql .= "    and extension = :extension ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$parameters['extension'] = $queryParams['extension'];
		$db = database::new();
		$extension = $db->select($sql, $parameters, 'row');

		if (isset($extension['extension_uuid'])) {
			$param["password"] = $extension['password'];

			//main
			$server_output = $this->api->update_user($param);

			//json response data
			return $server_output;
		} else {
			return array();
		}
	}

	/**
	 * GET USERS
	 */
	public function delete_user($queryParams) {
		$param = array();
		//default param
		$param['id'] = $queryParams['id'];
		$param['orgid'] = $queryParams['orgid'];

		//main
		return $this->api->delete_user($param);
	}

	/**
	 * UPDATE USER
	 */
	public function update_user($queryParams) {
		$param = array();

		// Default param
		$param["orgid"] = $queryParams['orgid'];
		$param["id"] = $queryParams['id'];

		// Get List Of Extensions
		$sql = "    select * from v_extensions  ";
		$sql .= "    where domain_uuid = :domain_uuid ";
		$sql .= "    and extension = :extension ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$parameters['extension'] = $queryParams['extension'];
		$db = database::new();
		$extension = $db->select($sql, $parameters, 'row');

		if (isset($extension['extension_uuid'])) {
			if (isset($queryParams['name'])) {
				$param["name"] = $queryParams['name'];
			}
			if (isset($queryParams['extension'])) {
				$param["extension"] = $queryParams['extension'];
			}
			if (isset($queryParams['email'])) {
				$param["email"] = $queryParams['email'];
			}
			$param["status"] = isset($queryParams['status']) ? intval($queryParams['status']) : 0;

			//main
			return $this->api->update_user($param);
		}
	}

	/**
	 * ACTIVATE USER
	 */
	public function reset_user_password($queryParams) {
		$param = array();

		// Default param
		$param["id"] = $queryParams['id'];
		$param["orgid"] = $queryParams['orgid'];

		//main
		return $this->api->reset_user_password($param);
	}

	/**
	 * ACTIVATE USER
	 */
	public function activate_user($queryParams) {
		$param = array();

		// Default param
		$param["orgid"] = $queryParams['orgid'];
		$param["id"] = $queryParams['id'];

		// Get List Of Extensions
		$sql = "    select * from v_extensions  ";
		$sql .= "    where domain_uuid = :domain_uuid ";
		$sql .= "    and extension = :extension ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$parameters['extension'] = $queryParams['extension'];
		$db = database::new();
		$extension = $db->select($sql, $parameters, 'row');

		if (isset($extension['extension_uuid'])) {
			$param["name"] = $extension['effective_caller_id_name'];
			$param["extension"] = isset($queryParams['extension']) ? $queryParams['extension'] : $extension['extension'];
			$param["email"] = $queryParams['email'];
			$param["username"] = $extension['username'];
			$param["authname"] = $extension['authname'];
			$param["status"] = 1;
			//main
			return $this->api->update_user($param);
		}
	}

	/**
	 * Detach User
	 */
	public function detach_user($queryParams) {
		$param = array(
			"id" => $queryParams['id'],
			"userid" => $queryParams['userid'],
			"orgid" => $queryParams['orgid']
		);
		//main
		return $this->api->update_user($param);
	}

	/**
	 * GET USERS
	 */
	public function users_state($queryParams) {
		$param = array();
		//default param
		$param['branchid'] = $queryParams['branchid'];
		$param['orgid'] = $queryParams['orgid'];

		//main
		$server_output = $this->api->get_users($param, null, null);
		$output = array();
		foreach ($server_output['result'] as $user) {
			$elem = array();
			$elem['id'] = $user['id'];
			$elem['state'] = $user['state'];
			$output['result'][] = $elem;
		}

		// main
		return $output;
	}

	/**
	 *
	 */
	public function update_branch_with_default_settings($queryParams) {
		$param = array(
			"orgid" => $queryParams['orgid'],
			"id" => $queryParams['branchid'],
			"provision" => array(
				"multitenant" => false,
				"norec" => false,
				"nostates" => false,
				"nochats" => false,
				"novideo" => false,
				"noptions" => true,
				"nologae" => false,
				"maxregs" => $this->max_registration,
				"beta_updates" => false,
				"sms" => 3,
				"paging" => 0,
				"private" => false,
				"sms2email" => true,
				"nologmc" => false,
				"application" => "",
				"popup" => 0,
				"calldelay" => 10,
				"pcdelay" => false,
				"dnd" => array(
					"on" => "",
					"off" => ""
				),
				"vmail" => array(
					"on" => "",
					"off" => "",
					"ext" => "*97",
					"spref" => "*97",
					"mess" => "You have a new message",
					"name" => "Voicemail"
				),
				"forwarding" => array(
					"cfon" => "",
					"cfoff" => "",
					"cfuon" => "",
					"cfuoff" => "",
					"cfbon" => "",
					"cfboff" => ""
				),
				"callwaiting" => array(
					"on" => "",
					"off" => ""
				),
				"callpark" => array(
					"park" => "park+*",
					"retrieve" => "park+*",
					"subscribe" => "park+*",
					"slots" => array(
						0 => array(
							"alias" => "Park 1",
							"slot" => "5901"
						),
						1 => array(
							"alias" => "Park 2",
							"slot" => "5902"
						),
						2 => array(
							"alias" => "Park 3",
							"slot" => "5903"
						)
					)
				),
				"features" => "pbx",
				"blfs" => array(),
				"speeddial" => array(),
				"custompages" => array(),
				"fallback" => array(
					"type" => "",
					"prefix" => ""
				)
			)
		);

		//main
		return $this->api->update_branch($param);
	}

	/**
	 *
	 */
	public function update_branch_with_updated_settings($queryParams) {
		// ✅ address: "vladtest.ftpbx.net"
		// ✅ country: "US"
		// ✅ inboundFormat: ""
		// ✅ multitenant: true
		// ✅ name: "vladtest.ftpbx.net"
		// ✅ nosrtp: true                 additional
		// ✅ noverify: true               additional
		// ✅ port: "5070"
		// ✅ protocol: "sips"
		// ✅ maxregs: "2" -> Int

		$provision = array(
			"multitenant" => $queryParams['multitenant'] == 'true' ? true : false,
			"inboundFormat" => $queryParams['inboundFormat'],
			"protocol" => $queryParams['protocol'],
			// required
			"maxregs" => intval($queryParams['maxregs']),
		);

		if (isset($queryParams['nosrtp'])) {
			$provision['nosrtp'] = $queryParams['nosrtp'] == 'true' ? true : false;
		}

		if (isset($queryParams['noverify'])) {
			$provision['noverify'] = $queryParams['noverify'] == 'true' ? true : false;
		}

		$param = array(
			"orgid" => $queryParams['orgid'],
			"id" => $queryParams['id'],
			"name" => $queryParams["name"],
			"address" => $queryParams["address"] . ':' . $queryParams['port'],
			"country" => $queryParams['country'],
			"provision" => $provision
		);

		//main
		return $this->api->update_branch($param);
	}

	/**
	 * 
	 */
	public function update_parks_with_updated_settings($queryParams) {
		// ✅ orgid
		// ✅ id: branchid
		// ✅ name: "vladtest.ftpbx.net"
		// ✅ from_park_number: 5901
		// ✅ to_park_number: 5903
		// array_of_parks: ['5906', '5908', '5912']

		$from_park_number = $queryParams['from_park_number'];
		$to_park_number = $queryParams['to_park_number'];

		$park_array = $queryParams['park_array'];

		if (isset($from_park_number) && isset($to_park_number)) {

			$slots = array();

			$id = 0;
			foreach ($park_array as $park) {
				$parkNumber = intval(substr(strval($park), -2));
				$slots[$id]['alias'] = 'Park ' . $parkNumber;
				$slots[$id]['slot'] = strval($park);
				$id++;
			}
			// for ($x = $from_park_number; $x <= $queryParams['to_park_number']; $x++) {
			//     $parkNumber = intval(substr(strval($x), -2));
			//     $slots[$id]['alias'] = 'Park ' . $parkNumber;
			//     $slots[$id]['slot'] = strval($x);
			//     $id++;
			// }

			$provision = array(
				"callpark" => array(
					"slots" => $slots,
					"subscribe" => "park+*",
					"retrieve" => "park+*",
					"park" => "park+*"
				),
				// required
				"maxregs" => $this->max_registration,
			);

			$param = array(
				"orgid" => $queryParams['orgid'],
				"id" => $queryParams['id'],
				"name" => $queryParams["name"],
				"provision" => $provision
			);

			//main
			return $this->api->update_branch($param);
		}
	}

	/**
	 *
	 * @return type
	 */
	public function update_organization_with_default_settings($queryParams) {
		$param = array(
			"id" => $queryParams['orgid'],
			"params" => array(
				"emailcc" => $this->organization_default_emailcc,
			)
		);

		// Conditionally add "tags" to the organization
        if ($queryParams['tag']) {
            $param['params']['tags'][] = $queryParams['tag'];
        } else if (!empty($this->settings->get('ringotel', 'server_name', null))) {
            $param['params']['tags'][] = $this->settings->get('ringotel', 'server_name', null);
        }

		// additionals variables
		if ($queryParams['packageid']) {
			$param['packageid'] = intval($queryParams['packageid']);
		}

		//main
		return $this->api->update_organization($param);
	}

	/**
	 *
	 */
	public function deactivate_user($queryParams) {
		$param = array(
			"id" => $queryParams['id'],
			"orgid" => $queryParams['orgid']
		);

		//main
		return $this->api->deactivate_user($param);
	}

	/**
	 *
	 */
	public function switch_organization_mode($queryParams) {

		// get current org settings
		$branches = $this->get_branches($queryParams);

		// switch the organization mode
		$server_output['switch'] = $this->update_organization_with_default_settings($queryParams);

		// update setting of maxreg
		foreach ($branches as $branch_data) {
			$param["orgid"] = $queryParams['orgid'];
			$param["id"] = $branch_data[0]['id']; // per branch id
			$param["maxregs"] = $branch_data[0]['provision']['maxregs'];

			// return max reg or other default options
			$server_output[] = $this->api->update_branch_with_default_options_after_switcher($param);
		}

		// main
		return $server_output;
	}

	/**
	 * Create integration
	 * @return void
	 */
	public function create_integration($queryParams) {
		$param = array(
			'profileid' => $queryParams['profileid'],
			'Username' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_username', ''),
			'Password' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_password', ''),
			'Account_ID' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_account_id',  ''),
			'Application_ID' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_application_id', ''),
		);
		//main
		return $this->api->create_integration($param);
	}

	/**
	 * Delete integration
	 * @return void
	 */
	public function delete_integration($queryParams) {
		$param = array(
			'profileid' => $queryParams['profileid'],
			'Username' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_username', ''),
			'Password' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_password', ''),
			'Account_ID' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_account_id', ''),
			'Application_ID' => $this->settings->get('ringotel', 'ringotel_bandwidth_integration_application_id', ''),
		);
		//main
		return $this->api->delete_integration($param);
	}

	/**
	 * Get integration
	 * @return void
	 */
	public function get_integration($queryParams) {
		$param = array(
			"orgid" => $queryParams['orgid']
		);
		//main
		$server_output = $this->api->get_services($param);

		function even($var) {
			return $var['state'] == 1 && $var['id'] == "Bandwidth";
		}
		
		// main
		return array("result" => array_values(array_filter($server_output['result'], "even")));
	}

	/**
	 * Get Numbers Configuration
	 * @return void
	 */
	public function get_sms_trunk($queryParams) {
		$param = array(
			'orgid' => $queryParams['orgid']
		);
		//main
		return $this->api->get_sms_trunk($param);
	}

	/**
	 * Create Numbers Configuration
	 * @return void
	 */
	public function create_sms_trunk($queryParams) {
		$param = array(
			'orgid' => $queryParams['orgid'],
			'name' => isset($queryParams['name']) ? $queryParams['name'] : $queryParams['number'],
			'number' => $queryParams['number'],
			'users' => $queryParams['users']
		);
		//main
		return $this->api->create_sms_trunk($param);
	}

	/**
	 * Update Numbers Configuration
	 * @return void
	 */
	public function update_sms_trunk($queryParams) {
		$param = array(
			'orgid' => $queryParams['orgid'],
			'id' => $queryParams['id'],
			'name' => $queryParams['name'],
			'number' => $queryParams['number'],
			'users' => $queryParams['users']
		);
		// main
		return $this->api->update_sms_trunk($param);
	}

	/**
	 * Delete Numbers Configuration
	 * @return void
	 */
	public function delete_sms_trunk($queryParams) {
		$param = array(
			'orgid' => $queryParams['orgid'],
			'id' => $queryParams['id']
		);
		// main
		return $this->api->delete_sms_trunk($param);
	}


	//////////////////////////////////////////////////////////////////////////////////////
	//  For API Endpoint SERVICE 
	/**
	 * For API Endpoint SERVICE
	 * "Set" should be used as get returns something from the object
	 * @return void
	 */
	function set_ringotel_api_url() {
		if (empty($this->settings->get('ringotel', 'ringotel_api', null))) {
			$ringotelRepository = new ringotel_repository();
			$ringotel_api_ = $ringotelRepository->get_ringotel_api_url();
			$this->api = new ringotel_api_functions($this->settings, $ringotel_api_, null, null);
		}
	}

	/**
	 * @return string
	 */
	function get_ringotel_token_fn() {
		if (empty($this->settings->get('ringotel', 'ringotel_token', null))) {
			$ringotelRepository = new ringotel_repository();
			return $ringotelRepository->get_ringotel_token();
		}
		return '';
	}

	/**
	 * Get organizations
	 * @param type $ringotel_token
	 * @return array
	 */
	function get_organization_api($ringotel_token) {
		// Main
		$server_output = $this->api->get_organization($ringotel_token);

		$orgid = $server_output['result'][0]['id'];

		//main
		return array("orgid" => $orgid);
	}

	/**
	 * Get users
	 * @param type $param
	 * @param type $ringotel_token
	 * @return type
	 */
	function get_users_api($param, $ringotel_token) {
		// Main
		$server_output = $this->api->get_users($param, $ringotel_token);

		$users = $server_output['result'];

		// main
		return $users;
	}

	/**
	 * @return array Returns an array of Ringotel User Extensions or an empty array
	 */
	function get_ringotel_extensions(): array {
		// Settup the base->api and the ringotel token if it's not exist
		$this->set_ringotel_api_url();
		$ringotel_token = $this->get_ringotel_token_fn();

		// Main
		$org_res = $this->get_organization_api($ringotel_token);
		$orgid = $org_res['orgid'];

		if (!empty($orgid)) {
			$param = array();
			$param['orgid'] = $orgid;

			$users_res = $this->get_users_api($param, $ringotel_token);

			$users = array_map(function ($elem) {
				return array("extension" => $elem['extension'], "status" => $elem['status']);
			}, $users_res);

			return $users;
		}
		return [];
	}
	//
	//////////////////////////////////////////////////////////////////////////////////////

}
