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
        // 接收参数
    	@$page = $_GET['p']?$_GET['p']:1;
    	$channel_id = trim($_GET['channel_id']);
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    
    	// 数据库huoma_channel_data表
    	$huoma_channel_data = $db->set_table('huoma_channel_data');
    
    	// 获取总数
    	$channelDataNum = $huoma_channel_data->getCount(['channel_id'=>$channel_id]);
    
    	// 每页数量
    	$lenght = 10;
    
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
    	$getchannelDataList = $huoma_channel_data->findAll(
    	    $conditions = ['channel_id' => $channel_id],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 判断获取结果
    	if($getchannelDataList && $getchannelDataList > 0){
    	    
    	    // 根据channel_id获取channel_title
    	    $huoma_channel = $db->set_table('huoma_channel');
            $getChanneltitle = ['channel_id'=>$channel_id];
            $getChanneltitleResult = $huoma_channel->find($getChanneltitle);
    	    
    	    // 获取成功
    		$result = array(
    		    'channelDataList' => $getchannelDataList,
    		    'channelDataNum' => $channelDataNum,
    		    'channel_id' => $channel_id,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'channel_title' => json_decode(json_encode($getChanneltitleResult))->channel_title,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无数据'
            );
    	}
    }else{
        
        // 未登录
        $result = array(
			'code' => 201,
            'msg' => '未登录或登录过期'
		);
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>