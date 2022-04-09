<?php

class rapidapi {

	private ?string $api_key;
	protected string $endpoint;
	protected array $params;
	private $proxy;
	private ?string $keyfile;

	private array $headers;
	private ?string $body;
	private array $result;
	private string $source; // db or request
	private string $lastError;

	public function
	__construct()
	{
		$this -> endpoint = '';
		$this -> params = [ ];
		$this -> headers = [ ];
		$this -> proxy = null;
		$this -> keyfile = null;
		$this -> api_key = null;
		$this -> lastError = '';
	}

	protected function
	setKey (string $api_key) {
		$this -> api_key = $api_key;
	}

	protected function
	setKeyfile (string $keyfile) {
		if (!file_exists ($keyfile))
		{
			$this -> lastError = 'keyfile not found';
			throw new Exception ($this -> lastError);
		}
		$this -> keyfile = $keyfile;
		$this -> setKey( trim(file_get_contents ($keyfile)) );
	}

	protected function
	setProxy (?callable $proxy = null): void {
		$this -> proxy = $proxy;
	}

	protected function
	addParam (string $name, string $value): void {
		$this -> params[$name] = $value;
	}

	private function
	addHeader(string $name, string $value): void {
		$this -> headers[$name] = $value;
	}

	public function
	getLastError(): string
	{
		return $this -> lastError;
	}

	private function
	fetch(): void {
		$http = new tinyHttp();
		$url = new tinyUrl ($this -> endpoint);
		$url -> setQuery ($this -> params);

		if ($this -> proxy != null) {
                        $this -> body = ($this -> proxy)('query', $url -> getUrl());
                        if ($this -> body != null)
                        {
                                $this -> source = 'db';
				return;
                        }
		}

		$http -> setUrl ($url);
		$http -> setMethod ('GET');
		$http -> setHeader ('X-RapidAPI-Host', $url->getHost());
		$http -> setHeader ('X-RapidAPI-Key', $this -> api_key);

		try {
			$req = $http -> send();
			if ($req -> getStatus() != 200)
			{
				$errorJson = $req -> getBody();
				if (substr ($errorJson, 0, 1) == '{')
				{
					$errorArray = json_decode ($errorJson, true);
					if (array_key_exists ('message', $errorArray))
						$this -> lastError = $errorArray['message'];
				}
				else
					$this -> lastError = 'HTTP ' . $req -> getStatus();

				throw new Exception ($this -> lastError);
			}

			// $headers = $req -> getHeaders(); // array of code:value
			// $req -> getHeader('Content-Length') // header value or null
			// $req -> getContentLength() // Content-Length value or 0
			$this -> body = $req -> getBody(); // string, can be empty
			$this -> source = 'api';
			if ($this -> proxy != null)
                                ($this -> proxy)('store', $url -> getUrl(), $this -> body);
		} catch (tinyHttp_Exception $e) {
			$this -> lastError = $e -> getMessage();
			throw new Exception ($e -> getMessage());
		}
	}

	public function
	go(): void {
		if ($this -> endpoint == '')
		{
			$this -> lastError = 'Missing endpoint';
			throw new Exception ($this -> lastError);
		}
		if (is_null ($this -> api_key))
		{
			$this -> lastError = 'Missing api key';
			throw new Exception ($this -> lastError);
		}

		$this -> fetch();

		$this -> result = json_decode ($this -> body, true);
	}

	public function
	getBody (): string {
		return $this -> body;
	}

	public function
	getSource(): string {
		return $this -> source;
	}

	public function
	getResult (): array {
		return $this -> result;
	}

	static function
	getVersion(): string
	{
		return '1.1';
	}

	public function
	getEndpoint (): string
	{
		return $this -> endpoint;
	}
}

?>
