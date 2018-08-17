<?php

/**
 * Exception异常码
 */
class Conf_Exception
{
	/**
	 * 异常定义
	 *
	 * 格式： 'exception tag' => array(errno, errmsg)
	 */
	public static $exceptions = array(

		//common
		'common:system error' => array('00001', '网络错误，请联系管理员或刷新重试'),
		'common:params error' => array('00002', '参数不全'),
		'common:permission denied' => array('00003', '您没有权限执行该操作'),
		'common:upload pic error' => array('00004', '图片上传错误'),
		'common:read pic info error' => array('00005', '解析图片格式错误'),
		'common:mobile format error' => array('00006', '手机格式不对'),
		'common:to login' => array('00007', '请登录'),
		'common:failure' => array('00010', '操作失败'),
		//customer user
		'user:user not exist' => array('10001', '用户不存在'),
		'user:password wrong' => array('10002', '密码错误'),
		'user:forbidden' => array('10003', '用户被禁用'),
		'user:captcha needed' => array('10004', '需要验证码'),
		'user:captcha wrong' => array('10005', '验证码错误'),
		'user:captcha to fast' => array('10006', '请稍后再重试'),
        'user:captcha error2' => array('10007', '验证码已失效，请重新获取'),
		'user:email taken' => array('10008', '邮箱已被占用'),
		'user:email format wrong' => array('10009', '邮箱格式错误'),
		'user:password empty' => array('10010', '密码为空'),
		'user:email empty' => array('10011', '邮箱为空'),
		'user:name empty' => array('10012', '用户名为空'),
		'user:password format error' => array('10014', '密码不能小于6个字母或数字'),
		'user:old password wrong' => array('10015', '原密码错误'),
		'user:mobile occupied' => array('10016', '该手机号已注册~'),
		'user:password simple' => array('10017', '密码不能是手机号后六位'),
		'user:modify user info fail' => array('10018', '修改用户信息失败'), //通用
		'user:password not same' => array('10019', '两次密码不一致'),
		'user:mobile format wrong' => array('10020', '手机号码格式不正确'),
		'user:status is not wrong' => array('10021', '用户状态异常'),
        'user:need modify password' => array('10022', '您还没设密码，请用手机号短信验证登录，然后设置密码'),
		//customer
		'customer:memberid empty' => array('10001', '会员编号为空'),
		'customer:name empty' => array('10001', '客户名称不能为空'),
		'customer:contact person name empty' => array('10001', '联系人姓名不能为空'),
		'customer:contact mobile' => array('10001', '联系人手机不能为空'),
		'customer:invalid customer id' => array('10001', '会员号错误'),
		'customer:empty sales_suid' => array('10001', '没有选择客服专员'),
		'customer:empty source' => array('10001', '请选择客户来源'),
		'customer:empty name' => array('10001', '名称不能为空'),
		'customer:address not belong to you' => array('20001', '不能删除该地址'),
        'customer:customer not belong to you' => array('20002', '非本人客户不能查看详情'),
        
        
		//shop-product
		'shop:empty product name' => array('60001', '商品名称不能为空'),
		'shop:empty cate1' => array('60001', '大类不能为空'),
		'shop:empty cate2' => array('60001', '小类不能为空'),
		'shop:empty cate3' => array('60001', '细类不能为空'),
		'shop:empty model name' => array('60001', '型号名称不能为空'),
		'shop:can not online when price zero' => array('60001', '价格为零时不能上线'),
		'shop:sku status error' => array('60001', '对应的sku不是上线状态'),
		'shop:product exists' => array('60001', '商品已存在'),
		'shop:only can delete offline product' => array('60001', '商品状态不是下架，不能删除'),
		//order
		'order:order not exist' => array('69999', '订单不存在'),
		'order:empty order id' => array('60001', '订单ID为空'),
		'order:empty phone' => array('60001', '订单联系电话为空'),
		'order:empty city' => array('60001', '请选择城市'),
		'order:empty district' => array('60001', '请选择城区'),
        'order:error address' => array('60001', '城区选择有误，请重新选择'),
		'order:empty area' => array('60001', '请选择范围'),
		'order:empty address' => array('60001', '请填写送货地址'),
        'order:exception address' => array('60001', '配送地址异常'),
		'order:empty payment_type' => array('60001', '请选择付款方式'),
		'order:delivery date too old' => array('60001', '配送日期不能早于今天'),
		'order:delivery date empty' => array('60001', '请选择配送日期'),
		'order:delivery time empty' => array('60001', '请选择配送时间'),
        'order:empty note' => array('60001', '请填写内部备注'),
		'Order: empty cart' => array('60001', '请先选购一些商品吧'),
		'Order:empty products' => array('60001', '商品列表不能为空'),
		'order:vnum cannot be big than num' => array('60001', '空采空配数量不能大于总数量'),
		'order:empty driver' => array('60001', '司机信息不能为空'),
		'order:status error' => array('60001', '订单状态已改变，不能取消订单'),
		'order:has picked' => array('60001', '订单已经开始拣货，不能回退订单'),
		'order:empty contact name' => array('60001', '请填写联系人'),
		'order:empty contact phone' => array('60001', '请填写联系电话'),
        'order:empty community' => array('60001', '请选择配送小区/大厦后再次保存'),
        'order:empty address area' => array('60001', '请选择配送地区后再次保存'),
        'order:empty address detail' => array('60001', '请完善详细配送地址后再次保存'),
		'order:empty floor' => array('60001', '请选择楼层'),
		'order:empty wid' => array('60001', '请选择仓库'),
		'order:empty payment type' => array('60001', '请选择支付方式'),
		'order:empty delivery date' => array('60001', '请选择配送日期'),
		'order:delivery type error' => array('60001', '订单已安排司机，不能改成自提'),
		'order:order finished' => array('60001', '订单已结单，不能做财务修改'),
		'order:invalid status' => array('60001', '当前操作不可用'),
		'order:had paid' => array('60001', '订单已支付，不能删除或取消，如要更改商品，请联系客服！'),
		'order:has refund' => array('60001', '财务收款的时候已经将同样数额的多余账款进入余额了，请和财务确认！'),
		'order:service empty' => array('60001', '请选择是否上楼'),
		'order:step error' => array('60001', '订单已删除或已经出库'),
		'order:picking order not exists' => array('6001', '拣货单不存在'),
        'order:no permission to create order' => array('60100', '你没有权限创建订单'),
        'order:customer not belong you un-create' => array('60101', '客户不属于当前销售，不能创建订单'),
        'order:order has been confirmed' => array('60200', '该订单客服已确认,如需修改请联系客服'),
        'order:order not belong to you' => array('60201', '您无权修改此订单'),
        'order:can not supplement order' => array('60202', '订单已出库，不能添加补单'),
        'order:order is been modified' => array('60203', '该拣货单客服正在编辑中，请稍后再试'),
        'order:change city error' => array('60204', '订单编辑页面不能切换城市，如需切换，请使用订单编辑页面的【切换订单的城市-订单状态：客服未确认】！'),

		//login/register
		'login: failure' => array('60000', '登陆失败'),
		'login:empty mobile' => array('60001', '手机号不能为空'),
		'login:empty password' => array('60003', '密码输入为空'),
		'login:password format error' => array('60004', '密码不能小于6个字母'),
		'register:shop name format error' => array('60102', '请正确输入门店名称'),
		'register:shop address format error' => array('60103', '请正确输入门店地址'),
		'register:shop assistant name format error' => array('60104', '请正确输入联系人姓名'),
		'register:shop assistant mobile format error' => array('60104', '请正确输入联系人手机'),
		'register:mobile format error' => array('60104', '请正确输入登录手机'),
		'register:mobile taken' => array('60105', '该登录手机号已经被占用'),
		'register:reset code error' => array('60106', '密码重置链接错误或已过期'),
		'register:password is to simple' => array('60107', '密码太简单请重试'),
		//finance
		'finance:empty money' => array('60106', '金额不能为零'),
		//shop
		'shop:order username format error' => array('80101', '姓名输入有误'),
		'shop:order phone format error' => array('80102', '联系电话输入有误'),
		'shop:order code format error' => array('80103', '服务码输入有误'),
		'shop:book time too old' => array('80104', '预约时间不能早于当前时间'),
		'shop:host error' => array('80105', '域名格式不正确'),
		'shop:must vip' => array('80106', '只有VIP用户可以操作，请先升级成VIP用户'),
		'shop:host used' => array('80107', '该域名已被其他用户使用'),
		'shop:icp error' => array('80108', '备案号码格式不正确'),
		'shop:not exist' => array('80109', '会员不存在'),
		'upgrade:code format error' => array('80201', '请按要求填写信息'),
		'upgrade:score not enough' => array('80202', '积分不够'),
		'upgrade:sid_agent format error' => array('80203', '代理商号码输入有误'),
		'shop:can not delete product with stock' => array('80109', '该先释放该商品的“库存”，然后再执行删除操作'),
		'shop: ori_price invalid' => array('80204', '原价不能小于售价'),
        'shop:work_price invalid' => array('80204', '工装价不能大于售价'),
		//warehouse
		'warehouse:empty supplier name' => array('80203', '供应商名称不能为空'),
		'warehouse:empty sku id' => array('80203', 'sku不能为空'),
		'warehouse:empty warehouse id' => array('80203', '库房id不能为空, 请选择仓库'),
		'warehouse:too few stock' => array('80203', '库存不足'),
		'warehouse:empty products' => array('80203', '商品列表为空'),
		'warehouse:in order must has price' => array('80203', '采购订单必须填写采购价格'),
		'warehouse:empty in_order products' => array('80109', '没有添加商品'),
	    'warehouse: picked not enough' => array('80203', '拣货数量不足，不能提交！'),
		//driver
		'driver: model exist' => array('90101', '车型已存在'),
		'driver: source exist' => array('90102', '来源已存在'),
		'driver: model used' => array('90103', '尚有司机使用该车型，不能删'),
		'driver: source used' => array('90104', '尚有司机使用该来源，不能删'),
		'driver: name empty' => array('90105', '司机姓名不能为空'),
		'driver: mobile empty' => array('90106', '司机手机不能为空'),
		'driver: car model empty' => array('90107', '司机车型不能为空'),
		'driver: source empty' => array('90108', '司机来源不能为空'),
		'driver: mobile used' => array('90109', '手机号已存在'),
		'driver: queue type error' => array('90110', '不能从已接单改为未接单'),
        'driver:driver has been allocated' => array('90111', '司机已有分配的订单,不能删除'),
		'carrier: name empty' => array('100101', '搬运工姓名不能为空'),
		'carrier: mobile empty' => array('100102', '搬运工手机不能为空'),
		'carrier: mobile used' => array('100103', '手机号已存在'),
        
        //排线，派单
        'orderline: line2driver-no driverId' => array('100200', '司机分线路，无司机ID'),
        'orderline: line2driver-no carmodel' => array('100201', '司机分线路，无车型信息'),
        'orderline: line2driver-no warehouse' => array('100202', '司机分线路，无仓库ID'),
        
		//business
		'business: name exist' => array('100201', '企业名已存在'),
		'business: name empty' => array('100202', '企业名不能为空'),
		'business: contract name empty' => array('100203', '企业联系人不能为空'),
		'business: contract phone empty' => array('100204', '企业联系电话不能为空'),
		//merchant
		'merchant: mid empty' => array('110101', '请选择第三方'),
		'merchant: msid empty' => array('110102', '请填写第三方sku id'),
		'merchant: sid empty' => array('110103', '请填写好材sku id'),
		'merchant: mprice empty' => array('110104', '请填写第三方对外售价'),
		'merchant: price empty' => array('110105', '请填写好材对第三方售价'),
		'merchant: msid exists' => array('110106', '第三方id和第三方sku id对应关系已存在'),
		'merchant: sid exists' => array('110106', '第三方id和好材sku id对应关系已存在'),
		//after sale
		'aftersale: department empty' => array('120101', '请选择责任部门'),
		'aftersale: order step invalid' => array('120102', '订单尚未回单'),
		'aftersale: must be the same cid' => array('120102', '订单必须为同一客户所有'),
		'aftersale: must be the same did' => array('120102', '订单必须为同一司机所有'),
		'aftersale: must be the same mid' => array('120102', '订单必须为同一搬运工所有'),
		'aftersale: without authority' => array('120102', '您没有权限，请联系管理员'),
		'aftersale: description empty' => array('120102', '请填写问题描述'),
		'aftersale: oid empty' => array('120102', '请填写相关订单号'),

        //refund
        'refund: refund amount cannot be negative' => array('130101', '退款金额不能为负'),

		//小区
		'community: cannot merge to deleted' => array('120102', '目标小区已被删除，不能作为合并的结果小区'),

		//客户端
		'app: can not re_buy' => array('300001', '抱歉，不能购买不同城市的订单商品!'),
		//司机app相关
		'app: check_in failed' => array('200001', '签到失败，无效二维码'),
		'app: has checked' => array('200002', '您已签到！'),
		'app: order not arrive' => array('200003', '您有尚未送达的订单，不能签到！'),
		'app: check_in time wrong' => array('200004', '签到开放时间为早6点到晚10点！'),
		'app: check_in refused' => array('200005', '您已拒单2次，抱歉，今日无法领单～'),
		'app: check_in site wrong' => array('200006', '签到失败，您当前未在签到地点'),

		//领单
		'app: not check_in' => array('200011', '您尚未签到'),
		'app: over max_refuse_num' => array('200012', '您已拒单2次，抱歉，今日无法领单～'),
		'app: order status wrong' => array('200013', '无效的订单状态！'),
		'app: over max_accept_time' => array('200013', '领单失败，您的领取已超时！'),
		'app: set out refuse' => array('200014', '您的订单未出库，请联系调度'),
		'app: order has accepted' => array('200015', '订单被派到别的地方去了，您可以下拉刷新试试'),

		//送达
		'app: not worker order' => array('200021', '不属于本人订单，不能操作!'),
		'app: not leave' => array('200022', '订单未发车，请发车后再操作！'),
		'app: site wrong' => array('200023', '送达失败，您当前未在送达范围之内，请确认位置'),

        
        'pda: please Input wid' => array('10001', '缺少仓库ID'),
        'pda: please Input task id' => array('10101', '请输入任务号'),

        //crm
        'crm: schedule content can not empty' => array('210001', '日程内容不能为空！'),
        'crm: schedule time can not early than now' => array('210002', '日程时间不能早于当前时间！'),
        'crm: schedule remind time can not early than now' => array('210003', '日程提醒时间不能早于当前时间！'),
        'crm: can not add schedule to customer not belong to you' => array('210004', '该客户不是您的私海客户，不能添加日程！'),
        'crm: schedule date can not be empty' => array('210005', '日程查询日期不能为空！'),
        'crm: visit relative schedule is not yours' => array('210006', '拜访关联日程不属于你，无法关联！'),
        'crm: visit address can not be empty' => array('210007', '拜访地址不能为空！'),
        'crm: visit customer can not be empty' => array('210008', '拜访用户不能为空！'),
        'crm: visit customer not belong to you' => array('210009', '该用户不是您的私海用户，不能添加拜访！'),
        'crm: visit relative schedule has been relatived' => array('210010', '该日程已经被关联拜访，不能重复添加！'),
        'crm: customer not exist' => array('210011', '客户不存在！'),
        'crm: customer does not belong to you' => array('210012', '该客户不是您的私海客户，不能编辑！'),
        'crm: only open for saler' => array('210012', '目前crm只对销售人员开放！'),
        'crm: mobile exists' => array('210012', '该手机号已存在于系统中！'),
        'crm: schedule time exists' => array('210012', '您已有一条该时间的日程存在于系统中！'),

        'cpoint: invalid producct info' => array('220001', '无效的商品信息'),
        'cpoint: contact name can not empty' => array('220002', '收货人不能为空'),
        'cpoint: mobile can not empty' => array('220003', '收货电话不能为空'),
        'cpoint: address can not empty' => array('220004', '所在地区不能为空'),
        'cpoint: address detail can not empty' => array('220005', '详细地址不能为空'),
        'cpoint: product is sell out' => array('220006', '该奖品已兑完，请重新选择其他奖品！'),
        'cpoint: product is offline' => array('220006', '该奖品已下架，请重新选择其他商品！'),
        'cpoint: point is not enough' => array('220006', '积分不足，无法兑换'),
        'cpoint: grade is not enough' => array('220006', '等级不足，无法兑换'),

        'finance: type can not be empty' => array('23001', '请选择账户类型！'),
        'finance: name can not be empty' => array('23001', '请填写姓名！'),
        'finance: bank card can not be empty' => array('23001', '请填写银行卡号！'),
        'finance: mobile can not be empty' => array('23001', '请填写手机号！'),
        'finance: id card no can not be empty' => array('23001', '请填写身份证号码！'),
        'finance: company can not be empty' => array('23001', '请填写公司名称！'),
        'finance: social code can not be empty' => array('23001', '请填写统一社会信用代码！'),
        'finance: legal person name can not be empty' => array('23001', '请填写法人姓名！'),
        'finance: check agreement required' => array('23001', '请先阅读并同意《龙门金融借款协议》！'),
        'finance: duplicated apply' => array('23001', '您已申请，每名用户仅限申请一次！'),
        'finance: tp total amount can not be empty' => array('23001', '请填写第三方授信额度！'),
        'finance: tp due date can not be empty' => array('23001', '请填写第三方授信期限！'),
        'finance: hc total amount can not be empty' => array('23001', '请填写好材授信额度！'),
        'finance: hc due date can not be empty' => array('23001', '请填写好材授信期限！'),
        'finance: hc due date can not be bigger than tp' => array('23001', '好材授信期限不能大于第三方授信期限！'),
        'finance: hc total amount can not be bigger than tp' => array('23001', '好材授信额度不能大于第三方授信额度！'),
        'finance: id card no is not same' => array('23001', '身份证号码和申请时填写的号码不一致！'),
        'finance: payment type limit' => array('23001', '该订单已使用好材信用支付，不能再使用其他支付方式！'),
	);

	const DEFAULT_ERRNO = '999999';
	const DEFAULT_ERRMSG = '内部错误，请联系管理员或稍后重试';
    
    
    public static $PDA_Exception = array(
        
    );
}