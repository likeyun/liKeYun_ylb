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
        $kf_title = trim($_POST['kf_title']);
        $kf_rkym = trim($_POST['kf_rkym']);
        $kf_ldym = trim($_POST['kf_ldym']);
        $kf_dlym = trim($_POST['kf_dlym']);
        $kf_model = trim($_POST['kf_model']);
        $kf_onlinetimes = trim($_POST['kf_onlinetimes']);
        $kf_creat_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($kf_title) || !isset($kf_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '客服标题未设置'
            );
        }else if(empty($kf_rkym) || !isset($kf_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($kf_ldym) || !isset($kf_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($kf_dlym) || !isset($kf_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($kf_model) || !isset($kf_model)){
            
            $result = array(
                'code' => 203,
                'msg' => '循环模式未选择'
            );
        }else{
            
            // 客服ID生成
            $kf_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
            // 随机生成kf_key
            function creatKey($length){
                $keyMember = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }

        	// 参数
            $createKfParams = [
                'kf_title'=>$kf_title,
                'kf_today_pv'=>'{"pv":0,"date":"'.date("Y-m-d").'"}',
                'kf_rkym'=>$kf_rkym,
                'kf_ldym'=>$kf_ldym,
                'kf_dlym'=>$kf_dlym,
                'kf_model'=>$kf_model,
                'kf_onlinetimes'=>$kf_onlinetimes,
                'kf_creat_user'=>$kf_creat_user,
                'kf_key' => creatKey(5),
                'kf_id'=>$kf_id
            ];
            
            // 执行SQL
            $createKf = $db->set_table('huoma_kf')->add($createKfParams);
            
            if($createKf){
                
                // 成功
                $result = array(
                    'code' => 200,
                    'kf_title' => $kf_title,
                    'kf_id' => $kf_id,
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