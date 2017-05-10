<?php
/**
 *
 * @name Starting_up_ad_model
 * @desc 开机广告
 *
 * @author	 liule1
 * @date 2016.03.01
 *
 * @copyright (c) 2016 SINA Inc. All rights reserved.
 */
class Starting_up_ad_model extends MY_Model {
	
	var $_cache_key_pre = '';
	var $_cache_expire = 600;
    protected $_table = 'gl_starting_up_ad';
	function __construct() {
		parent::__construct ();
		$this->_cache_key_pre = "glapp:" . ENVIRONMENT . ":starting_up_ad_model:";
		$this->load->driver('cache');
	}
    
    public function get_ad($game_id, $retio) {
        $platform = $this->platform;
        $cache_key = $this->_cache_key_pre . 'operation';
        $hash_key =  "ad:$game_id:$platform";
        
        $data= $this->cache->redis->hGet($cache_key, $hash_key);
        $data && $data = json_decode($data, 1);
        
        if ($data === false) {
            $origin_platform = $platform === 'ios' ? array(1,3) : array(2, 3);
            $origin_data = $this->get_data($game_id, $origin_platform);
            if (empty($origin_data)) {
                $data = array();
            } else {
                // 取最接近redio的row
                // format by end time
                foreach ($origin_data as $k => $v) {
                    if ($v['end_put_time'] < SYS_TIME) {
                        unset($origin_data[$k]);
                    }
                }
                
                // 取到合适的row, 目前是取第一个，因为比例的优先度 和 广告时间优先度 的比重确立起来麻烦，默认广告的时间优先度最高
                $data = $origin_data ? array_shift($origin_data) : array();
            }
        }
        
        // 取合适比例的图片， 最简单的算法：
        //       $retio > 1 ,  优先 > 1接近的， 其次是1 最后是最大的 < 1
        //       $retio == 1 , 优先 == 1， 其次对比 > 1 与 < 1 倒数的大小
        //       $retio < 1 ， 有限 < 1 其次 1 ， 最后是最小的 > 1 数   
        $images = $data ? $data['images'] : array();
        $suit_image = array();  // 合适的图片
        
        if ($images) {
            // format 
            $images_0 = array();    // < 1
            $images_1 = array();    // == 1
            $images_2 = array();    // > 1
            $suit_retio = 0;
            
            foreach ($images as $k => $v) {
                $ret = round($v['width'] / $v['height'], 8);
                if ($ret == $retio) {
                    // 正好相等，
                    $suit_image = $v;
                    break;
                } elseif ($ret < 1) {
                    $ret = round($v['height'] / $v['width'], 8);
                    $images_0[$ret] = $v;
                } elseif ($ret == 1) {
                    $images_1[$ret] = $v;
                } elseif ($ret > 1) {
                    $images_2[$ret] = $v;
                }
            }
            
            if (!$suit_image) {
                krsort($images_0, SORT_NUMERIC);
                ksort($images_1, SORT_NUMERIC);
                ksort($images_2, SORT_NUMERIC);
                
                // 没有正好的
                if ($retio < 1) {
                    foreach ($images_0 as $k => $v) {
                        $ret = round($v['width'] / $v['height'], 8);
                        if (!$suit_retio || abs($ret - $retio) < $suit_retio) {
                            $suit_retio = $ret;
                            $suit_image = $v;
                        }
                    }
                    if ($images_1 && (!$suit_retio || abs(1 - $retio) < ($suit_retio - $retio) )) {
                        // == 1比较合适
                        $suit_retio = 1;
                        $suit_image = array_pop($images_1);
                    }
                    
                    // 如果没有合适的，则用 > 1的
                    if (!$suit_retio) {
                        $suit_image = array_shift($images_2);
                    }
                } elseif ($retio == 1) {
                    // 正合适的，不可能到这里
                    $image0 = $images_0 ? array_pop($images_0) : array();
                    $image2 = $images_2 ? array_pop($images_2) : array();
                    if ($image0 && $image2) {
                        $suit_image = ($image0['height'] / $image0['width']) - ($image2['width'] / $image2['height']) > 0 ? $image2 : $image0;
                    } elseif ($image0) {
                        $suit_image = $image0;
                    } elseif ($image2) {
                        $suit_image = $image2;
                    }
                } else {
                    foreach ($images_2 as $k => $v) {
                        $ret = round($v['width'] / $v['height'], 8);
                        if (!$suit_retio || abs($ret - $retio) < $suit_retio) {
                            $suit_retio = $ret;
                            $suit_image = $v;
                        }
                    }
                    if ($images_1 && (!$suit_retio || abs(1 - $retio) < ($suit_retio - $retio)) ) {
                        // == 1比较合适
                        $suit_retio = 1;
                        $suit_image = array_pop($images_1);
                    }
                    
                    // 如果没有合适的，则用 > 1的
                    if (!$suit_retio) {
                        $suit_image = array_shift($images_0);
                    }
                }
                
            }
            
            
            
        }
        
        
        $data['image'] = $suit_image;
        return $data;
    }
    
    public function get_data($game_id, $platform) {
        $platform = is_array($platform) ? $platform : array($platform);
        
        $cache_key = $this->_cache_key_pre . 'data';
        $hash_key =  "ad:$game_id:" . implode(',', $platform);
        
        $data= $this->cache->redis->hGet($cache_key, $hash_key);
        $data && $data = json_decode($data, 1);
        $data = false;
        if ($data === false) {
            $data = $this->_get_data_from_cache($game_id, $platform);
            foreach ($data  as $k => $v) {
                $data[$k]['images'] = json_decode($v['images'], 1);
                foreach ($data[$k]['images'] as $img_k => $img_v) {
                    if (!in_array($img_v['platform'], $platform)) {
                        unset($data[$k]['images'][$img_k]);
                    }
                }
                if (empty($data[$k]['images'])) {
                    unset($data[$k]);
                }
            }
            
            $data || $data = array();
            
            $this->cache->redis->hSet($cache_key, $hash_key, json_encode($data));
            $this->cache->redis->expire($cache_key, $this->_cache_expire);
        }
        
        return $data;
    }
    
    private function _get_data_from_cache($game_id, $platform) {
        $time = SYS_TIME;
        $sql = "SELECT * FROM {$this->_table} WHERE status=1 and put_platform in (" . implode(',', $platform) . ")  AND (put_game_id ='' OR put_game_id like '%|{$game_id}|%') ORDER BY id desc";

        $query = $this->db->query_read($sql);
        $query = $query ? $query->result_array() : array();
        return $query;
    }
	
    
}
