<?php
header("Content-type:text/html;charset=utf-8");
session_start();
unset($_SESSION['www.likeyunba.com']);
echo "<script>location.href=\"../../index.html\";</script>";
?>