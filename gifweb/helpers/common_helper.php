<?php
/**
 * 判断是否为URL
 * @param  [type]  $path [description]
 * @return boolean       [description]
 */
function is_url($path) {
    $str = substr($path, 0 , 7);
    $arr = array(
        'http://',
        'https:/'
    );
    return in_array($str, $arr);
}

/**
 * 攻略图片完整URL
 * @param  [type] $path [description]
 * @return [type]       [description]
 */
function gl_img_url($path) {
    $old_img_domin='http://store.games.sina.com.cn/';
    $url = '';
    if (!empty($path)) {
        $url = is_url($path) ? $path : $old_img_domin . $path;
    }
    return $url;
}

/**
 * URL 转为路径，且左侧没有/
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function url_to_path($url) {
    if (is_url($url)) {
        $arr = explode('/', $url);
        $arr = array_slice($arr, 2 );
        $url = implode('/', $arr);
    }
    return ltrim($url, '/');
}


/**
 * 获取函数被调用信息
 * @return [type]     [description]
 * @author liule1
 * @date   2016-08-16
 */
function get_caller_info($deep = 1) {
    $c = '';
    
    $debug_trace = debug_backtrace();

    $last_trace = $trace = array();
    
    for ($i = 0; $i <= $deep; $i++) {
        $last_trace = $trace;
        $trace = array_shift($debug_trace);
    }

    
    $file = $last_trace['file'];
    $line = $last_trace['line'];
    $class = $trace['class'] ? $trace['class'] : '';
    $func = $trace['function'] ? $trace['function'] : '';
    $type = $trace['type'] ? $trace['type'] : '.';
    $args = $trace['args'] ? $trace['args'] : array();
    $arg_arr = array();
    foreach ($args as $v) {
        // @TODO 传入对象什么的
        if (is_array($v)) {
            $arg_arr[] = json_encode($v);
        } else {
            $arg_arr[] = $v;
        }
    }
    
    
    $c = $file . ": {$line}: {$class} {$type} {$func} (" . implode(', ', $arg_arr) . ")";
    
    return($c);
}
