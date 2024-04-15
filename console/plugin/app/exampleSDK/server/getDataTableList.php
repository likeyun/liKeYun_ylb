<?php

    /**
     * 状态码说明
     * 状态码：200 获取成功
     * 其它状态码自己定义就行
     * 源码用途：获取数据列表
     * 作者：TANKING
     */

	// 编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 我这里就不读取数据库了
        // 用下面示例数据
        $dataList = array(
            array(
                'news_id' => '10000',
                'news_title' => '一起来看双子座流星雨',
                'news_addtime' => '2023/12/12',
                'news_pv' => '4316511',
                'news_type' => '新闻',
                'news_status' => 1
            ),
            array(
                'news_id' => '10001',
                'news_title' => '董明珠怒斥孟羽童',
                'news_addtime' => '2023/12/13',
                'news_pv' => '3872553',
                'news_type' => '新闻',
                'news_status' => 1
            ),
            array(
                'news_id' => '10002',
                'news_title' => '东方甄选CEO道歉',
                'news_addtime' => '2023/12/14',
                'news_pv' => '3516699',
                'news_type' => '新闻',
                'news_status' => 1
            )
        );
        
        $result = array(
			'code' => 200,
            'msg' => '获取成功',
            'dataList' => $dataList
		);
    }else {
        
        $result = array(
			'code' => 201,
            'msg' => '未登录'
		);
    }
    
    // 输出JSON
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	
?>