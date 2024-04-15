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
        $kami_title = trim($_POST['kami_title']);
        $kami_type = trim($_POST['kami_type']);
        $kami_create_user = trim($_SESSION["yinliubao"]);
        
        // 过滤参数
        if(empty($kami_title) || !isset($kami_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '项目标题未填写'
            );
        }else if(empty($kami_type) || !isset($kami_type)){
            
            $result = array(
                'code' => 203,
                'msg' => '项目类型未选择'
            );
        }else{
            
            // ID生成
            $kami_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 随机生成Key
            function creatKey($length){
                $keyMember = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }
        
        	// 参数
            $creatKamiProjectParams = [
                'kami_id' => $kami_id,
                'kami_title' =>$kami_title,
                'kami_type' => $kami_type,
                'kami_create_user' => $kami_create_user,
                'kami_key' => creatKey(4)
            ];
            
            // 执行SQL
            $creatKamiProject = $db->set_table('ylb_kami')->add($creatKamiProjectParams);
            
            // 执行结果
            if($creatKamiProject){
                
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