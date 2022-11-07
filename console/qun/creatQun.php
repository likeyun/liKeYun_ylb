<?php
    
    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     */

	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        // 接收参数
        $qun_title = trim($_POST['qun_title']);
        $qun_rkym = trim($_POST['qun_rkym']);
        $qun_ldym = trim($_POST['qun_ldym']);
        $qun_dlym = trim($_POST['qun_dlym']);
        $qun_creat_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($qun_title) || $qun_title == '' || $qun_title == null || !isset($qun_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '群标题未设置'
            );
        }else if(empty($qun_rkym) || $qun_rkym == '' || $qun_rkym == null || !isset($qun_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($qun_ldym) || $qun_ldym == '' || $qun_ldym == null || !isset($qun_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($qun_dlym) || $qun_dlym == '' || $qun_dlym == null || !isset($qun_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else{
            
            // 群ID生成
            $qun_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 数据库huoma_qun表
        	$huoma_qun = $db->set_table('huoma_qun');
        	
            // 随机生成qun_key
            function creatKey($length){
                $keyMember = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }
        
        	// 插入数据库
            $creatQun_Sql = [
                'qun_title'=>$qun_title,
                'qun_rkym'=>$qun_rkym,
                'qun_ldym'=>$qun_ldym,
                'qun_dlym'=>$qun_dlym,
                'qun_creat_user'=>$qun_creat_user,
                'qun_key' => creatKey(5),
                'qun_id'=>$qun_id
            ];
            $Result_creatQun_Sql = $huoma_qun->add($creatQun_Sql);
            if($Result_creatQun_Sql){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'msg' => '创建成功'
                );
            }else{
                
                // 失败
                $result = array(
                    'code' => 202,
                    'msg' => '创建失败'
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