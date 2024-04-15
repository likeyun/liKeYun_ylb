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
	
	// 数据库配置
	include '../../Db.php';
    
    // 实例化类
	$db = new DB_API($config);

	// 获取所有上架的卡密项目
	$getKamiProJectList = $db->set_table('ylb_kami')->findAll(
	    $conditions = ['kami_status' => 1],
	    $order = null,
	    $fields = 'kami_title,kami_id,kami_status,kami_repeat_tiqu,kami_repeat_tiqu_interval,kami_type,id',
	    $limit = null
	);
	
    // 读取配置
    $getKamiConfig = $db->set_table('ylb_kamiConfig')->findAll(
	    $conditions = ['id' => 1],
	    $order = null,
	    $fields = 'kmConf_status,kmConf_xcx_title,kmConf_adShow,kmConf_adType,kmConf_bannerID,kmConf_videoID,kmConf_jiliStatus,kmConf_jiliID,kmConf_kfQrcode,kmConf_notification_text',
	    $limit = null
	);
    
    // 服务状态
    if($getKamiConfig[0]['kmConf_status'] == 1) {
        
        // 1 正常服务
        $Service_Status = true;
    }else {
        
        // 2 暂停服务
        $Service_Status = false;
    }
    
    // 公告内容
    $Service_Notification_Text = $getKamiConfig[0]['kmConf_notification_text'];
    
    // 小程序提取页的顶部标题
    $Xcx_title = $getKamiConfig[0]['kmConf_xcx_title'];
    
    // 提取页广告开关
    if($getKamiConfig[0]['kmConf_adShow'] == 1) {
        
        // 1 提取页广告开
        $kmConf_adShow = true;
    }else {
        
        // 2 提取页广告关
        $kmConf_adShow = false;
    }
    
    // banner广告ID
    $kmConf_bannerID = $getKamiConfig[0]['kmConf_bannerID'];
    
	// 视频广告ID
	$kmConf_videoID = $getKamiConfig[0]['kmConf_videoID'];
	
	// 激励视频广告开启状态
	if($getKamiConfig[0]['kmConf_jiliStatus'] == 1) {
        
        // 1 激励视频广告开
        $jili_adShow = true;
        
        // 激励视频广告开启的时候，提取按钮上的文字
        // 因为审核要求明确告知用户点击这个按钮是看广告的才给通过
        // 所以这个文字就用后端返回，过审后你可以改其他的
	    $tiquButtonText = '看视频免费提取';
    }else {
        
        // 2 激励视频广告关
        $jili_adShow = false;
        $tiquButtonText = '立即提取';
    }
    
    // 激励视频广告ID
    $kmConf_jiliID = $getKamiConfig[0]['kmConf_jiliID'];
    
	// 提取页使用的广告类型（1banner 2视频）
	$kmConf_adType = $getKamiConfig[0]['kmConf_adType'];
	
    // 获取结果
	if($getKamiProJectList){
	    
	    // 获取成功
		$result = array(
		    'kamiProJectList' => $getKamiProJectList,
		    'code' => 200,
		    'msg' => '获取成功',
		    'Xcx_title' => $Xcx_title,
		    'Service_Status' => $Service_Status, // 服务状态
		    'Service_Notification_Text' => $Service_Notification_Text, // 公告内容
		    'kmConf_adShow' => $kmConf_adShow, // 提取页广告开关
            'kmConf_bannerID' => $kmConf_bannerID, // banner广告ID
            'kmConf_videoID' => $kmConf_videoID, // 视频广告ID
		    'jili_adShow' => $jili_adShow, // 激励视频广告开启状态
		    'kmConf_jiliID' => $kmConf_jiliID, // 激励视频广告ID
		    'kmConf_adType' => $kmConf_adType, // 提取页使用的广告类型（1banner 2视频）
		    'tiquButtonText' => $tiquButtonText,
		);
	}else{
	    
	    // 获取失败
        $result = array(
            'code' => 204,
            'msg' => '暂无项目',
            'Xcx_title' => $Xcx_title,
		    'Service_Status' => $Service_Status, // 服务状态
		    'Service_Notification_Text' => $Service_Notification_Text, // 公告内容
		    'kmConf_adShow' => $kmConf_adShow, // 提取页广告开关
            'kmConf_bannerID' => $kmConf_bannerID, // banner广告ID
            'kmConf_videoID' => $kmConf_videoID, // 视频广告ID
		    'jili_adShow' => $jili_adShow, // 激励视频广告开启状态
		    'kmConf_jiliID' => $kmConf_jiliID, // 激励视频广告ID
		    'kmConf_adType' => $kmConf_adType, // 提取页使用的广告类型（1banner 2视频）
		    'tiquButtonText' => $tiquButtonText,
        );
	}

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>