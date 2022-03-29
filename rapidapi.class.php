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

	public function
	__construct()
	{
		$this -> endpoint = '';
		$this -> params = [ ];
		$this -> headers = [ ];
		$this -> proxy = null;
		$this -> keyfile = null;
		$this -> api_key = null;
	}

	protected function
	setKey (string $api_key) {
		$this -> api_key = $api_key;
	}

	protected function
	setKeyfile (string $keyfile) {
		if (!file_exists ($keyfile))
			throw new Exception ('keyfile not found');
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
				throw new Exception ('HTTP ' . $req -> getStatus());

			// $headers = $req -> getHeaders(); // array of code:value
			// $req -> getHeader('Content-Length') // header value or null
			// $req -> getContentLength() // Content-Length value or 0
			$this -> body = $req -> getBody(); // string, can be empty
			$this -> source = 'api';
			if ($this -> proxy != null)
                                ($this -> proxy)('store', $url -> getUrl(), $this -> body);
		} catch (tinyHttp_Exception $e) {
			throw new Exception ($e -> getMessage());
		}
	}

	public function
	go(): void {
		if ($this -> endpoint == '')
			throw new Exception ('Missing endpoint');
		if (is_null ($this -> api_key))
			throw new Exception ('Missing api key');

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
		return '1.0';
	}

	public function
	getEndpoint (): string
	{
		return $this -> endpoint;
	}
}

?>
