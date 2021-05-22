<?php
header("Content-type:text/html;charset=utf-8");
if(empty($_GET['content'])){
  echo "请传入需要生成二维码的内容（文本、链接）"; // 判断 GET 到的 qr 如果是空的话返回的文字是什么
}else{
  require_once __DIR__ . '/phpqrcode/phpqrcode.php';
  QRcode::png($_GET['content'], false, 'L', 10, 1); // 这里把 GET 到的 qr 的值作为二维码内容
}