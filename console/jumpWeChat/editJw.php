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
        $jw_title = trim($_POST['jw_title']);
        $jw_yccym = trim($_POST['jw_yccym']);
        $jw_icon = trim($_POST['jw_icon']);
        $jw_bgimg = trim($_POST['jw_bgimg']);
        $jw_url = trim($_POST['jw_url']);
        $jw_id = trim($_POST['jw_id']);
        
        // 过滤参数
        if(empty($jw_title) || !isset($jw_title)){
            
            $result = array(
                'code' => 203,
                'msg' => '标题未填写'
            );
        }else if(empty($jw_yccym) || !isset($jw_yccym)){
            
            $result = array(
                'code' => 203,
                'msg' => '云储存域名未选择'
            );
        }else if(empty($jw_icon) || !isset($jw_icon)){
            
            $result = array(
                'code' => 203,
                'msg' => '图标未上传'
            );
        }else if(empty($jw_bgimg) || !isset($jw_bgimg)){
            
            $result = array(
                'code' => 203,
                'msg' => '背景图片未上传'
            );
        }else if(empty($jw_url) || !isset($jw_url)){
            
            $result = array(
                'code' => 203,
                'msg' => '目标链接未填写'
            );
        }else if(empty($jw_id) || !isset($jw_id)){
            
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
            
            // 验证当前要编辑的jw_id的发布者是否为当前登录的用户
            $getJwCreatUser = $db->set_table('ylb_jumpWeChat')->find(['jw_id'=>$jw_id]);
            $jw_create_user = json_decode(json_encode($getJwCreatUser))->jw_create_user;
            
            // 用户一致：允许操作
            if($jw_create_user == $LoginUser){
                
                // jw_token
                $jw_token = MD5($jw_id . $jw_title . $jw_url . $jw_create_user);
                
                // 需更新的字段
                $updateData = [
                    'jw_title'=>$jw_title,
                    'jw_yccym'=>$jw_yccym,
                    'jw_icon'=>$jw_icon,
                    'jw_bgimg'=>$jw_bgimg,
                    'jw_url'=>$jw_url,
                    'jw_create_user'=>$jw_create_user,
                    'jw_token'=>$jw_token
                ];
                
                // 更新条件
                $updateCondition = [
                    'jw_id' => $jw_id,
                    'jw_create_user' => $LoginUser
                ];
                
                // 执行更新
                $updateSQL = $db->set_table('ylb_jumpWeChat')->update($updateCondition,$updateData);
                if($updateSQL){
                    
                    // 更新成功
                    $result = array(
                        'code' => 200,
                        'msg' => '更新成功'
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
            'msg' => '未登录或登录过期'
        );
    }

    // 输出JSON
    echo json_encode($result,JSON_UNESCAPED_UNICODE);
    
?>