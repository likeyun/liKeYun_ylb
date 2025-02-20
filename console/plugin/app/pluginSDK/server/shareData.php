<?php

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $data_id = trim(intval($_GET['data_id']));
        
        // 过滤参数
        if(empty($data_id) || !isset($data_id)){
            
            // 非法请求
            $result = array(
			    'code' => 203,
                'msg' => '非法请求'
		    );
        }else{
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 获取详情
            $getDataInfo = $db->set_table('ylbPlugin_sdk')->find(['data_id' => $data_id]);
            
            // 返回数据
            if($getDataInfo){
                
                // 短链域名
                $data_dlym = $getDataInfo['data_dlym'];
                
                // 入口域名
                $data_rkym = $getDataInfo['data_rkym'];
                
                // 短网址Key
                $data_key = $getDataInfo['data_key'];
                
                // 拼接短链接
                $shortUrl = $data_dlym.'/sdk/'.$data_key;
                
                // 拼接长链接
                // 获取当前请求的URI
                $currentUri = $_SERVER["REQUEST_URI"];
                
                // 解析URI并构建根路径
                $parsedUrl = parse_url($currentUri);
                $pathComponents = explode('/', $parsedUrl['path']);
                
                // 去掉最后六个路径部分
                $rootPathComponents = array_slice($pathComponents, 0, -6);
                $rootPath = implode('/', $rootPathComponents);
                
                // 路径修改指引：
                // 1. 如果不希望使用 common 这个目录，请自行修改下方的 common
                // 2. 修改完成后，还需去引流宝根目录创建你自己的目录，并且将 common 里面的 sdkdata 整个目录复制到你新建的目录内
                // 3. 还需要修改短网址调度器里面的目录，在 /s/sdkdata.php 里面修改，具体修改的位置里面有说
                
                // 构建完整的URL
                $longUrl = $data_rkym . $rootPath . '/common/sdkdata/rkpage/?pid=' . base64_encode($data_id) . '&dkey=' . $data_key;
                
                // 返回数据
                $result = array(
        		    'code' => 200,
        		    'msg' => '获取成功',
        		    'shortUrl' => $shortUrl,
        		    'longUrl' => $longUrl.'#landpage',
        		    'qrcodeUrl' => $longUrl.'#qrcode'
    		    );
            }else{
                
                // 无结果
                $result = array(
        		    'code' => 204,
        		    'msg' => '获取失败'
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