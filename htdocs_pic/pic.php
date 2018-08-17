<?php
include_once ('../global.php');

class CApp extends App_Page
{
	private $filename;

	protected function getPara()
	{
		$this->filename = Tool_Input::clean( 'r', 'filename', 'str');
	}

	protected function main()
	{
		$content = Data_Pic::getPic($this->filename, $fid);
		if ( empty($content))
		{
			header("HTTP/1.0 404 Not Found");
			exit;
		}

		list($token, $type) = explode('.', $this->filename);
		$this->outputImage($content, $type, strlen($content));
		exit;
	}

	/**
	 * 输出图片
	 */
	private function outputImage($content, $type, $length, $etag = "", $cc = "public")
	{
		if ($type == "jpg")
		{
			$type = "jpeg";
		}
		header("HTTP/1.0 200 OK");
		header("Content-Type: image/".$type);
		header("Pragma:");
		header("Cache-Control: ".$cc);
		header("Expires: ".gmdate("D, d M Y H:i:s", strtotime("+2 years"))." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");

		if ($etag)
		{
			header("Etag: \"".$etag."\"");
		}
		header("Content-Length: ". $length);
		print($content);
	}
}

$app = new CApp("");
$app->run();
