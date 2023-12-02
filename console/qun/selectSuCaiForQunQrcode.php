<?php

// 编码
header("Content-type:application/json");

// 群ID
$qun_id = trim(intval($_GET['qunid']));

// 素材ID
$sucai_id = trim(intval($_GET['sucai_id']));

// 过滤
if($qun_id && $sucai_id){
    
    // 执行
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 子码ID生成
        $zm_id = rand(100000,999999);
        
        // 数据库配置
    	include '../Db.php';
    	
        // 公共配置
    	include '../public/publicConfig.php';
    
    	// 实例化类
    	$db = new DB_API($config);
    	
        // 根据素材ID查询素材文件名
        $getSuCaiFileName = $db->set_table('huoma_sucai')->find(['sucai_id'=>$sucai_id]);
        $suCaiFileName = json_decode(json_encode($getSuCaiFileName,true))->sucai_filename;
    
    	// 添加群二维码
        $addQunQrcodeZm = [
            'qun_id' => $qun_id,
            'zm_id' => $zm_id,
            'zm_qrcode' => $imgPathUrl.$suCaiFileName,
            'zm_update_time' => date('Y-m-d H:i:s')
        ];
        $addQunQrcodeZmResult = $db->set_table('huoma_qun_zima')->add($addQunQrcodeZm);
        if($addQunQrcodeZmResult){
            
            // 成功
            $result = array(
                'code' => 200,
                'msg' => '添加成功'
            );
        }else{
            
            // 失败
            $result = array(
                'code' => 202,
                'msg' => '添加失败'
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
