<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     * 程序用途：获取素材列表
     * 最后维护日期：2023-06-03
     * 作者：TANKING
     * 博客：https://segmentfault.com/u/tanking
     * 该软件遵循MIT开源协议。
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 数据库配置
    	include '../Db.php';
    	
        // 当前登录的用户
        $LoginUser = $_SESSION["yinliubao"];
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 获取页码
    	@$page = $_GET['p']?$_GET['p']:1;
    	
    	// 获取总数
    	$suCaiNum = $db->set_table('huoma_sucai')->getCount(['sucai_upload_user'=>$LoginUser]);
    
    	// 每页数量
    	@$lenght = $_GET['num']?$_GET['num']:12;
    
    	// 每页第一行
    	$offset = ($page-1)*$lenght;
    
    	// 总页码
    	$allpage = ceil($suCaiNum/$lenght);
    
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
    	
        // 获取素材列表
        $getSuCaiList = $db->set_table('huoma_sucai')->findAll(
            $conditions=['sucai_upload_user'=>$LoginUser],
            $order='ID DESC',
            $fields=null,
            $limit=''.$offset.','.$lenght.''
        );
        
        // 获取结果
        if ($getSuCaiList) {
            
            // 返回结果（有数据）
        	$result = array(
    		    'suCaiList' => $getSuCaiList,
    		    'totalNum' => $suCaiNum,
    		    'allpage' => $allpage,
    		    'prepage' => $prepage,
    		    'nextpage' => $nextpage,
    		    'code' => 200,
    		    'msg' => '获取成功'
        	);
            
        }else{
            
            // 返回结果（没有数据）
        	$result = array(
    		    'code' => 204,
    		    'msg' => '素材库空空如也~'
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