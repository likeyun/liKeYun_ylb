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
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
        
        // 数据库配置
    	include '../Db.php';
        
        // 实例化类
    	$db = new DB_API($config);
    	
    	// 检查是否存在【ylb_CarouselSPA、ylb_CarouselSPA_pics】这两个表
        // 该检查是为了自动完成升级
        // 2025年2月8日加入
        
        // 需要操作的表
        $table_1 = 'ylb_CarouselSPA';
        $table_2 = 'ylb_CarouselSPA_pics';
        
        // 检查表 1 是否存在
        $checkExits_table_1 = "SHOW TABLES LIKE '$table_1'";
        $check_table_1 = $db->set_table($table_1)->findSql($checkExits_table_1);
        
        // 检查表 2 是否存在
        $checkExits_table_2 = "SHOW TABLES LIKE '$table_2'";
        $check_table_2 = $db->set_table($table_2)->findSql($checkExits_table_2);
        
        if(!$check_table_1) {
            
            // 如果不存在
            // 创建表 1
            $table_1_create = "CREATE TABLE `$table_1` (
              `id` int(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `Carousel_id` int(9) DEFAULT NULL COMMENT '单页ID',
              `Carousel_title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '标题',
              `Carousel_dlym` text COMMENT '短链域名',
              `Carousel_rkym` text COMMENT '入口域名',
              `Carousel_ldym` text COMMENT '落地域名',
              `Carousel_pv` int(9) NOT NULL DEFAULT '0' COMMENT '访问量',
              `Carousel_key` varchar(6) DEFAULT NULL COMMENT '短网址Key',
              `Carousel_create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `Carousel_status` int(1) NOT NULL DEFAULT '1' COMMENT '状态（1正常 2停用）',
              `Carousel_create_user` varchar(32) DEFAULT NULL COMMENT '创建者'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='轮播单页表'";
            
            // 执行
            $db->set_table($table_1)->findSql($table_1_create);
        }
        
        if(!$check_table_2) {
            
            // 如果不存在
            // 创建表 2
            $table_2_create = "CREATE TABLE `$table_2` (
              `id` int(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '自增ID',
              `Carousel_id` int(9) DEFAULT NULL COMMENT '单页ID',
              `pic_id` int(9) DEFAULT NULL COMMENT '图片id',
              `pic_url` text COMMENT '图片地址',
              `pic_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '图片描述文案',
              `show_copy_btn` int(1) DEFAULT NULL COMMENT '复制按钮显示状态 1显示 2隐藏',
              `add_user` varchar(32) DEFAULT NULL COMMENT '添加人'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='轮播图片表'";
            
            // 执行
            $db->set_table($table_2)->findSql($table_2_create);
        }
    	
    	// 获取当前登录用户创建的总数
    	$CarouselSPANum = $db->set_table('ylb_CarouselSPA')->getCount(['Carousel_create_user' => $LoginUser]);
    
    	// 每页数量
    	$lenght = 12;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($CarouselSPANum/$lenght);
    
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
    
    	// 获取当前登录用户创建的中间页
    	$getCarouselSPAList = $db->set_table('ylb_CarouselSPA')->findAll(
    	    $conditions = ['Carousel_create_user' => $LoginUser],
    	    $order = 'ID DESC',
    	    $fields = null,
    	    $limit = ''.$offset.','.$lenght.''
    	);
    	
        // 判断获取结果
    	if($getCarouselSPAList && $getCarouselSPAList > 0){
    	    
    	    // 获取成功
    		$result = array(
    		    'carouselSPAList' => $getCarouselSPAList,
    		    'carouselSPANum' => $CarouselSPANum,
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
                'msg' => '暂无单页'
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