<?php
require_once dirname(__FILE__)."/../vendor/Smarty-3.1.7/libs/Smarty.class.php";

/**
 * Smarty 操作的封装
 */
class Tool_Smarty
{
	private $smarty;

	public function __construct($templatePath , $param = array())
	{
		$this->smarty = new Smarty();
		$this->smarty->left_delimiter = "{{";
		$this->smarty->right_delimiter = "}}";
		$this->smarty->compile_check = true;
		$this->smarty->caching = false;
		$this->smarty->template_dir = $templatePath;
		$this->smarty->compile_dir = '/tmp/templates_c/';
		$this->smarty->loadFilter("variable", "htmlspecialchars");
		$this->smarty->loadFilter("pre", "strip");
		foreach ($param as $key => $val)
		{
			$this->smarty->$key = $val;
		}
	}

	public function getEngine()
	{
		return $this->smarty;
	}

	/**
	 * 普通html
	 */
	public function assignHtml($var, $value)
	{
		assert(false);
		self::processValueDeep($value, 'htmlspecialchars');
		$this->smarty->assign($var, $value);
	}

	/**
	 * 单行文本 简单的作为JS变量
	 */
	public function assignEscapeSlash($var, $value)
	{
		self::processValueDeep($value, array(self, 'addSlashes'));
		$this->smarty->assign($var, $value);
	}

	/**
	 * 单行文本 作为JS变量，并最终输出到页面显示
	 */
	public function assignEscapeHtmlSlash($var, $value)
	{
		self::processValueDeep($value, array(self, 'addSlashesHtml'));
		$this->smarty->assign($var, $value);
	}

	/**
	 * 多行文本 显示
	 */
	public function assignMultiline($var, $value)
	{
		self::processValueDeep($value, array(self, 'multiline'));
		$this->smarty->assign($var, $value);
	}

	/**
	 * JSON，不支持批量设置
	 */
	public function assignJson($name, $value)
	{
		$this->smarty->assign($name, json_encode($value));
	}

	/**
	 * 将HTML作为普通文本设置到编辑器中，不支持批量设置 或 html文本 编辑器编辑
	 */
	public function assignEditor($name, $value, $sTextType='html')
	{
		if($sTextType == 'plain')
		{
			$value = str_replace(array("&quot;", "&lt;", "&gt;", "&amp;"), array("\"", "<", ">", "&"),
					 str_replace(array("<br />", "<br/>"), array("", ""), $value));
		}
		$value = str_replace(array("\n", "\r"), array("\\n", ""), self::addSlashes($value));
		$this->smarty->assign($name, $value);
	}

	/**
	 * assign raw
	 */
	public function assign($var, $value)
	{
		$this->smarty->assign($var, $value);
	}
    
    public function assignRaw($var, $value)
    {
        $this->smarty->unloadFilter("variable", "htmlspecialchars");
        $this->smarty->assign($var, $value);
    }

	/**
	 * 输出模板
	 */
	public function display($filePath, $clear=true)
	{
		try
		{
			$this->smarty->display($filePath);
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
		if($clear)
		{
			$this->smarty->clearAllAssign();
		}
	}

	/**
	 * 返回模板替换后内容
	 *
	 * @return string
	 */
	public function fetch($filePath, $clear=true)
	{
		try
		{
			$content = $this->smarty->fetch($filePath);
		}
		catch(Exception $e)
		{
			$content = $e->getMessage();
		}
		if($clear)
		{
			$this->smarty->clearAllAssign();
		}
		return $content;
	}

	/**
	 * 获得smarty中assign的变量值
	 */
	public function getTemplateVars($var)
	{
		try
		{
			$value = $this->smarty->getTemplateVars($var);
		}
		catch(Exception $e)
		{
			$value = $e->getMessage();
		}
		return $value;
	}

	/**
	 * 用fn函数递归处理var变量
	 */
	public function processValueDeep(&$var, $func)
	{
		if (is_array($var))
		{
			foreach ($var as $key => &$item)
			{
				self::processValueDeep($item, $func);
			}
		}
		else
		{
			if (!isset($var))
			{
				$var = '';
			}
			$var = call_user_func_array($func,$var);
		}
	}

	/**
	 * 封装 addslashes 和 forbidScript 编码
	 *
	 * @return string
	 */
	private static function addSlashes($input)
	{
		return addslashes(self::forbidScript($input));
	}

	/**
	 * 封装 addslashes 和 forbidScript 和 htmlspecialchars 编码
	 *
	 * @return string
	 */
	private static function addSlashesHtml($input)
	{
		return addslashes(self::forbidScript(htmlspecialchars($input)));
	}

	private static function forbidScript($sText)
	{
		$sText = str_replace("\r", "", $sText);
		return preg_replace("/script/i", " script ", $sText);
	}

	/**
	 * 封装 nl2br 和 htmlspecialchars 编码
	 *
	 * @return string
	 */
	private static function multiline($input)
	{
		return nl2br(htmlspecialchars($input));
	}
}
