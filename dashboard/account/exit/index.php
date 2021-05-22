<?php
header("Content-type:text/html;charset=utf-8");
session_start();
unset($_SESSION['huoma.dashboard']);
echo "<script>location.href=\"../../\";</script>";
?>