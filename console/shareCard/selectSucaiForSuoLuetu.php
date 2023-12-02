<?php

// 编码
header("Content-type:application/json");

// 素材ID
$sucai_id = trim(intval($_GET['sucai_id']));

// 过滤
if($sucai_id){
    
    // 执行
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 数据库配置
    	include '../Db.php';
    	
        // 公共配置
    	include '../public/publicConfig.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 根据素材ID查询素材文件名
        $getSuCaiFileName = $db->set_table('huoma_sucai')->find(['sucai_id'=>$sucai_id]);
        $suCaiFileName = json_decode(json_encode($getSuCaiFileName,true))->sucai_filename;
                
        if($suCaiFileName){
            
            // 成功
            $result = array(
                'code' => 200,
                'msg' => '获取成功',
                'suoLuetuUrl' => $imgPathUrl.$suCaiFileName
            );
        }else{
            
            // 失败
            $result = array(
                'code' => 202,
                'msg' => '获取失败'
            );
        }
        
    }else{
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
}else{
    
    $result = array(
        'code' => 204,
        'msg' => '非法请求'
    );
}

// 输出JSON
echo json_encode($result,JSON_UNESCAPED_UNICODE);

?>
