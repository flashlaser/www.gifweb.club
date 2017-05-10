<?php

/**
 * seed
 */
class Seed {
    private $ci;
    var $_class;    // 当前的class
    var $_method;   // 当前的method
    var $_params;   // 当前的传参
    var $_chains = array(); // 配置seed链
    var $_match_chains = array();   // 符合条件的
    
    var $_the_chain_pre = '';   // 验证的seed链
    var $_the_chain_cur = '';   // 当前的 seed 环节
    var $_flag = '';    // 唯一标识
    var $_is_chain = 0;
    var $_check_success = 1;    // 检查成功！！，可生成下个SEED
    
    var $_old_chains_str = '';
    var $_old_seed;
    
    var $_level = -1;    // 当前SEED权限
    
    var $_sep = array(  // 分隔符
        'chain_and_seed' => '|',
        'chain_and_chain' => ',',  
        'class_and_method' => ':',
        'method_and_params' => '!',
        'param_and_param' => '#',
        'param_key_and_val' => '@',
    );
    
    var $_cache_key_pre = '';
    var $_cache_expire = '';

    public function __construct(){
        $this->ci =  get_instance();;
        $this->ci->load->config('check_seed_chainlist', true);
        $this->_chains = $this->ci->config->item('check_seed_chainlist');
        $this->ci->load->driver('cache');
        $this->ci->load->model('common_model');
        
        $this->_cache_key_pre = 'glapp:' . ENVIRONMENT . 'seed:';
        $this->_cache_expire = 86400;
        
        $this->set_params($_REQUEST);
    }
    
    public function set_class($class) {
        $this->_class = strtolower($class);
    }
    public function set_method($method) {
        $this->_method = strtolower($method);
    }
    public function set_params($data) {
        $this->_params = $data;
    }
    
    public function set_unique_flag($unique_flag) {
        $this->_flag = $unique_flag;
    }
    // ========================================================================
    
    
    private function _check_config() {
        return ($this->_class && $this->_method && $this->_flag) ? 1 : 0;
    }

    //检查随机码方法
    public function check_randcode($seed){
        if (!$this->_check_config()) {
            exit(__CLASS__ . '->' . __FUNCTION__ . ':' . __LINE__);
        }
        $seed = (string) $seed;
        $this->_old_seed = $seed;
        
        //带入seed检查
        $val_arr = explode('|', $seed);
        $seed_chainlist = $val_arr[0];
        $in_vcode = isset($val_arr[1]) ? $val_arr[1] : '';

        //检查业务线，并判断是否需要检查值
        return $this->check_chain($seed_chainlist, $in_vcode);
    }

    public function get_old_seed() {
        return $this->_old_seed;
    }
    //产生随机码方法
    public function generate_new_seed(){
        if (!$this->_check_success || !$this->_is_chain) {
            return '';
        }
        
        if (!$this->_check_config()) {
            $exit = (__CLASS__ . '->' . __FUNCTION__ . ':' . __LINE__);
            return $exit;
        }
        
        
        // 删除当前SEED，生成新SEED
        $cache_key = $this->_get_cache_key($this->_old_chains_str);
        $this->ci->cache->redis->delete($cache_key);
        
        if ($this->_is_chain) {
            //拼装前置
            $seed_name = trim($this->_the_chain_pre . $this->_sep['chain_and_chain'] . $this->_the_chain_cur, $this->_sep['chain_and_chain']);

            //调用方法生成随机码
            $vcode = $this->ci->common_model->get_code(8,6);   
            $new_seed = $this->my_encode($seed_name) . $this->_sep['chain_and_seed'] . $vcode;

            //保存新码
            $redis_key = $this->_get_cache_key($this->my_encode($seed_name));

            $this->ci->cache->redis->save($redis_key, $vcode, $this->_cache_expire);
        } else {
            // 本次访问的方法，不需要验证或生成 seed，即没有在 chain list 中。
            $new_seed = $this->_old_seed;
        }
        
        //返回
        return $new_seed;
    }

    // =====================================================================================================//\
    private function _get_cache_key($chain_strting) {
        return $this->_cache_key_pre . $this->my_encode($chain_strting);
    }

