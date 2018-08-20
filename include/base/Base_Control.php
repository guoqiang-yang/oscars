<?php
/**
 * 控制层的基类
 *
 * 目前项目中, 控制层都会对应一个登录用户, 为了方便操作将当前登录用户的uid和信息分别存在了：$_uid, $_user
 */
class Base_Control
{
	protected $_uid;
	protected $_user;

	/**
	 * 设置当前登录用户uid
	 */
	protected function setCurUid($uid)
	{
		$this->_uid = $uid;
	}

	/**
	 * 设置当前登录用户uid
	 */
	protected function setCurUser($user)
	{
		$this->_user = $user;
	}
}
