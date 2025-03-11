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
        $data_id = trim($_POST['data_id']);
        
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
        }else if(empty($data_id) || !isset($data_id)){
            
            $result = array(
                'code' => 203,
                'msg' => '非法请求~'
            );
        }else{
            
            // 当前登录的用户
            $LoginUser = $_SESSION["yinliubao"];
            
            // 数据库配置
            include '../../../../Db.php';
        
            // 实例化类
            $db = new DB_API($config);
            
            // 验证当前要编辑的 data_id 的创建者是否为当前登录的用户
            $getCreatUser = $db->set_table('ylbPlugin_wxdmQk')->find(['data_id' => $data_id]);
            $data_create_user = $getCreatUser['data_create_user'];
            
            // 用户一致：允许操作
            if($data_create_user == $LoginUser){
                
                // 需更新的字段
                $updateData = [
                    'data_title' => $data_title,
                    'data_jumplink' => $data_jumplink,
                    'data_dxccym' => $data_dxccym
                ];
                
                // 执行更新
                $updateSQL = $db->set_table('ylbPlugin_wxdmQk')->update(['data_id' => $data_id, 'data_create_user' => $data_create_user],$updateData);
                if($updateSQL){
                    
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