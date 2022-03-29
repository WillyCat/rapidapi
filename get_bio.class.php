<?php

class get_bio extends actors {

	public function
	__construct(string $keyfile, string $nconst, ?callable $proxy = null) {
		parent::__construct();
		$this -> setKeyfile($keyfile);
		$this -> endpoint .= '/get-bio';
		$this -> addParam ('nconst', $nconst);
		$this -> setProxy ($proxy);
	}


}

?>