    private function check_chain($seed_md5, $seed){
        //获取合理业务线配置文件
        $cur_class_and_method = ($this->_class . $this->_sep['class_and_method'] . $this->_method);
        $cur_chains_pre_arr = array();  // 当前调的接口所产生的链
        
        foreach ($this->_chains as $chain_key => $chain) {
            // init match chain
            if (!isset($this->_match_chains[$chain_key])) {
                $this->_match_chains[$chain_key] = array(
                   'match' => 0,
                   'chain' => $chain,
                   'chain_pre' => array(),
                   'chain_pre_encode' => array(),
               );
            }
            $_level = 0;
            $chain_pre_arr = array('');
            foreach ($chain as $k => $v) {
                $ring = $v['ring'];
                $params = isset($v['params']) ? $v['params'] : array();
                $required = isset($v['required']) ? $v['required'] : 1;
                $_is_cur_ring = true;
                
                if ($cur_class_and_method != $ring) {
                    // 判断是否为该环1：通过class和method
                    $_is_cur_ring = false;
                }
                
                if ($params) {
                    // 有参数条件
                    $ring_params = '';
                    foreach ($params as $pk => $pv) {
                        if ($_is_cur_ring && !(isset($this->_params[$pk]) && $this->_params[$pk] == $pv)) {
                            // 判断是否为该环1：通过参数
                            $_is_cur_ring = false;
                        }
                        $ring_params .= $this->_sep['param_and_param'] . $pk . $this->_sep['param_key_and_val'] . $pv;
                    }
                    
                    $ring = $ring . $this->_sep['method_and_params'] . substr($ring_params, strlen($this->_sep['param_and_param']));
                }
                
                // 加入match_chains
                foreach ($chain_pre_arr as $cpv) {
                    $_cpv = rtrim($cpv , $this->_sep['chain_and_chain']);
                    
                    if (!isset($this->_match_chains[$chain_key]['chain_pre'][$_cpv])) {
                        $this->_match_chains[$chain_key]['chain_pre'][$_cpv] = $_level;
                        $this->_match_chains[$chain_key]['chain_pre_encode'][$this->my_encode($_cpv)] = $_level;
                    }
                }
                
                // match到当前访问请求在
                if ($_is_cur_ring) {
                    foreach ($chain_pre_arr as $cpv) {
                        $_cpv = rtrim($cpv , $this->_sep['chain_and_chain']);
                        $cur_chains_pre_arr[] = $_cpv;
                    }
                    
                    $this->_the_chain_cur = $ring;  // 当前的环， 生成seed需要使用
                    $this->_is_chain = 1;
                    
                    $this->_match_chains[$chain_key]['match'] = 1;
                }
                
                // 处理 chain_pre
                if ($required) {
                    foreach ($chain_pre_arr as $cpk => $cpv) {
                        $chain_pre_arr[$cpk] .= $ring . ',';
                    }
                } else {
                    // 分裂
                    $chain_pre_arr_cop = $chain_pre_arr;
                    foreach ($chain_pre_arr as $cpk => $cpv) {
                        $chain_pre_arr[$cpk] .= $ring . ',';
                    }
                    $chain_pre_arr = array_merge($chain_pre_arr_cop, $chain_pre_arr);
                }
                $_level++;
            }
        }
        //判断当前接口是否需要检验
        if (!$cur_chains_pre_arr) { //不在检查线,一次放行
            $this->_match_chains = array();
            return true; //检查通过
        } else { 
            // match chains， 靠后的接口SEED能适用之前的，反之则不行
            $cur_max_level = 0; // 匹配到的最大权限，用来生成$this->_old_chains_str
            foreach ($this->_match_chains as $k => $v) {
                if ($v['match']) {
                    $need_level = 0;
                    $cur_level = 0;
                    foreach ($v['chain_pre'] as $chain_pre => $level) {
                        if ($this->my_encode($chain_pre) == $seed_md5) {
                            if ($cur_level < $level) {
                                $cur_level = $level;
                            }
                            
                        }
                    }
                    
                    // 权限验证，取权限最接近的。
                    $flag = 0;
                    foreach ($cur_chains_pre_arr as $cur_chains_v) {
                        // var_dump($cur_level . ' L:' . $v['chain_pre'][$cur_chains_v] . ' LL: ' . $cur_max_level);
                        if (isset($v['chain_pre'][$cur_chains_v]) && $cur_level >= $v['chain_pre'][$cur_chains_v] && $v['chain_pre'][$cur_chains_v] >= $cur_max_level) {
                            $cur_max_level = $v['chain_pre'][$cur_chains_v];
                            $this->_old_chains_str = $seed_md5;
                            $this->_the_chain_pre = $cur_chains_v;
                            $flag = 1;
                            $this->_level = $cur_max_level;
                        }
                    }
                }
            }
            
            
            //匹配到是链，但没有合适的链，证明是权限不对
            if ($this->_is_chain && $this->_level < 0) {
                return false;
            }
            // 验证原值， 可能是首个环
            if ($this->_old_chains_str) {
                $this->_check_success = $this->_check_chain_val($seed);
            }
            
            return $this->_check_success;
        }
    }

    private function my_encode($str){
        return $str . '*';
        return md5($str);
    }
    
    /**
     * 验证上一步chain值
     * @param  [type] $val [description]
     * @return 0异常， 1成功
     */
    private function _check_chain_val($val) {
        if (!$this->_old_chains_str) {
            return 0;
            exit('' . __LINE__);
        }
        $val = (string) $val;

        if (empty($val)) {
            return 0;   //不是第一次单未提交seed 返回0
        } else {
            $cache_key = $this->_get_cache_key($this->_old_chains_str);
            $cache_val = $this->ci->cache->redis->get($cache_key);

            if ($cache_val == $val) {
                return 1; //通过
            } else {
                return 0; //不通过
            }
        }
    }

}

?>
