<?php
/**
* @author guotao1, <guotao1@staff.sina.com.cn>
* @version : MY_Model.php 2013-3-5 ����04:08:30 guotao1 
* @copyright  (c) 2012 Sina PAY Team.
*/

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * MY_Router Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		ExpressionEngine Dev Team
 * @category	Libraries
 * @link		http://codeigniter.com/user_guide/general/routing.html
 */
class MY_Model extends CI_Model
{
	
	/**
	 * Table name, with prefix and main name
	 *
	 * @var string
	 */
	protected $_table;

	/**
	 * Primary key
	 *
	 * @var string
	 */
	protected $_pk = 'id';

	/**
	 * Error
	 *
	 * @var mixed string | array
	 */
	protected $_error;

	/**
	 * Validate rules
	 *
	 * @var array
	 */
	protected $_validate = array();
	
	public function __construct()
	{
		parent::__construct();	
		$this->load->library('page');	
		$this->load->library('global_func');
		$this->load->library('func');
	}
	
	/**
	 * Find result
	 *
	 * @param array $conditions
	 * @return array
	 */
	public function find($conditions = array())
	{
		if (empty($conditions['where'])) unset($conditions['where']);
		if (isset($conditions['where']) && is_array($conditions['where']))
			$conditions['where'] = $this->where($conditions['where'], false);
		
		if (is_string($conditions))
			$conditions = array('where' => $conditions);
		if (!array_key_exists('table', $conditions))
		{
			$conditions += array('table' => $this->_table);
		}
	
		$conditions = $conditions + array(
			'fields' => '*',
			'where' => 1,
			'group' => null,
			'order' => null,
			'start' => -1,
			'limit' => -1
		);	
		extract($conditions);

		$sql = "select {$fields} from $table where $where";
		if ($group)
			$sql .= " group by {$group}";

		if ($order)
			$sql .= " order by {$order}";

		if (0 <= $start && 0 <= $limit)
			$sql .= " limit {$start}, {$limit}";

		
		return $sql;

	}
	
	/**
	 * translate for table
	 * 
	 * @param array $data
	 * @return \Model
	 */
	protected function _map(array &$data)
	{
		if (is_array($this->_map) && count($this->_map)) {
			foreach ($this->_map as $name => $field) {
				if (isset($data[$name])) {
					$data[$field] = $data[$name];
					unset($data[$name]);
				}
			}
		}
		
		return $this;
	}

	
	/**
	 * Set table Name
	 *
	 * @param string $table
	 */
	public function table($table = null)
	{
		if (!is_null($table)) {
			$this->_table = $table;
			return $this;
		}

		return $this->_table;
	}

	/**
	 * Update
	 *
	 * @param int $id
	 * @param array $data
	 * @return string
	 */
	public function update($id, $data,$table)
	{
		if (is_array($id)) {
			$where = $this->where($id, false);
		} else {
			$where = $this->_pk . '=' . (is_int($id) ? $id : "'$id'");
		}
		
		if (!isset($table) || empty($table))
		{
			$table = $this->_table;
		}
		
		
		$tmp = array();	
		foreach ($data as $key => $value) {
			$tmp[] = "`$key`='" . $this->escape($value) . "'";
		}	
		$str = implode(',', $tmp);	
		$sql = "update $table set " . $str . " where $where";
		return $sql;

	}
	
	/**
	 * Insert
	 *
	 * @param array $data
	 * @param string $table
	 * @return boolean
	 */
	public function insert($data, $table = null)
	{
		if (null == $table)
			$table = $this->_table;
		
		$keys = '';
		$values = '';
		foreach ($data as $key => $value) {
			$keys .= "`$key`,";
			$values .= "'" . $this->escape($value) . "',";
		}
		$sql = "insert into $table (" . substr($keys, 0, -1) . ") values (" . substr($values, 0, -1) . ");";
		return $sql;

	}
	
	
	/**
	 * Escape string
	 *
	 * @param string $str
	 * @return string
	 */
	public function escape($str)
	{
		return $this->security->ids_clean($str);
// 		return mysql_escape_string($str);
	}
	
	/**
	 * Load data
	 *
	 * @param int $id
	 * @return array
	 */
	public function load($id, $col = null)
	{
		if (is_null($col))
			$col = $this->_pk;
		$sql = "select * from {$this->_table} where {$col} = " . (is_int($id) ? $id : "'$id'");

		return $sql;
	}
	
