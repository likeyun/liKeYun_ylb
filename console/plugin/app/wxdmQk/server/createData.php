<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $data_title = trim($_POST['data_title']);
        $data_dxccym = trim($_POST['data_dxccym']);
        $data_jumplink = trim($_POST['data_jumplink']);
        $data_create_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($data_title) || !isset($data_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($data_dxccym)){
            
            $result = array(
                'code' => 203,
                'msg' => '请选择对象存储域名'
            );
        }else if(empty($data_jumplink) || !isset($data_jumplink)){
            
            $result = array(
                'code' => 203,
                'msg' => '请填写跳转地址'
            );
        }else{
            
            // ID生成
            $data_id = '10'.rand(101112,989898);
            
            // 数据库配置
        	include '../../../../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 参数
            $datas = [
                'data_id' => $data_id,
                'data_title' => $data_title,
                'data_key' => createKey(5),
                'data_jumplink' => $data_jumplink,
                'data_dxccym' => $data_dxccym,
                'data_create_user' => $data_create_user
            ];

            // 执行SQL
            $createSQL = $db->set_table('ylbPlugin_wxdmQk')->add($datas);
            
            if($createSQL){
                
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
    
    // 生成Key
    function createKey($length){
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);
        $rands= substr($randStr,0,$length);
        return $rands;
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>