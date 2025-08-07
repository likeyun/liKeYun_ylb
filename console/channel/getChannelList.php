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
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 2025-07-16
        // 新增字段
    	$checkExitsSQL = "SHOW COLUMNS FROM huoma_channel LIKE 'channel_limit'";
        $checkExits = $db->set_table('huoma_channel')->findSql($checkExitsSQL);
        if(!$checkExits) {
            
            $Add_channel_limit = "ALTER TABLE huoma_channel ADD channel_limit int(1) DEFAULT '1' COMMENT '访问限制'";
            $db->set_table('huoma_channel')->findSql($Add_channel_limit);
            
            $Add_is_mzfwxz = "ALTER TABLE huoma_channel ADD is_mzfwxz int(1) DEFAULT '1' COMMENT '命中访问限制规则的时候'";
            $db->set_table('huoma_channel')->findSql($Add_is_mzfwxz);
            
            $Add_mzfwxz_url = "ALTER TABLE huoma_channel ADD mzfwxz_url text DEFAULT NULL COMMENT '命中跳转url'";
            $db->set_table('huoma_channel')->findSql($Add_mzfwxz_url);
            
            $Add_channel_beizhu_ht = "ALTER TABLE huoma_channel ADD channel_beizhu_ht varchar(32) DEFAULT NULL COMMENT '后台备注'";
            $db->set_table('huoma_channel')->findSql($Add_channel_beizhu_ht);
        }
    	
    	// 数据库huoma_channel表
    	$huoma_channel = $db->set_table('huoma_channel');
    
    	// 获取总数
    	$channelNum = $huoma_channel->getCount(['channel_creat_user'=>$LoginUser]);
    
    	// 每页数量
    	$lenght = 10;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($channelNum/$lenght);
    
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
    
    	// 获取当前登录用户创建的渠道码，每页10个DESC排序
    	$getchannelList = $huoma_channel->findAll(
    	    $conditions = ['channel_creat_user' => $LoginUser],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 判断获取结果
    	if($getchannelList && $getchannelList > 0){
    	    
    	    // 获取成功
    		$result = array(
    		    'channelList' => $getchannelList,
    		    'channelNum' => $channelNum,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'allpage' => $allpage,
    		    'page' => $page,
    		    'code' => 200,
    		    'msg' => '获取成功'
    		);
    	}else{
    	    
    	    // 获取失败
            $result = array(
                'code' => 204,
                'msg' => '暂无渠道码'
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