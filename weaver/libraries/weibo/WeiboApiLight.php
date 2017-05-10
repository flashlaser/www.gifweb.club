<?php

require_once  SYSDIR.'/libraries/weibo/HttpRequestLight.php';
require_once  SYSDIR.'/libraries/weibo/weiboConfig.php';

/**
 * @desc 微博接口
 * @author gaoyi@staff.sina.com.cn
 * 
 */
class WeiboApiLight {

    private $mExt;
	private $httpRequest;
	private $ci;
    
    public function __construct($akey = '') {
    	$CI = &get_instance();
    	if ($akey != '')
    	{
    	    if ($akey['akey'])
	    		{
	    		$wb_akey = $akey['akey'];
	    		}
    		else 
	    		{
	    		$wb_akey = $akey;
	    		}
    	}
    	else 
    	{
    		$wb_akey = $CI->config->item('WB_AKEY');
    	}
    	$this->ci = $CI;
    	//$wb_skey = $CI->config->item('WB_SKEY');
        $this->mExt = "json";
        $this->httpRequest = new HttpRequestLight();
        $this->httpRequest->init($wb_akey);
    }

    public function setExt($sExt) {
        if (in_array($sExt, array("json", "xml"))) {
            $this->mExt = $sExt;
        } else {
            $this->mExt = "json";
        }
    }

    /**
     * 授权token
     */
    public  function get_token()
    {
        $aParam['client_id']='2935211146';
        $aParam['redirect_uri']='http://kan.sina.com.cn/lightapp/home';
        $url = 'https://api.weibo.com/oauth2/authorize?client_id='.$aParam['client_id'].'&redirect_uri='.$aParam['redirect_uri'].'&response_type=code';
        header('Location:'.$url);
    }

    /**
     * 授权token
     */
    public function access_token($token)
    {
        $aParam['client_id']='2935211146';
        $aParam['client_secret']='1a289d1db0ed233e9e9c93be1d482c72';
        $aParam['grant_type']='authorization_code';
        $aParam['code']=$token;
        $aParam['redirect_uri']='http://kan.sina.com.cn/lightapp/home';
        return $this->httpRequest->send(WeiboConfig::API_ACCESS_TOKEN, 'POST', $aParam);
    }


    /**
     * 获取用户粉丝数量
     */
    public function get_user_fans_count($uid,$access_token)
    {
        $aParam['uid']=$uid;
        $aParam['access_token']=$access_token;
        return $this->httpRequest->send(WeiboConfig::GET_WEIBO_FANS_COUNT. "." . $this->mExt, 'GET', $aParam);
    }


}

