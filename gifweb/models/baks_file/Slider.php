<?php
class Slider {

  public function __construct() {

  }

  public function getSliderInfoByUrl($sliderUrl){
      $sliderInfo=$this->getSliderInfoFromApiByUrl($sliderUrl);
      return $sliderInfo;
  }

  public function getSliderImagesArrayByUrl($sliderUrl){///11111
        $arrTmp=explode("#",$sliderUrl);
        $sliderInfo=$this->getSliderInfoByUrl($arrTmp[0]);
        $returnData=array();
        if(empty($sliderInfo)){
              $returnData["image_count"]=0;
              $returnData["thumbnail_urls"]=array();
        }else{
              $returnData["image_count"]=$sliderInfo["total"];
              $returnData["sid"]=$sliderInfo["album"]["sid"];
              $returnData["images_id"]=$sliderInfo["album"]["id"];
              foreach($sliderInfo["data"]["item"] as $key =>$imgInfo){
                    $returnData["thumbnail_urls"][]=$imgInfo["thumb_url"];
              }
        }

        return $returnData;

  }
  /**
   * 同 getVideoByIdAry 只是直接从 db 中读数并写 mc
   */
  public function getSliderInfoFromApiByUrl($sliderUrl) {
        try {
             $key='1372825881';
             $getFromUrl="http://platform.sina.com.cn/slide/image?app_key=".$key."&format=json&prevnext=1&url=".$sliderUrl;
             $retJsonData=Util::curl_get_contents($getFromUrl);
             $retData=json_decode($retJsonData,true);
              return $retData;
        } catch (Exception $e) {
              ;
        }
        return array();
  }

}

?>