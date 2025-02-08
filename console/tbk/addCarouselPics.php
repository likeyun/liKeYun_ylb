<?php
    
	// 页面编码
	header("Content-type:application/json");
	
	// 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 当前登录的用户
        $loginUser = $_SESSION["yinliubao"];
        
        // 已登录
        $Carousel_id = trim($_POST['Carousel_id']);
        $pic_url = trim($_POST['pic_url']);
        $pic_desc = trim($_POST['pic_desc']);
        $show_copy_btn = trim($_POST['show_copy_btn']);
        
        // 过滤参数
        if(empty($Carousel_id) || !isset($Carousel_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '添加失败，参数缺失！'
            );
        }else if(empty($pic_url) || !isset($pic_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '请上传图片'
            );
        }else if(empty($pic_desc) || !isset($pic_desc)){
            
            $result = array(
                'code' => 203,
                'msg' => '请填写图片的描述文字'
            );
        }else if(contains_js($pic_desc)){
            
            $result = array(
                'code' => 203,
                'msg' => '图片描述文字禁止包含js代码，iframe等禁止使用的标签'
            );
        }else{
            
            // ID生成
            $pic_id = '10'.mt_rand(100000,999999);
            
            // 数据库配置
        	include '../Db.php';
        
        	// 实例化类
        	$db = new DB_API($config);
        
        	// 参数
            $addParams = [
                'pic_id' => $pic_id,
                'Carousel_id' => $Carousel_id,
                'pic_url' => $pic_url,
                'pic_desc' => $pic_desc,
                'show_copy_btn' => $show_copy_btn,
                'add_user' => $loginUser,
            ];
            
            // 添加
            $addSQL = $db->set_table('ylb_CarouselSPA_pics')->add($addParams);
            
            if($addSQL){
                
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
        }
        
    }else{
        
        // 未登录
        $result = array(
            'code' => 201,
            'msg' => '未登录'
        );
    }
    
    // 过滤参数
    function contains_js($pic_desc) {
        $pattern = '/(script|iframe|on\w+|javascript:|eval|alert|expression|document\.|window\.|setTimeout|setInterval)/i';
        return preg_match($pattern, $pic_desc) ? true : false;
    }

	// 输出JSON
	echo json_encode($result,JSON_UNESCAPED_UNICODE);
	
?>