	/**
	 * make where condition
	 * 
	 * @param type $where
	 * @param type $chain
	 * @return type
	 */
	public function where($where = array(), $chain = true)
	{
		return $this->parseWhere($where);

		// TODO
		//return $chain ? $this : $this->_where;
	}
	
	
	/**
	 * Delete
	 *
	 * @param string $where
	 * @param string $table
	 * @return boolean
	 */
	public function delete($id, $col = null,$table=null)
	{
		if (is_array($id)) {
			$where = $this->where($id);
		} else {
			if (is_null($col))
				$col = $this->_pk;

			$where = $col . '=' . (is_int($id) ? $id : "'$id'");
		}

		if (!isset($table) || empty($table))
		{
			$table = $this->_table;
		}
		$result  = "delete from $table where $where";
		return $result;
	
	}
	
	/**
	 * parse where condition
	 * 
	 * @param type $where
	 * @return null
	 */
	final protected function parseWhere($where)
	{
		if (empty($where)) {
			return null;
		}
		
		$whereStr = '';
		if (!is_array($where)) {
			// string
			$whereStr .= (string) $where;
		} else {
			// array
			$operate = isset($where['_logic']) ? strtoupper($where['_logic']) : '';
			$operate = sprintf(" %s ", (in_array($operate, array('AND', 'OR', 'XOR')) ? $operate : 'AND'));
			foreach ($where as $key => $val) {
				$whereStr .= '( ';
				if (!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/', trim($key))) {
					die('error_sql_condition: ' . $key);
				}
				// 多条件支持
				$multi = is_array($val) && isset($val['_multi']);
				$key = trim($key);
				if (strpos($key, '|')) {
					// 支持 name|title|nickname 方式定义查询字段, OR 查询
					$array = explode('|', $key);
					$str = array();
					foreach ($array as $m => $k) {
						$v = $multi ? $val[$m] : $val;
						$str[] = '(' . $this->parseWhereItem($this->parseKey($k), $v) . ')';
					}
					$whereStr .= implode(' OR ', $str);
				} elseif (strpos($key, '&')) {
					// 支持 name&title&nickname 方式定义查询字段, AND 查询
					$array = explode('&', $key);
					$str = array();
					foreach ($array as $m => $k) {
						$v = $multi ? $val[$m] : $val;
						$str[] = '(' . $this->parseWhereItem($this->parseKey($k), $v) . ')';
					}
					$whereStr .= implode(' AND ', $str);
				} else {
					$whereStr .= $this->parseWhereItem($this->parseKey($key), $val);
				}
				$whereStr .= ' )' . $operate;
			}
			$whereStr = substr($whereStr, 0, -strlen($operate));
		}
		
		return empty($whereStr) ? '' : $whereStr;
	}
	
	
	/**
	 * parse where item
	 * 
	 * @param type $key
	 * @param type $val
	 * @return string
	 */
	final protected function parseWhereItem($key, $val)
	{
		$comparison = array(
			'=' => '=', '<>' => '<>', '>' => '>', '>=' => '>=','<' => '<', '<=' => '<=',	// add by liule1 2015.5.21
			'eq' => '=', 'neq' => '<>', 'gt' => '>', 'egt' => '>=','lt' => '<', 'elt' => '<=',
			'notlike' => 'NOT LIKE','like' => 'LIKE', 'in' => 'IN', 'notin' => 'NOT IN'
		);
		$whereStr = '';
		if (is_array($val)) {
			if (is_string($val[0])) {
// 				if (preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i', $val[0])) {	// modify by liule1 2015.5.21
				if (preg_match('/^(=|<>|>|>=|<|<=|EQ|NEQ|GT|EGT|LT|ELT)$/i', $val[0])) {
					// 比较运算
					$whereStr .= $key . ' ' . $comparison[strtolower($val[0])] . ' ' . $this->parseValue($val[1]);
				} elseif (preg_match('/^(NOTLIKE|LIKE)$/i', $val[0])) {
					// 模糊查找
					if (is_array($val[1])) {
						$likeLogic = isset($val[2]) ? strtoupper($val[2]) : 'OR';
						if (in_array($likeLogic, array('AND', 'OR', 'XOR'))) {
							$likeStr = $comparison[strtolower($val[0])];
							$like = array();
							foreach ($val[1] as $item) {
								$like[] = $key . ' ' . $likeStr . ' ' . $this->parseValue($item);
							}
							$whereStr .= '(' . implode(' ' . $likeLogic . ' ', $like) . ')';
						}
					} else {
						$whereStr .= $key . ' ' . $comparison[strtolower($val[0])] . ' ' . $this->parseValue($val[1]);
					}
				} elseif ('exp' == strtolower($val[0])) {
					// 使用表达式
					$whereStr .= ' (' . $key . ' ' . $val[1] . ') ';
				} elseif (preg_match('/IN/i', $val[0])) {
					// IN 运算
					if (isset($val[2]) && 'exp' == $val[2]) {
						$whereStr .= $key . ' ' . strtoupper($val[0]) . ' ' . $val[1];
					} else {
						if (is_string($val[1])) {
							$val[1] = explode(',', $val[1]);
						}
						$zone = implode(',', $this->parseValue($val[1]));
						$whereStr .= $key . ' ' . strtoupper($val[0]) . ' (' . $zone . ')';
					}
				} elseif (preg_match('/BETWEEN/i', $val[0])) { // BETWEEN运算
					$data = is_string($val[1]) ? explode(',', $val[1]) : $val[1];
					$whereStr .= ' (' . $key . ' ' . strtoupper($val[0]) . ' ' . $this->parseValue($data[0]) . ' AND ' . $this->parseValue($data[1]) . ' )';
				} else {
					die('error_condition_sql: ' . $val[0]);
				}
			} else {
				/**
				 * 支持
				 * $where['name'] = array(
				 *		array('in', '1,2,3,4'),
				 *		array('like', '%sina%'),
				 *		'and',
				 * );
				 * 即 WHERE ( (id IN '1,2,3,4,5') AND (id LIKE '%sina%') )
				 */
				$count = count($val);
				$rule = isset($val[$count - 1]) && is_string($val[$count - 1]) ? strtoupper($val[$count - 1]) : '';
				if (in_array($rule, array('AND', 'OR', 'XOR'))) {
					$count = $count - 1;
				} else {
					$rule = 'AND';
				}
				for ($i = 0; $i < $count; $i++) {
					$data = is_array($val[$i]) ? $val[$i][1] : $val[$i];
					if ('exp' == strtolower($val[$i][0])) {
						$whereStr .= '(' . $key . ' ' . $data . ') ' . $rule . ' ';
					} else {
						$op = is_array($val[$i]) ? $comparison[strtolower($val[$i][0])] : '=';
						$whereStr .= '(' . $key . ' ' . $op . ' ' . $this->parseValue($data) . ') ' . $rule . ' ';
					}
				}
				$whereStr = substr($whereStr, 0, -4);
			}
		} else {
			$whereStr .= $key . ' = ' . $this->parseValue($val);
		}
		
		return $whereStr;
	}
	
