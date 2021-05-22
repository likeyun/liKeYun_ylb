<?php
header("Content-type:application/json");
 
//获取原始文件名
$filename = $_FILES["file"]["name"];
 
//获取文件后缀名
$hzm = substr($filename,strpos($filename,"."));
 
//设置新文件名
$newfilename = date("Y-m-d")."-".rand(100,999);
 
// 允许上传的图片后缀
$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $filename);
$extension = end($temp);
if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/jpg")
|| ($_FILES["file"]["type"] == "image/pjpeg")
|| ($_FILES["file"]["type"] == "image/x-png")
|| ($_FILES["file"]["type"] == "image/png"))
&& ($_FILES["file"]["size"] < 2048000)   // 小于 2000 kb
&& in_array($extension, $allowedExts))
{
    if ($_FILES["file"]["error"] > 0)
    {
        $upload_result = array(
            "res" => 404,
            "msg" => "上传时发生错误"
        );
    }
    else
    {
    // 此处可以输出文件的详细信息
    if (file_exists("upload/" . $newfilename.$hzm))
        {
            //
        }
        else
        {
            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $newfilename.$hzm);
            $current_url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
            $current_wenjianjia = dirname($current_url);
            $upload_result = array(
                "errno" => 0,
                "res" => 400,
                "msg" => "上传成功",
                "path" => $current_wenjianjia."/upload/".$newfilename.$hzm,
                "data" => array(
                    array(
                        "url" => $current_wenjianjia."/upload/".$newfilename.$hzm,
                        "alt" => "",
                        "href" => ""
                    )
                )
            );
        }
    }
}
else
{
    $upload_result = array(
        "res" => 403,
        "msg" => "上传时发生错误"
    );
}
// 输出上传结果
echo json_encode($upload_result,true);
?>
