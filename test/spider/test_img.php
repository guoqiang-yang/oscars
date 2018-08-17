<?php
/**
 * 抓取有赞数据
 */
include_once ('../../global.php');
include_once(INCLUDE_PATH . 'vendor/simple_html_dom.php');
include_once (INCLUDE_PATH . 'vendor/Snoopy.class.php');

class App extends App_Cli
{
	protected function main()
	{
		$host = 'imgqn.koudaitong.com';
		$imgUrl = 'http://imgqn.koudaitong.com/upload_files/2015/04/23/Frq6OqeV93c-wjommTa_L_Se50Sv.jpg!490x490+2x.jpg';
		$url = 'http://wap.koudaitong.com/v2/goods/y1i12l0q?spm=t12484360_h1145545_t12484527';
		$content = $this->_downloadByCurl($imgUrl, $url, $host);

		$file = '/tmp/tmp3.jpg';
		$this->_trace('saving %s ...', $file);

		$fs = fopen($file,"w+");
		fwrite($fs, $content);
		fclose($fs);
	}

	private function _downloadByCurl($url, $referer, $host='')
	{
		$agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4';
		$headers["Host"] = $host ? $host:'wap.koudaitong.com';
		$headers["Accept"] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
		$headers["Accept-Language"] = 'zh-CN,zh;q=0.8,en;q=0.6';
		$headers["Accept-Encoding"] = 'gzip, deflate, sdch';
		$headers["Connection"] = 'keep-alive';
		$headers["max-age"] = '0';
		$content = Tool_Http::get($url, array(), $headers, $referer, $agent);
		return $content;
	}

	private function _downloadBySnoopy($url, $refer, $host='')
	{
		$snoopy = new Snoopy;
		$snoopy->referer = $refer;
		$snoopy->agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4';
		$snoopy->maxredirs = 20;
		$snoopy->rawheaders["Host"] = $host ? $host:'wap.koudaitong.com';
		$snoopy->rawheaders["Accept"] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
		$snoopy->rawheaders["Accept-Language"] = 'zh-CN,zh;q=0.8,en;q=0.6';
		$snoopy->rawheaders["Accept-Encoding"] = 'gzip, deflate, sdch';
		$snoopy->rawheaders["Connection"] = 'keep-alive';
		$snoopy->rawheaders["max-age"] = '0';

		$this->_trace('get url : %s', $url);

		$ret = $snoopy->fetch($url);
		if (!$ret) {
			$this->__error('error fetching document: %s', $snoopy->error);
			return false;
		}

		return $snoopy->results;
	}
}

$app = new App();
$app->run();
