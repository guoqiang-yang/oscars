<?php
include_once ('../../../global.php');

class App extends App_Ajax
{
	const LOGO_MAX_FILESIZE = 5242880;	//5M

	private $pic;
	private $path;
	private $imageinfo;

	protected function getPara()
	{
		$this->pic = Tool_Input::clean( 'f', 'pic', TYPE_FILE);
		$this->path = trim(Tool_Input::clean( 'r', 'path', TYPE_STR));
	}


	protected function checkPara()
	{
		if (UPLOAD_ERR_OK != $this->pic['error'])
		{
			throw new Exception('common:upload pic error');
		}

		$imageinfo = Tool_Image::getImageInfo($this->pic['tmp_name']);
		if ($imageinfo === false || !$imageinfo['width'] || !$imageinfo['height'])
		{
			throw new Exception('common:upload pic error');
		}
		$this->imageinfo = $imageinfo;

		if ($this->path == 'undefined')
		{
			$this->path = OSS_PIC_PATH;
		}
	}

	protected function main()
	{
		$name = $this->pic['name'];
		$content = file_get_contents($this->pic['tmp_name']);
		$width = $this->imageinfo['width'];
		$height = $this->imageinfo['height'];
        
        $path = '';
        if (!empty($this->path))
        {
            $path .= trim($this->path, DS). DS;
        }
		$pictag = Shop_Api::saveSkuPic($name, $content, $width, $height, $path);

		$response = new Response_Ajax();
//		$picUrl = Data_Pic::getPicUrl($pictag, 'middle');//图片缩放后gif不是动态
		$picUrl = Shop_Helper::formatSkuPic($pictag);
		$response->setContent(array('pictag'=> $pictag, 'picurl'=> $picUrl));
		$response->send();
		exit;
	}
}
$app = new App('pri');
$app->run();