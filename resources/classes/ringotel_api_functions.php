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

class ringotel_api_functions {

	/**
	 *
	 * @var type
	 */
	public $config;

	/**
	 *
	 * @var type
	 */
	public $domain_name_postfix;

	/**
	 *
	 * @var type
	 */
	public $default_connection_regexpires;

	/**
	 * Ringotel curl request object
	 * @var curl_ringotel
	 */
	private $curl;

	/**
	 * Ringotel ringotel middleware manager class
	 * @var ringotel_middleware_manager
	 */
	private $ringotel_middleware_manager;

	public function __construct(settings $settings, string $base_url, ?ringotel_middleware_manager $ringotel_middleware_manager = null, ?curl_ringotel $curl = null) {
		$this->domain_name_postfix = '-' . $settings->get('ringotel', 'domain_name_postfix', 'ringotel');
		$this->default_connection_regexpires = intval($settings->get('ringotel', 'default_connection_regexpires', 120));
		$this->baseUrl = $base_url;
		$this->settings = $settings;

		$token = $this->settings->get('ringotel', 'ringotel_token', null);
		if (empty($token)) throw new invalid_token_exception("Token is not valid");

		if ($curl !== null) {
			$this->curl = $curl;
		} else {
			$this->curl = new curl_ringotel();
		}

		if ($curl !== null) {
			$this->ringotel_middleware_manager = $ringotel_middleware_manager;
		} else {
			$this->ringotel_middleware_manager = new ringotel_middleware_manager();
		}

		// With the new curl object, we could now use the following:
		$this->curl->add_header('Authorization', "Bearer $token")
					->set_base_url($base_url)
					->set_content_type_json()
		;

		// Add middleware for middleware manager
        $this->ringotel_middleware_manager->addMiddleware(new ringotel_error_middleware());
        $this->ringotel_middleware_manager->addMiddleware(new ringotel_logging_middleware());
        $this->ringotel_middleware_manager->addMiddleware(new ringotel_response_mapper_middleware()); // it's necessery =)
	}


