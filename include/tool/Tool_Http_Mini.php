<?php
/*from qq open platform*/
class Tool_Http_Mini
{
	public static function request( $url , $params = array(), $method = 'GET' , $multi = false, $extheaders = array(), $cookie='')
	{
		if(!function_exists('curl_init')) exit('Need to open the curl extension');
		$method = strtoupper($method);
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36');
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
		$timeout = $multi?30:3;
		curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ci, CURLOPT_COOKIE, $cookie);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ci, CURLOPT_HEADER, false);
		$headers = (array)$extheaders;
		switch ($method)
		{
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($params))
				{
					if($multi)
					{
						curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
						$headers[] = 'Expect: ';
					}
					else
					{
						curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
					}
				}
				break;
			case 'DELETE':
			case 'GET':
				$method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($params))
				{
					$url = $url . (strpos($url, '?') ? '&' : '?')
						. (is_array($params) ? http_build_query($params) : $params);
				}
				break;
		}
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
		curl_setopt($ci, CURLOPT_URL, $url);
		if($headers)
		{
			curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		}

		$response = curl_exec($ci);
		curl_close ($ci);
		return $response;
	}
}
