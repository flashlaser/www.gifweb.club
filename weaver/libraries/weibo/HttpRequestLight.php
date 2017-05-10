<?php

class HttpRequestLight {

    private $aKey;
    private $mCurl;
    
    public function __construct() {
        
    }
    
    public function init($akey) {
        $this->aKey = $akey;
        $this->mCurl = curl_init();
        if (isset($_COOKIE['SUP']) && isset($_COOKIE['SUE'])) {
		curl_setopt($this->mCurl, CURLOPT_COOKIE, 'SUP=' . rawurlencode($_COOKIE['SUP']) . ';SUE=' . rawurlencode($_COOKIE['SUE']));
        }
    }

    public function send($sUrl, $sMethod, $aParam = "", $user = "", $pass = "", $bForce = false) {
        $sMethod = strtoupper($sMethod);
        if (!isset($aParam) || empty($aParam))
        {
        	//$sQuery = "source=".$user;
        	$sQuery = '';
        }
        else if (is_array($aParam)) {
            //$aParam["source"] = $this->aKey;
            $sQuery = http_build_query($aParam);
        } else {
            //$sQuery = $aParam . "&source=" . $this->aKey;
            $sQuery = $aParam ;
        }

        if ($sMethod == "POST") {
            curl_setopt($this->mCurl, CURLOPT_POST, 1);
            curl_setopt($this->mCurl, CURLOPT_POSTFIELDS, $sQuery);
        } else {
            curl_setopt($this->mCurl, CURLOPT_POST, 0);
            $sUrl = $sUrl . "?" . $sQuery;
        }


        if ($bForce) {
            $user = ($user != "") ? $user : USER;
            $pass = ($pass != "") ? $pass : PASS;
            curl_setopt($this->mCurl, CURLOPT_USERPWD, $user . ":" . $pass);
        }

        if (0 === strpos($sUrl, 'https')) {
            curl_setopt($this->mCurl, CURLOPT_SSL_VERIFYHOST, 1);
            curl_setopt($this->mCurl, CURLOPT_SSL_VERIFYPEER, false);
        }

        curl_setopt($this->mCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->mCurl, CURLOPT_URL, $sUrl);
        curl_setopt($this->mCurl, CURLOPT_TIMEOUT, 10);
        $aResult = curl_exec($this->mCurl);
        return json_decode($aResult, true);
    }
	
    public function setSource($appkey)
    {
    	$this->aKey = $appkey;
    }
    
    private function setUser($name, $pass) {
        curl_setopt($this->mCurl, CURLOPT_USERPWD, "$name:$pass");
    }

 
    public function upload($sUrl, $status, $file) {
        $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $file = file_get_contents($file, false);
        $multipartbody = "";
// 需要上传的图片所在路径
        $multipartbody .= $MPboundary . "\r\n";
        $multipartbody .= "Content-Disposition: form-data; name=\"pic\"; filename=\"wiki.png\"\r\n";
        $multipartbody .= "Content-Type: image/png\r\n\r\n";
        $multipartbody .= $file . "\r\n";

        $multipartbody .= $MPboundary . "\r\n";
        $multipartbody .= "content-disposition: form-data; name=\"source\"\r\n\r\n";
        $multipartbody .= $this->aKey . "\r\n";

        $multipartbody .= $MPboundary . "\r\n";
        $multipartbody .= "content-disposition: form-data; name=\"status\"\r\n\r\n";
        $multipartbody .= $status . "\r\n";
        $multipartbody .= "\r\n" . $endMPboundary;
        //curl_setopt($this->mCurl, CURLOPT_REFERER, "http://event.games.sina.com.cn");
        curl_setopt($this->mCurl, CURLOPT_URL, $sUrl);
        curl_setopt($this->mCurl, CURLOPT_POST, 1);
        curl_setopt($this->mCurl, CURLOPT_POSTFIELDS, $multipartbody);
        curl_setopt($this->mCurl, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=$boundary"));
        curl_setopt($this->mCurl, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($this->mCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->mCurl, CURLOPT_RETURNTRANSFER, 1);
        $aResult = curl_exec($this->mCurl);
        return json_decode($aResult, true);
    }

}

