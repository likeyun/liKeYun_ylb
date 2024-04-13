<?php

    /**
     * 状态码说明
     * 状态码：200 操作成功
     * 其它状态码自己定义就行
     * 源码用途：提交配置，更新alipayShangJin.json
     * 作者：TANKING
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        $alipayShangJinSPA_title = trim($_POST['alipayShangJinSPA_title']);
        $alipayShangJinSPA_desc = trim($_POST['alipayShangJinSPA_desc']);
        $alipayShangJinSPA_uid = trim($_POST['alipayShangJinSPA_uid']);
        $alipayShangJinSPA_bgImg = trim($_POST['alipayShangJinSPA_bgImg']);
        $alipayShangJinSPA_token = trim($_POST['alipayShangJinSPA_token']);
        $alipayShangJinSPA_hbm = trim($_POST['alipayShangJinSPA_hbm']);
        
        // 创建一个数组用于存储错误信息
        $errors = [];
        
        // 使用switch语句验证每个变量是否为空
        switch (true) {
            case empty($alipayShangJinSPA_title):
                $errors[] = "单页标题不能为空";
                break;
            
            case empty($alipayShangJinSPA_desc):
                $errors[] = "单页摘要不能为空";
                break;
        
            case empty($alipayShangJinSPA_uid):
                $errors[] = "支付宝商家uid不能为空";
                break;
        
            case empty($alipayShangJinSPA_bgImg):
                $errors[] = "背景图未上传";
                break;
        
            case empty($alipayShangJinSPA_token):
                $errors[] = "支付宝赏金二维码Token不得为空";
                break;
                
            case empty($alipayShangJinSPA_hbm):
                $errors[] = "搜索码未填写";
                break;
        }
        
        // 检查是否有任何错误，并进行相应的处理
        if (!empty($errors)) {
            
            // 有错误时输出错误信息
            foreach ($errors as $error) {
                
                $result = array(
			        'code' => 201,
                    'msg' => $error
		        );
            }
        } else {
            
            // 所有变量都不为空时，执行其他逻辑
            // 读取JSON文件内容
            $jsonFile = 'alipayShangJin.json';
            $jsonData = file_get_contents($jsonFile);
            
            // 解码JSON数据
            $data = json_decode($jsonData, true);
            
            // 修改键值内容
            $data['alipayShangJinSPA_title'] = $alipayShangJinSPA_title;
            $data['alipayShangJinSPA_desc'] = $alipayShangJinSPA_desc;
            $data['alipayShangJinSPA_uid'] = $alipayShangJinSPA_uid;
            $data['alipayShangJinSPA_bgImg'] = $alipayShangJinSPA_bgImg;
            $data['alipayShangJinSPA_token'] = $alipayShangJinSPA_token;
            $data['alipayShangJinSPA_hbm'] = $alipayShangJinSPA_hbm;
            
            // 编码为JSON格式
            $newJsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            // 写回JSON文件
            file_put_contents($jsonFile, $newJsonData);
            
            $result = array(
			    'code' => 200,
                'msg' => '已完成配置'
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