<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     */

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $kmConf_status = trim($_POST['kmConf_status']);
        $kmConf_adShow = trim($_POST['kmConf_adShow']);
        $kmConf_adType = trim($_POST['kmConf_adType']);
        $kmConf_bannerID = trim($_POST['kmConf_bannerID']);
        $kmConf_videoID = trim($_POST['kmConf_videoID']);
        $kmConf_jiliStatus = trim($_POST['kmConf_jiliStatus']);
        $kmConf_jiliID = trim($_POST['kmConf_jiliID']);
        $kmConf_kfQrcode = trim($_POST['kmConf_kfQrcode']);
        $kmConf_xcx_title = trim($_POST['kmConf_xcx_title']);
        $kmConf_notification_text = trim($_POST['kmConf_notification_text']);
        $kmConf_appid = trim($_POST['kmConf_appid']);
        $kmConf_appsecret = trim($_POST['kmConf_appsecret']);
        
        // 验证Url合法性
        function is_url($url){
            $r = "/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is";
            if(preg_match($r,$url)){
                return TRUE;
            }else{
                return FALSE;
            }
        }
        
        // 过滤参数
        if(empty($kmConf_status) || !isset($kmConf_status)){
            
            $result = array(
                'code' => 203,
                'msg' => '服务状态未设置'
            );
        }else if(empty($kmConf_xcx_title) || !isset($kmConf_xcx_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '提取页标题未设置'
            );
        }else if(empty($kmConf_adShow) || !isset($kmConf_adShow)){
            
            $result = array(
                'code' => 203,
                'msg' => '提取页广告开关未设置'
            );
        }else if(empty($kmConf_adType) || !isset($kmConf_adType)){
            
            $result = array(
                'code' => 203,
                'msg' => '提取页广告类型未选择'
            );
        }else if($kmConf_adType == 1 && empty($kmConf_bannerID)){
            
            $result = array(
                'code' => 203,
                'msg' => 'Banner广告ID未填写'
            );
        }else if($kmConf_adType == 2 && empty($kmConf_videoID)){
            
            $result = array(
                'code' => 203,
                'msg' => '视频广告ID未填写'
            );
        }else if(empty($kmConf_jiliStatus) || !isset($kmConf_jiliStatus)){
            
            $result = array(
                'code' => 203,
                'msg' => '激励视频广告开关未设置'
            );
        }else if($kmConf_jiliStatus == 1 && empty($kmConf_jiliID)){
            
            $result = array(
                'code' => 203,
                'msg' => '激励视频广告ID未填写'
            );
        }else if(empty($kmConf_appid) || !isset($kmConf_appid)){
            
            $result = array(
                'code' => 203,
                'msg' => '小程序AppId未设置'
            );
        }else if(empty($kmConf_appsecret) || !isset($kmConf_appsecret)){
            
            $result = array(
                'code' => 203,
                'msg' => '小程序AppSecret未设置'
            );
        }else if(empty($kmConf_kfQrcode) || !isset($kmConf_kfQrcode)){
            
            $result = array(
                'code' => 203,
                'msg' => '客服二维码未上传'
            );
        }else if(is_url($kmConf_kfQrcode) == FALSE){
            
            $result = array(
                'code' => 203,
                'msg' => '客服二维码不是正确的Url'
            );
        }else{
            
            // 数据库配置
            include '../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 需更新的字段
            $params = [
                'kmConf_status' => $kmConf_status,
                'kmConf_adShow' => $kmConf_adShow,
                'kmConf_adType' => $kmConf_adType,
                'kmConf_bannerID' => $kmConf_bannerID,
                'kmConf_videoID' => $kmConf_videoID,
                'kmConf_jiliStatus' => $kmConf_jiliStatus,
                'kmConf_jiliID' => $kmConf_jiliID,
                'kmConf_kfQrcode' => $kmConf_kfQrcode,
                'kmConf_xcx_title' => $kmConf_xcx_title,
                'kmConf_notification_text' => $kmConf_notification_text,
                'kmConf_appid' => $kmConf_appid,
                'kmConf_appsecret' => $kmConf_appsecret,
            ];
            
            // 执行更新
            $updateConf = $db->set_table('ylb_kamiConfig')->update(['id' => '1'], $params);
            
            if($updateConf){
                
                // 配置成功
                $result = array(
                    'code' => 200,
                    'msg' => '已保存'
                );
            }else{
                
                // 配置失败
                $result = array(
                    'code' => 202,
                    'msg' => '保存失败'
                );
            }
            
        }
        
    }else{
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }

    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>