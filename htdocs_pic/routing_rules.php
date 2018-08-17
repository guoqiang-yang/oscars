<?php

return array(
	// 首页
	array("/", "index.php", "GET"),

	// 图片
	array("/pic/:filename", "pic.php", "GET"),
    array("/wx_pic/:date/:filename", 'wx_pic.php', "GET"),
);

