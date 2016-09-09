<?php

#namespace NoIP;

class NoIP
{

    /**
     * @var string
     */
    private $username;
	
    /**
     * @var string
     */
    private $password;
	
	private $cookie;
	private $token;

    public function __construct($login, $password)
    {
		$this->username = $login;
		$this->password = $password;
		$this->cookie = dirname(__FILE__) . '/cookie.txt';
		$this->token = $this->doLogin();
    }
	
	public function refreshHosts()
	{
		$hosts = $this->getHosts();
		
		$responseArr = [];
		foreach($hosts->hosts as $host) {
			if(true === $host->is_expiring_soon) {
				
				$refreshUrl = sprintf("https://my.noip.com/api/host/%d/touch", $host->id);

				$headers = [
					'X-CSRF-TOKEN:'. $this->token,
					'X-Requested-With: XMLHttpRequest'
				];
				
				$c = curl_init($refreshUrl);
				curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookie);
				curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($c, CURLOPT_COOKIE, $this->cookie);
				curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36");
				curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

				$response = curl_exec($c);
				
				$responseArr[] = json_decode($response);
			}
		}
		return $responseArr;
	}
	
	private function doLogin()
	{
		$url = 'https://www.noip.com/login';
		
		$post = [
			'submit_login_page' => 1,
			'_token' => $this->getLoginToken(),
			'Login' => null,
			'username' => $this->username,
			'password' => $this->password
		];
		

		$c = curl_init($url);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($c, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36");
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($post));
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

		$response = curl_exec($c);
		
		$dom = new DOMDocument();
		@$dom->loadHTML($response);

		foreach ($dom->getElementsByTagName('meta') as $link) {
			if ('token' == $link->getAttribute('name')) {
				return $link->getAttribute('content');
			}
		}
		
		$xpath = new DOMXpath($dom);

		$elements = $xpath->query('//*[@id="sign-up-wrap"]/div[2]/div[2]');
		
		$errors = '';
		if (!is_null($elements)) {
			$errorList = [];
			  foreach ($elements as $element) {
				$nodes = $element->childNodes;
				foreach ($nodes as $node) {
				  $errorList[] = $node->nodeValue;
				}
			  }
			$errors = implode("; ", $errorList);
		}
		
		throw new Exception("Unable to get page CSRF-TOKEN. $errors");
	}
	
	private function getLoginToken()
	{
		$url = 'www.noip.com/login';
			
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_getinfo($c);
		$site = curl_exec($c);

		if (FALSE === $site) {
			throw new Exception(curl_error($c), curl_errno($c));
		}

		
		$dom = new DOMDocument();
		@$dom->loadHTML($site);

		# Iterate over all the <a> tags
		$token = '';
		foreach ($dom->getElementsByTagName('input') as $link) {
			if ('_token' == $link->getAttribute('name')) {
				$token = $link->getAttribute('value');
			}
		}
		
		return $token;
	}
	
	public function getHosts()
	{
		$url = 'https://my.noip.com/api/host';
		
		$headers = [
			'X-CSRF-TOKEN:'. $this->token,
			'X-Requested-With: XMLHttpRequest'
		];
		
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($c, CURLOPT_COOKIE, $this->cookie);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36");
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($c);
		
		return json_decode($response);
	}
} 