<?php

class ringotel_repository {

	private $db;

	function __construct() {
		$this->db = database::new();
	}

	function get_ringotel_token() {
		$parameters = null;
		$sql = "   select default_setting_value from v_default_settings    ";
		$sql .= "   where default_setting_category = 'ringotel'             ";
		$sql .= "   and default_setting_subcategory = 'ringotel_token'      ";
		//return info with destinations
		$res = $this->db->select($sql, $parameters, 'column');
		return $res;
	}

	function get_ringotel_api_url() {
		$parameters = null;
		$sql = "   select default_setting_value from v_default_settings    ";
		$sql .= "   where default_setting_category = 'ringotel'             ";
		$sql .= "   and default_setting_subcategory = 'ringotel_api'      ";
		//return info with destinations
		$res = $this->db->select($sql, $parameters, 'column');
		return $res;
	}
}
