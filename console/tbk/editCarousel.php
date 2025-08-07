<?php

    /**
     * 状态码说明
     * 200 成功
     * 201 未登录
     * 202 失败
     * 203 空值
     * 204 无结果
     */

    // 页面编码
    header("Content-type:application/json");
    
    // 判断登录状态
    session_start();
    if(isset($_SESSION["yinliubao"])){
        
        // 已登录
        $Carousel_title = trim($_POST['Carousel_title']);
        $Carousel_rkym = trim($_POST['Carousel_rkym']);
        $Carousel_ldym = trim($_POST['Carousel_ldym']);
        $Carousel_dlym = trim($_POST['Carousel_dlym']);
        $Carousel_id = trim($_POST['Carousel_id']);
        
        // 过滤参数
        if(empty($Carousel_title) || !isset($Carousel_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($Carousel_rkym) || !isset($Carousel_rkym)){
            
            $result = array(
                'code' => 203,
                'msg' => '入口域名未选择'
            );
        }else if(empty($Carousel_ldym) || !isset($Carousel_ldym)){
            
            $result = array(
                'code' => 203,
                'msg' => '落地域名未选择'
            );
        }else if(empty($Carousel_dlym) || !isset($Carousel_dlym)){
            
            $result = array(
                'code' => 203,
                'msg' => '短链域名未选择'
            );
        }else if(empty($Carousel_id) || !isset($Carousel_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 验证当前要编辑的 Carousel_id 的创建者是否为当前登录的用户
            $checkCreatUser = $db->set_table('ylb_CarouselSPA')->find(['Carousel_id' => $Carousel_id]);
            $Carousel_create_user = $checkCreatUser['Carousel_create_user'];
            
            // 用户一致：允许操作
            if($Carousel_create_user == $LoginUser){
        
                // 需更新的字段
                $updateParams = [
                    'Carousel_title'=>$Carousel_title,
                    'Carousel_project'=>$Carousel_project,
                    'Carousel_rkym'=>$Carousel_rkym,
                    'Carousel_ldym'=>$Carousel_ldym,
                    'Carousel_dlym'=>$Carousel_dlym,
                    'Carousel_img'=>$Carousel_img
                ];
                
                // 执行更新
                $updateCarouselSQL = $db->set_table('ylb_CarouselSPA')->update(['Carousel_id' => $Carousel_id,'Carousel_create_user' => $LoginUser],$updateParams);
                if($updateCarouselSQL){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '已保存'
                    );
                }else{
                    
                    // 更新失败
                    $result = array(
                        'code' => 202,
                        'msg' => '更新失败'
                    );
                }
                
            }else{
                
                // 用户不一致
                $result = array(
                    'code' => 202,
                    'msg' => '非法操作'
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