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
        
        // 获取到表单提交的数据
        $input1 = trim($_POST['input1']);
        $imgurl = trim($_POST['imgurl']);
        $select1 = trim($_POST['select1']);
        
        // 创建一个数组用于存储错误信息
        $errors = [];
        
        // 使用switch语句验证每个变量是否为空
        switch (true) {
            case empty($input1):
                $errors[] = "input1（表单1）不能为空";
                break;
            
            case empty($imgurl):
                $errors[] = "imgurl（上传图片）不能为空";
                break;
        
            case empty($select1):
                $errors[] = "select1（下拉菜单）不能为空";
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
            
            // 这里编写你的逻辑，例如插入数据库
            
            // 所有数据均不为空
            $result = array(
			    'code' => 200,
                'msg' => '你提交的数据；表单1：' . $input1 . '；上传图片：' . $imgurl . '；下拉菜单：' . $select1
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