<?php
/**
 *
 * @name Answer_model
 * @desc null
 *
 * @author	 liule1
 * @date 2015年8月4日
 *
 * @copyright (c) 2015 SINA Inc. All rights reserved.
 */
class Version_swift_model extends MY_Model {
	private $_cache_key_pre = '';
	private $_cache_expire = 3600;
	protected $_table = 'gl_version_swift_config';

	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":version_swift:";
	}
	// ---------------------------------------------------------------------------------- //
	// ---------------------------------------------------------------------------------- //
	public function getInfoByPostfix($postfix) {
		$postfix = !$postfix ? '.RaidersQA' : ".$postfix";

		$cache_key = $this->_cache_key_pre . 'getGidByPostfix2:' . "$postfix";

		$data = $this->cache->redis->get($cache_key);
		$data && $data = json_decode($data, true);
		if ($data === false) {
			$data = $this->getInfoByPostfixFromDB($postfix);
			if (empty($data)) {
				$data = $this->getInfoByPostfixFromConfig($postfix);
			}
			$this->cache->redis->set($cache_key, json_encode($data), $this->_cache_expire);
		}

		return $data;
	}

	private function getInfoByPostfixFromDB($postfix) {
		$ret = array();
		$postfix = $this->escape($postfix);
		$sql = "SELECT * FROM {$this->_table} WHERE safe_package_id REGEXP '$postfix$' LIMIT 1";
		$ret = $this->common_model->get_one_data_by_sql($sql);
		return $ret;
	}
	private function getInfoByPostfixFromConfig($postfix) {
		$data = array(
			array(
				'safe_package_id' => 'com.sina.RaidersQA',
				'url' => 'https://itunes.apple.com/app/id1048841352?mt=8',
			),
			array(
				'safe_package_id' => 'com.sina.RaidersQA.blct',
				'url' => 'https://itunes.apple.com/app/id1048841352?mt=blct',
			),
			array(
				'safe_package_id' => 'com.sina.RaidersQA.lqyxz',
				'url' => 'https://itunes.apple.com/app/id1048841352?mt=lqyxz',
			),
		);
		$pattern = '/' . preg_quote($postfix) . '$' . '/';
		foreach ($data as $v) {
			if (preg_match($pattern, $v['safe_package_id'])) {
				return $v;
			}
		}
		return false;
	}
	// ---------------------------------------------------------------------------------- //

}
