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
        
        // 当前登录的用户
        $loginUser = $_SESSION["yinliubao"];
        
        // 已登录
        $multiSPA_title = trim($_POST['multiSPA_title']);
        $multiSPA_rkym = trim($_POST['multiSPA_rkym']);
        $multiSPA_ldym = trim($_POST['multiSPA_ldym']);
        $multiSPA_dlym = trim($_POST['multiSPA_dlym']);
        $multiSPA_img = trim($_POST['multiSPA_img']);
        $multiSPA_project = trim($_POST['multiSPA_project']);
        
        // 过滤参数
        if(empty($multiSPA_title) || !isset($multiSPA_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($multiSPA_project) || !isset($multiSPA_project)){
            
            $result = array(
                'code' => 203,
                'msg' => '请按照格式编写项目'
            );
        }else if(empty($multiSPA_rkym) || !isset($multiSPA_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($multiSPA_ldym) || !isset($multiSPA_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($multiSPA_dlym) || !isset($multiSPA_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else{
            
            // ID生成
            $multiSPA_id = rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        	
            // 随机生成multiSPA_key
            function creatKey($length){
                $keyMember = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz1234567890';
                $keyStr = str_shuffle($keyMember);
                $keys = substr($keyStr,0,$length);
                return $keys;
            }
        
        	// 参数
            $createMultiSPAParams = [
                'multiSPA_id'=>$multiSPA_id,
                'multiSPA_title'=>$multiSPA_title,
                'multiSPA_rkym'=>$multiSPA_rkym,
                'multiSPA_ldym'=>$multiSPA_ldym,
                'multiSPA_dlym'=>$multiSPA_dlym,
                'multiSPA_img'=>$multiSPA_img,
                'multiSPA_project'=>$multiSPA_project,
                'create_user'=>$loginUser,
                'multiSPA_key' => creatKey(4)
            ];
            
            // 创建
            $createMultiSPA = $db->set_table('huoma_tbk_mutiSPA')->add($createMultiSPAParams);
            
            if($createMultiSPA){
                
                // 创建成功
                $result = array(
                    'code' => 200,
                    'msg' => '创建成功'
                );
            }else{
                
                // 创建失败
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