	/**
	 *
	 * @return array JSON decoded string to an associative array
	 */
	public function get_organization() {
		$parameters = array(
            "method" => "getOrganizations",
            "params" => new stdClass()
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}


	/**
	 * after grant code
	 * @param array $param
	 * @return array JSON decoded string to an associative array
	 */
	public function create_organization(array $param) {
		$name = explode(".", $_SESSION['domain_name'])[0];
		$parameters = array(
			"method" => "createOrganization",
			"params" => array(
				"name" => $_SESSION['domain_name'] ?? '',
				"region" => $param['region'],
				"domain" => isset($param['domain']) ? $param['domain'] : explode(".", $name)[0] . $this->domain_name_postfix,
			)
		);
		// adminlogin [optional]
		if (isset($param['adminlogin'])) {
			$parameters['adminlogin'] = $param['adminlogin'];
		}
		// adminpassw [optional]
		if (isset($param['adminpassw'])) {
			$parameters['adminpassw'] = $param['adminpassw'];
		}

		$curl = new curl_ringotel();
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Delete Organization
	 * @param array $param
	 */
	public function delete_organization(array $param) {
		$parameters = array(
			"method" => "deleteOrganization",
			"params" => array("id" => $param['id'])
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Get Connections List
	 * @param array $param
	 */
	public function get_branches(array $param) {
		$parameters = array(
			"method" => "getBranches",
			"params" => array(
				"orgid" => $param['orgid']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * after grant code
	 * @param array $param
	 */
	public function create_branch(array $param) {
		$parameters = array(
			"method" => "createBranch",
			"params" => array(
				"orgid" => $param["orgid"],
				"name" => $param["name"],
				"address" => $param["address"],
				"country" => "US",
				"number" => "",
				"provision" => array(
					"displayname" => "",
					"protocol" => $param['protocol'],
					"noverify" => true,
					"nosrtp" => false,
					"internal" => false,
					"maxregs" => intval($param['maxregs']),
					"extvc" => false,
					"private" => false,
					"multitenant" => false,
					"dtmfmode" => "rfc2833",
					"regexpires" => $this->default_connection_regexpires,
					"codecs" => array(
						0 => array(
							"codec" => "G.711 Alaw",
							"frame" => 20
						),
						1 => array(
							"codec" => "G.711 Ulaw",
							"frame" => 20
						)
					),
					"plength" => 32,
					"tones" => array(
						"Progress" => "Progress 1",
						"Ringback2" => "Ringback 1",
						"Ringback" => "United States"
					),
					"username" => "",
					"authname" => "",
					"authpass" => "",
					"ipcheck" => false,
					"iptable" => array(
						0 => array(
							"net" => "",
							"mask" => ""
						)
					),
					"inboundFormat" => "",
					"noreg" => false,
					"withReg" => false,
					"sms" => 3,
					"blfs" => [],
					"subscription" => new stdClass()
				)
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * DELETE CONNECTION
	 * @param array $param
	 */
	public function delete_branch(array $param) {
		$parameters = array(
			"method" => "deleteBranch",
			"params" => array(
				"id" => $param['id'],
				"orgid" => $param['orgid']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * GET USERS
	 * @param array $param
	 */
	public function get_users($param) {
		$parameters = array(
			"method" => "getUsers",
			"params" => array(
				"branchid" => $param['branchid'],
				"orgid" => $param['orgid']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * GET USERS
	 * @param array $param
	 */
	public function create_users($param) {
		$parameters = array(
			"method" => "createUsers",
			"params" => array(
				"branchid" => $param['branchid'],
				"orgid" => $param['orgid'],
				"users" => $param['users']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Delete Organization
	 * @param array $param
	 */
	public function delete_user($param) {
		$parameters = array(
			"method" => "deleteUser",
			"params" => array(
				"id" => $param['id'],
				"orgid" => $param['orgid']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * GET USERS
	 * @param array $params
	 */
	public function reset_user_password($params) {
		$parameters = array(
			"method" => "resetUserPassword",
			"params" => $params
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * GET USERS
	 * @param array $params
	 * @return string
	 */
	public function update_user($params) {
		$parameters = array(
			"method" => "updateUser",
			"params" => $params
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Detach User
	 * @param array $params
	 */
	public function detach_user($params) {
		$parameters = array(
			"method" => "detachUser",
			"params" => $params
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 *
	 * @param type $params
	 * @return type
	 */
	public function update_branch($params) {
		$parameters = array(
			"method" => "updateBranch",
			"params" => $params
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 *
	 * @param type $params
	 * @return type
	 */
	public function update_branch_with_default_options_after_switcher($params) {
		$parameters = array(
			"method" => "updateBranch",
			"params" => array(
				"id" => $params['id'],
				"orgid" => $params['orgid'],
				"provision" => array(
					"maxregs" => $params['maxregs']
				)
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 *
	 * @param type $params
	 * @return type
	 */
	public function update_organization($params) {
		$parameters = array(
			"method" => "updateOrganization",
			"params" => $params
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 *
	 * @param type $params
	 * @return type
	 */
	public function deactivate_user($params) {
		$parameters = array(
			"method" => "deactivateUser",
			"params" => $params
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/////
	////
	///
	// INTEGRATION

	/**
	 * create Integration
	 * @param array $param
	 * @return type
	 */
	public function create_integration(array $param) {
		$parameters = array(
			'serviceid' => 'Bandwidth',
			'state' => 1,
			'profileid' => $param['profileid'],
			'Username' => $param['Username'],
			'Password' => $param['Password'],
			'Account ID' => $param['Account_ID'],
			'Application ID' => $param['Application_ID'],
			'options' => '',
			'redirect' => 'https://shell.ringotel.co/account/en-US/#/org/' . $param['profileid'] . '/integrations/Bandwidth'
		);
		return $this->ringotel_middleware_manager->handle($this->curl->get($this->baseUrl, $parameters));
	}

	/**
	 * delete Integration
	 * @param array $param
	 * @return type
	 */
	public function delete_integration(array $param) {
		$parameters = array(
			'serviceid' => 'Bandwidth',
			'state' => 0,
			'profileid' => $param['profileid'],
			'Username' => $param['Username'],
			'Password' => $param['Password'],
			'Account ID' => $param['Account_ID'],
			'Application ID' => $param['Application_ID'],
			'options' => '',
			'redirect' => 'https://shell.ringotel.co/account/en-US/#/org/' . $param['profileid'] . '/integrations/Bandwidth'
		);
		return $this->ringotel_middleware_manager->handle($this->curl->get($this->baseUrl, $parameters));
	}

	/**
	 * get Integration
	 * @param array $param
	 * @return type
	 */
	public function get_services(array $param) {
		$parameters = array(
			"method" => "getServices",
			"params" => array(
				"orgid" => $param['orgid']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Get Numbers Configuration
	 * @param array $param
	 * @return type
	 */
	public function get_sms_trunk(array $param) {
		$parameters = array(
			"method" => "getSMSTrunks",
			"params" => array(
				"orgid" => $param['orgid']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Create Numbers Configuration
	 * @param array $param
	 * @return type
	 */
	public function create_sms_trunk(array $param) {
		$parameters = array(
			"method" => "createSMSTrunk",
			"params" => array(
				"orgid" => $param['orgid'],
				"name" => $param['name'],
				"number" => '+1' . $param['number'],
				"service" => "Bandwidth",
				"users" => $param['users'],
				"sessionTimeout" => 0,
				"country" => "US",
				"groupmode" => true,
				"outboundFormat" => "e164",
				"inboundFormat" => "national",
				"optout" => array(
					"keyword" => "STOP",
					"autoreply" => "You have been removed from our list and will no longer receive messages. To resubscribe, send any message back."
				),
				"autoreply" => array(
					0 => array(
						"key" => "HELP",
						"value" => "Send STOP to stop receiving messages from us. To resubscribe, send any message back."
					)
				)
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Update Numbers Configuration
	 * @param array $param
	 * @return type
	 */
	public function update_sms_trunk(array $param) {
		$parameters = array(
			"method" => "updateSMSTrunk",
			"params" => array(
				"orgid" => $param['orgid'],
				"country" => "US",
				"inboundFormat" => "national",
				"groupmode" => true,
				"created" => floor(microtime(true) * 1000),
				"outboundFormat" => "e164",
				"users" => $param['users'],
				"autoreply" => array(
					0 => array(
						"value" => "Send STOP to stop receiving messages from us. To resubscribe, send any message back.",
						"key" => "HELP"
					)
				),
				"optout" => array(
					"autoreply" => "You have been removed from our list and will no longer receive messages. To resubscribe, send any message back.",
					"keyword" => "STOP"
				),
				"number" => '+1' . $param['number'],
				"service" => "Bandwidth",
				"name" => $param['name'],
				"sessionTimeout" => 0,
				"id" => $param['id'],
				"status" => 2
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}

	/**
	 * Delete Numbers Configuration
	 * @param array $param
	 * @return type
	 */
	public function delete_sms_trunk(array $param) {
		$parameters = array(
			"method" => "deleteSMSTrunk",
			"params" => array(
				"orgid" => $param['orgid'],
				"id" => $param['id']
			)
		);
		return $this->ringotel_middleware_manager->handle($this->curl->post($this->baseUrl, $parameters));
	}
}