<?php
class Video {

      public function __construct() {

      }

    /*
     * 根据视频id获取视频详情
     *
     */
    public function getListVideoInfoById($id) {//1111
        $videoInfo=$this->getVideoInfoById($id);

        $returnData=array();
        if(empty($videoInfo)){
            return array('result'=>0);
        } else {
            $returnData['result'] = 1;
            $returnData['desc'] = "";
            $returnData['ipad_url'] = $videoInfo['stream'];
            $returnData['imagelink'] = $videoInfo['image'];
            $returnData['nick_name'] = "";
            $returnData['play_times'] = 0;
            $returnData['time_length'] = 0;
            return $returnData;
        }
    }

    public function getNewVideoInfoById($id) {////111111
        $videoInfo=$this->getVideoInfoByNewId($id);

        $returnData=array();
        if(empty($videoInfo)){
            return array('result'=>0);
        } else {
            $returnData['result'] = 1;
            $returnData['desc'] = "";
            $returnData['ipad_url'] = "";
            foreach ($videoInfo['videos'] as $_k => $_v) {
                if ($_v['type'] == 'mp4') {
                    $returnData['ipad_url'] = $_v['file_api']."?vid=".$_v['file_id'];
                    break;
                }
            }
            $returnData['imagelink'] = $videoInfo['image'];
            $returnData['nick_name'] = "";
            $returnData['play_times'] = $videoInfo['length'];
            $returnData['time_length'] = 0;
            return $returnData;
        }
    }

    public function getVideoInfoById($id) {//1111
            $videoInfo=$this->getVideoInfoByIdFromApi($id);
            return $videoInfo['data'];
    }

    public function getVideoInfoByIdFromApi($id) {//1111
        try {
            $getFromUrl='http://vms.video.sina.com.cn/interface/get_videos_by_flvid.php?flvids='.$id;
            $retJsonData=Util::curl_get_contents($getFromUrl);
            $retData=json_decode($retJsonData,true);
            return $retData;
        } catch (Exception $e) {
            ;
        }
        return array();
    }

    public function getVideoInfoByNewId($id) {//11111
            $videoInfo=$this->getVideoInfoByNewIdFromApi($id);
            return $videoInfo['data'];
    }

    public function getVideoInfoByNewIdFromApi($id) {/////1111
        try {
            $getFromUrl='http://s.video.sina.com.cn/video/play?video_id='.$id;
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