<?php

require_once dirname(__FILE__) . '/OssClient.php';

function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/../' . DIRECTORY_SEPARATOR . $path . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');

use OSS\OssClient;
use OSS\Core\OssException;

class CI_Oss extends OSS\OssClient {
    private $endpoint;
    private $accessKeyId;
    private $accessKeySecret;
    private $bucket;
    private $CI;
    
    public function __construct($config = array()) {
        
        if (empty($config['accessKeyId']) || empty($config['accessKeySecret']) || empty($config['endpoint']) || empty($config['bucket'])) {
            exit('error oss config');
        }
        
        $this->accessKeyId = $config['accessKeyId'];
        $this->accessKeySecret = $config['accessKeySecret'];
        $this->endpoint = $config['endpoint'];
        $this->bucket = $config['bucket'];
        
        try {
            parent::__construct($this->accessKeyId, $this->accessKeySecret, $this->endpoint, false);
        } catch (OssException $e) {

        }
        
        $this->createBucket();
    }
    
    public function get_endpoint() {
        return $this->endpoint;
    }
    

    /**
     * 根据Config配置，得到一个OssClient实例
     *
     * @return OssClient 一个OssClient实例
     */
    public function getOssClient()
    {
        return $this;
    }

    public  function getBucketName()
    {
        return $this->bucket;
    }

    /**
     * 工具方法，创建一个存储空间，如果发生异常直接exit
     */
    public function createBucket()
    {
        $ossClient = $this->getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = $this->getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            parent::createBucket($bucket, $acl);
        } catch (OssException $e) {

            $message = $e->getMessage();
            if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
                echo "Please Check your AccessKeyId and AccessKeySecret" . "\n";
                exit(0);
            } elseif (strpos($message, "BucketAlreadyExists") !== false) {
                echo "Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. " . "\n";
                exit(0);
            }
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    public static function println($message)
    {
        if (!empty($message)) {
            echo strval($message) . "\n";
        }
    }
}
