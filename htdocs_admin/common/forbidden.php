<?php

header("content-type:text/html; charset=utf-8");
?>

<html>
    <head>

    </head>
    <body>
    <div style="width: 100%; text-align: center; margin: 20px; font-size: 18px;">您无权访问该页面！</div>
    <div style="width: 100%; text-align: center; margin: 10px; font-size: 16px;"><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">返回上一页</a></div>
    </body>
</html>

