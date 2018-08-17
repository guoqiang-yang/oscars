<?php

class Tool_Http
{
    public static $Time_Out = 5;    //秒
    
    public static function setTimeOut($second)
    {
        self::$Time_Out = $second;
    }
    
	public static function get($url, $param=array(), $headers=array(), $referer='', $agent='Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)')
	{
		if (!empty($param))
		{
			if (is_array($param))
			{
				$param = http_build_query($param);
			}
			$url .= strstr('?', $url) ? '&':'?';
			$url .= $param;
		}
        
		$curl=curl_init();
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); //
		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0); //
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_TIMEOUT, self::$Time_Out);
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_USERAGENT, $agent);
		curl_setopt($curl, CURLOPT_REFERER, $referer);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl,CURLOPT_COOKIESESSION,true);
		curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, false);

		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
	}

	public static function post($url, $param, $headers=array(), $referer='', $agent='Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)')
	{
		if (empty($param))
		{
			return false;
		}

		$curl=curl_init();
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0); //
		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0); //
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, self::$Time_Out);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_USERAGENT, $agent);
		curl_setopt($curl, CURLOPT_REFERER, $referer);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_COOKIESESSION,true);
		curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		if(is_array($param))
		{
			// curl_setopt($curl, CURLOPT_POSTFIELDS, $param); //tomcat spring不行？？
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));
		}
		else
		{
			curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
		}

		$content = curl_exec($curl);
		curl_close($curl);

		return $content;
	}

	public function build_http_query_multi($params)
	{
		if (!$params) return '';

		uksort($params, 'strcmp');

		$pairs = array();

		$boundary = '';
		$boundary = uniqid('------------------');

		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		$multipartbody = '';

		foreach ($params as $parameter => $value)
		{
			if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' )
			{
				$url = ltrim( $value, '@' );
				$content = file_get_contents( $url );
				$array = explode( '?', basename( $url ) );
				$filename = $array[0];

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
				$multipartbody .= "Content-Type: image/unknown\r\n\r\n";
				$multipartbody .= $content. "\r\n";
			}
			else
			{
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
				$multipartbody .= $value."\r\n";
			}
		}

		$multipartbody .= $endMPboundary;
		return array($boundary, $multipartbody);
	}

	/**
	 * 用于腾讯微博
	 */
	public function http( $url , $params , $method='GET' , $multi=false )
	{
		$method = strtoupper($method);
		$postdata = '';
		$urls = @parse_url($url);
		$httpurl = $urlpath = $urls['path'] . ($urls['query'] ? '?' . $urls['query'] : '');
		if( !$multi )
		{
			$parts = array();
			foreach ($params as $key => $val)
			{
				$parts[] = urlencode($key) . '=' . urlencode($val);
			}
			if ($parts)
			{
				$postdata = implode('&', $parts);
				$httpurl = $httpurl . (strpos($httpurl, '?') ? '&' : '?') . $postdata;
			}
			else
			{
			}
		}

		$host = $urls['host'];
		$port = $urls['port'] ? $urls['port'] : 80;
		$version = '1.1';
		if($urls['scheme'] === 'https')
		{
			$port = 443;
		}
		$headers = array();
		if($method == 'GET')
		{
			$headers[] = "GET $httpurl HTTP/$version";
		}
		else if($method == 'DELETE')
		{
			$headers[] = "DELETE $httpurl HTTP/$version";
		}
		else
		{
			$headers[] = "POST $urlpath HTTP/$version";
		}
		$headers[] = 'Host: ' . $host;
		$headers[] = 'User-Agent: OpenSDK-OAuth';
		$headers[] = 'Connection: Close';

		if($method == 'POST')
		{
			if($multi)
			{
				$boundary = uniqid('------------------');
				$MPboundary = '--' . $boundary;
				$endMPboundary = $MPboundary . '--';
				$multipartbody = '';
				$headers[]= 'Content-Type: multipart/form-data; boundary=' . $boundary;
				foreach($params as $key => $val)
				{
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
					$multipartbody .= $val . "\r\n";
				}
				foreach($multi as $key => $data)
				{
					$multipartbody .= $MPboundary . "\r\n";
					$multipartbody .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . $data['name'] . '"' . "\r\n";
					$multipartbody .= 'Content-Type: ' . $data['type'] . "\r\n\r\n";
					$multipartbody .= $data['data'] . "\r\n";
				}
				$multipartbody .= $endMPboundary . "\r\n";
				$postdata = $multipartbody;
			}
			else
			{
				$headers[]= 'Content-Type: application/x-www-form-urlencoded';
			}
		}
		$ret = '';
		$fp = fsockopen($host, $port, $errno, $errstr, 5);

		if(! $fp)
		{
			$error = 'Open Socket Error';
			return '';
		}
		else
		{
			if( $method != 'GET' && $postdata )
			{
				$headers[] = 'Content-Length: ' . strlen($postdata);
			}
			fwrite($fp, implode("\r\n", $headers));
			fwrite($fp, "\r\n\r\n");
			if( $method != 'GET' && $postdata )
			{
				fwrite($fp, $postdata);
			}
			//skip headers
			while(! feof($fp))
			{
				$ret .= fgets($fp, 1024);
			}
			fclose($fp);
			$pos = strpos($ret, "\r\n\r\n");
			if($pos)
			{
				$rt = trim(substr($ret , $pos+1));
				$responseHead = trim(substr($ret, 0 , $pos));
				$responseHeads = explode("\r\n", $responseHead);
				$httpcode = explode(' ', $responseHeads[0]);
				if(strpos( substr($ret , 0 , $pos), 'Transfer-Encoding: chunked'))
				{
					$response = explode("\r\n", $rt);
					$t = array_slice($response, 1, - 1);

					return implode('', $t);
				}
				return $rt;
			}
			return '';
		}
	}
}
