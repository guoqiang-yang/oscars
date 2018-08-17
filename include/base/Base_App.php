<?php
/**
 * 应用程序的基类
 */
class Base_App extends Base_Control
{
	public function run()
	{
		try
		{
			//基本权限验证
			$this->checkAuth();

			//参数获取
			$this->getPara();

			//参数校验
			$this->checkPara();

			//主逻辑
			$this->main();

			//输出前释放一些关键资源
			$this->free();

			//输出 HTTP 协议头信息部分
			$this->outputHttp();

			//输出 HTTP 协议页面部分
			$this->outputPage();

		}
		catch(Exception $ex)
		{
			$this->showError($ex);
		}
	}

	/**
	 * 基本权限验证
	 */
	protected function checkAuth()
	{
	}

	/**
	 * 参数获取
	 */
	protected function getPara()
	{
	}

	/**
	 * 参数校验
	 */
	protected function checkPara()
	{
	}

	/**
	 * 主逻辑
	 */
	protected function main()
	{
	}

	/**
	 * 输出前释放一些关键资源
	 */
	protected function free()
	{
	}

	/**
	 * 输出 HTTP 协议头信息部分
	 */
	protected function outputHttp()
	{
	}

	/**
	 * 输出 HTTP 协议页面部分
	 */
	protected function outputPage()
	{
		//公共参数
		$this->setCommonPara();

		//输出页面标准头部
		$this->outputHead();

		//公共参数
		$this->setCommonPara();

		//输出页面主体部分
		$this->outputBody();

		//公共参数
		$this->setCommonPara();

		//输出页面标准尾部
		$this->outputTail();
	}

	/**
	 * 输出页面标准头部
	 */
	protected function outputHead()
	{
	}

	/**
	 * 公共参数
	 */
	protected function setCommonPara()
	{
	}

	/**
	 * 输出页面主体部分
	 */
	protected function outputBody()
	{
	}

	/**
	 * 输出页面标准尾部
	 */
	protected function outputTail()
	{
	}

	/**
	 * 统一异常处理
	 */
	protected function showError($ex)
	{
	}
}
