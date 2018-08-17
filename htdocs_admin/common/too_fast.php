<?php

header("content-type:text/html; charset=utf-8");
?>

<html>
    <head>

    </head>
    <body>
    <div style="width: 100%; text-align: center; margin: 20px; font-size: 18px;">手速太快了，歇一会儿再访问吧！</div>
    <div style="width: 100%; text-align: center; margin: 20px; font-size: 14px;">如果正常操作看到此页面，请联系技术部</div>
    <div style="width: 100%; text-align: center; margin: 10px; font-size: 16px;"><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">返回上一页</a></div>
    </body>
</html>

