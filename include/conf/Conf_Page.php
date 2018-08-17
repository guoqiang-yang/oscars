<?php
/**
 * 页面列表
 */
class Conf_Page
{
	public static $modules = array(
		'setting' => array(
			'name' => '首页',
			'pages' => array(
				array('name'=>'账号首页', 'url'=>'/setting/index.php', 'page' => 'index', 'icon' => 'people'),
				array('name'=>'信息设置', 'url'=>'/setting/base.php', 'page' => 'base', 'icon' => 'set'),
				array('name'=>'修改密码', 'url'=>'/setting/chgpwd.php', 'page' => 'chgpwd', 'icon' => 'key'),
				array('name'=>'账号升级', 'url'=>'/setting/upgrade.php', 'page' => 'upgrade', 'icon' => 're'),
				array('name'=>'使用说明', 'url'=>'/setting/help.php', 'page' => 'help', 'icon' => 'list'),
			)
		),
		'agent' => array(
			'name' => '代理管理',
			'limit' => 'agent',
			'pages' => array(
				array('name'=>'业务管理', 'url'=>'/agent/business.php', 'page' => 'business', 'icon' => 'people'),
				array('name'=>'客户资源', 'url'=>'/agent/customer.php', 'page' => 'customer', 'icon' => 'customer'),
				array('name'=>'充值管理', 'url'=>'/agent/recharge.php?status=1', 'page' => 'recharge', 'icon' => 'key'),
				array('name'=>'财务管理', 'url'=>'/agent/finance.php', 'page' => 'finance', 'icon' => 'list'),
			),
		),
		'shop' => array(
			'name' => '微网站',
			'pages' => array(
				array('name'=>'基本设置', 'url'=>'/shop/setting.php', 'page' => 'setting', 'icon' => 'set'),
				array('name'=>'宣传信息', 'url'=>'/shop/intro.php', 'page' => 'intro', 'icon' => 're'),
				array('name'=>'信息中心', 'url'=>'/shop/news.php', 'page' => 'news', 'icon' => 'event'),
				array('name'=>'产品服务', 'url'=>'/shop/product.php', 'page' => 'product', 'icon' => 'material'),
				array('name'=>'模板选择', 'url'=>'/shop/template.php', 'page' => 'template', 'icon' => 'list'),
			)
		),
		'promotion' => array(
			'name' => '推广+',
			'pages' => array(
				array('name'=>'微信', 'url'=>'/promotion/weixin.php', 'page' => 'weixin', 'icon' => 're'),
				array('name'=>'新浪微博', 'url'=>'/promotion/weibo.php', 'page' => 'weibo', 'icon' => 're'),
				array('name'=>'腾讯微博', 'url'=>'/promotion/qqweibo.php', 'page' => 'qqweibo', 'icon' => 're'),
				array('name'=>'QQ空间', 'url'=>'/promotion/qzone.php', 'page' => 'qzone', 'icon' => 're'),
				array('name'=>'人人网', 'url'=>'/promotion/renren.php', 'page' => 'renren', 'icon' => 're'),
			)
		),
		'content' => array(
			'name' => '内容库',
			'pages' => array(
				array('name'=>'内容库', 'url'=>'/content/article.php', 'page' => 'article', 'icon' => 'write'),
				array('name'=>'图片库', 'url'=>'/content/picture.php', 'page' => 'picture', 'icon' => 'list'),
				array('name'=>'广告库', 'url'=>'/content/gg.php', 'page' => 'gg', 'icon' => 'list'),
				array('name'=>'问答库', 'url'=>'/content/qa.php', 'page' => 'qa', 'icon' => 'list'),
				array('name'=>'搜词库', 'url'=>'/content/keyword.php', 'page' => 'keyword', 'icon' => 'list'),
				array('name'=>'活动库', 'url'=>'/content/event.php', 'page' => 'event', 'icon' => 'list'),
			)
		),
		'message' => array(
			'name' => '微互动',
			'pages' => array(
				array('name'=>'微信', 'url'=>'/message/weixin.php', 'page' => 'weixin', 'icon' => 'msg'),
				array('name'=>'新浪微博', 'url'=>'/message/weibo.php', 'page' => 'weibo', 'icon' => 'msg'),
				array('name'=>'腾讯微博', 'url'=>'/message/qqweibo.php', 'page' => 'qqweibo', 'icon' => 'msg'),
				array('name'=>'QQ空间', 'url'=>'/message/qzone.php', 'page' => 'qzone', 'icon' => 'msg'),
				array('name'=>'人人网', 'url'=>'/message/renren.php', 'page' => 'renren', 'icon' => 'msg'),
			)
		),
		'customer' => array(
			'name' => '微crm',
			'pages' => array(
				array('name'=>'客户管理', 'url'=>'/customer/customer.php', 'page' => 'customer', 'icon' => 'customer'),
				array('name'=>'微信', 'url'=>'/customer/weixin.php', 'page' => 'weixin', 'icon' => 'customer'),
				array('name'=>'新浪微博', 'url'=>'/customer/weibo.php', 'page' => 'weibo', 'icon' => 'customer'),
				array('name'=>'腾讯微博', 'url'=>'/customer/qqweibo.php', 'page' => 'qqweibo', 'icon' => 'customer'),
				array('name'=>'QQ空间', 'url'=>'/customer/qzone.php', 'page' => 'qzone', 'icon' => 'customer'),
				array('name'=>'人人网', 'url'=>'/customer/renren.php', 'page' => 'renren', 'icon' => 'customer'),
			),
		),
		'service' => array(
			'name' => '微服务',
			'pages' => array(
				array('name'=>'订单', 'url'=>'/service/order.php?status=1', 'page' => 'order', 'icon' => 'fans'),
				array('name'=>'预约', 'url'=>'/service/book.php?status=1', 'page' => 'book', 'icon' => 'list'),
			)
		),
		'share' => array(
			'name' => '微享会',
			'limit' => 'common',
			'pages' => array(
				array('name'=>'赚取积分', 'url'=>'/share/articles.php', 'page' => 'articles', 'icon' => 'list'),
				array('name'=>'我的积分', 'url'=>'/share/score.php', 'page' => 'score', 'icon' => 'fans'),
			)
		),
	);
}