	/**
	 * parse where item key
	 * 
	 * @param type $key
	 * @return type
	 */
	final protected function parseKey($key)
	{
		return $key;
	}
	
	/**
	 * parse where item value
	 * 
	 * @param type $key
	 * @return type
	 */
	final protected function parseValue($value)
	{
		// modify liule1 2015.5.21
        if (is_string($value)) {
//             $value =  '\''.$this->_escapeString($value).'\'';
			$value = "'{$this->escape($value)}'";
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
//             $value =  $this->_escapeString($value[1]);
        	$value = "'{$this->escape($value[1])}'";
        } elseif (is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        } elseif (is_bool($value)){
            $value =  $value ? '1' : '0';
        } elseif (is_null($value)){
            $value =  'null';
        }
        return $value;
	}
	
	/**
	 * safe to sql
	 * 
	 * @param type $value
	 * @return type
	 */
	final protected function _escapeString($value)
	{
		// modify by liule1 20150521
// 		if (MAGIC_QUOTES_GPC) {
// 			return $value;
// 		} else {
// 			return addslashes($value);
// 		}
		//  -------------------------------------//
		// ci hanle http request's params already, so 
		return $this->escape($value);
		// end
	}
	
	protected function _fields($table = null, $more = true)
	{
		if (empty($table)) {
			$table = $this->_table;
		}
		
		return $this->fields($table, $more);
	}
	
	/**
	 * get table fileds info
	 * 
	 * @param type $table
	 * @param type $more
	 * @return type
	 */
    public function fields($table, $more = true) {
		$more	=	(bool) $more;
		return $this->db->list_fields($table);
        $rs =   $this->db->simple_query('SHOW COLUMNS FROM ' . $table);
		while ($row = mysql_fetch_assoc($rs)) {
			$result[] = $row;
		}
		mysql_free_result($rs);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$val['Field']] = array(
                'name'    => $val['Field'],
                'type'    => $val['Type'],
                'notnull' => (bool) ($val['Null'] === ''), // not null is empty, null is yes
                'default' => $val['Default'],
                'primary' => (strtolower($val['Key']) == 'pri'),
                'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
            );
        }
        return $more ? $info : array_keys($info);
    }
	
    // -----------------------------------------------------------------------------------------------------//
    public function check_escape($params = array()) {
    	$return = true;
    	is_array($params) || $params = array($params);
    	foreach ($params as $v) {
	    	if (is_string($v) && $v != $this->escape($v)) {
	    		$return = false;
	    		break;
	    	}
    	}
    	return $return;
    }
}

?>
