<?php 
class pagecache {
    /**
     * @var string $file 缓存文件地址
     * @access public
     */
    public $file;
    
    /**
     * @var int $cachetime 缓存时间
     * @access public
     */
    public $cachetime = 3600;
    
    /**
     * 构造函数
     * @param string $file 缓存文件地址
     * @param int $cachetime 缓存时间
     */
    function __construct($file, $cachetime = 3600) {
        $this->file = $file;
        $this->cachetime = $cachetime;
    }
     
    /**
     * 取缓存内容
     * @param bool 是否直接输出，true直接转到缓存页,false返回缓存内容
     * @return mixed
     */
    public function get($output = true) {
        
        if (is_file($this->file) && is_writable($this->file) && (time()-filemtime($this->file))<=$this->cachetime) {
            if ($output) {
                header('location:' . $this->file);
                exit;
            } else {
                return file_get_contents($this->file);
            }
        } else {
            return false;
        }
    }
     
    /**
      * 设置缓存内容
      * @param $content 内容html字符串
      */
    public function set($content) {
        if (is_writable($this->file)) {
            $fp = fopen($this->file, 'w');
            fwrite($fp, $content);
            fclose($fp);
        }
    }
}



 ?>
