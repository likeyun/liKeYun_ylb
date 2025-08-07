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
    	@$page = $_GET['p']?$_GET['p']:1;
    	$channel_id = trim($_GET['channel_id']);
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    
    	// 获取总数
    	$channelDataNum = $db->set_table('huoma_channel_data')->getCount(['channel_id' => $channel_id]);
    
    	// 每页数量
    	$lenght = 13;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($channelDataNum/$lenght);
    
    	// 上一页     
    	$prepage = $page-1;
    	if($page == 1){
    		$prepage=1;
    	}
    
    	// 下一页
    	$nextpage = $page+1;
    	if($page == $allpage){
    		$nextpage=$allpage;
    	}
    
    	// 获取当前channel_id来源数据，每页10个DESC排序
    	$getchannelDataList = $db->set_table('huoma_channel_data')->findAll(
    	    $conditions = ['channel_id' => $channel_id],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 渠道标题
        $channelTitle = json_decode(json_encode($db->set_table('huoma_channel')->find(['channel_id'=>$channel_id])))->channel_title;

        // 判断获取结果
    	if($getchannelDataList && $getchannelDataList > 0){
    	    
    	    // 将这个总数更新到channel_DataTotal字段
    	    $db->set_table('huoma_channel')->update(['channel_id' => $channel_id],['channel_DataTotal' => $channelDataNum]);
    	    
    	    // 统计设备数据量
            $Android_Total = $db->set_table('huoma_channel_data')->getCount(['channel_id' => $channel_id,'data_device' => 'Android']);
            $iOS_Total = $db->set_table('huoma_channel_data')->getCount(['channel_id' => $channel_id,'data_device' => 'iOS']);
            $Windows_Total = $db->set_table('huoma_channel_data')->getCount(['channel_id' => $channel_id,'data_device' => 'Windows']);
            $Linux_Total = $db->set_table('huoma_channel_data')->getCount(['channel_id' => $channel_id,'data_device' => 'Linux']);
            $MacOS_Total = $db->set_table('huoma_channel_data')->getCount(['channel_id' => $channel_id,'data_device' => 'Mac']);
            
            // 更新对应的Total
            $db->set_table('huoma_channel')->update(
                ['channel_id' => $channel_id],
                [
                    'Android_Total' => $Android_Total,
                    'iOS_Total' => $iOS_Total,
                    'Windows_Total' => $Windows_Total,
                    'Linux_Total' => $Linux_Total,
                    'MacOS_Total' => $MacOS_Total
                ]
            );
    	    
    	    // 获取成功
    		$result = array(
    		    'channelDataList' => $getchannelDataList,
    		    'channelDataNum' => $channelDataNum,
    		    'channel_id' => $channel_id,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'channel_title' => $channelTitle,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无数据',
                'channel_title' => $channelTitle
            );
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