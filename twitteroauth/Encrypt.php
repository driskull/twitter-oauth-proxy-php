<?php
class Encrypt{
	/*
	* Basic encryption Class
	* Version: 1.0
	* Author: Andy Gup
	*/
    private $key;
	private $iv;
	
    function __construct($encryption_key,$iv,$time_zone) {
        $this->key = $encryption_key;
		$this->iv = $iv;
		date_default_timezone_set($time_zone);
    }

    public function encrypt($value){
        $final = null;
		if($value == null || $this->key == null || $this->iv == null)
		{
			header("HTTP/1.0 400 Error");
			echo "\n\n\nERROR: Null value detected: check your inputs";
			exit;
		}
		else
		{
			try
			{
				$final = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_CFB,$this->iv);
			}
			catch(Exception $e)
			{
				header("HTTP/1.0 400 Error");
				echo "\n\n\nERROR: Failed encryption. " .$e->getMessage();
				exit;
			}		
		}

        return  $final;
    }

    public function decrypt($value){
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_CFB,$this->iv);
    }

    public function get_RandomKey(){
        $result = openssl_random_pseudo_bytes(32, $secure);
        return base64_encode($result);
    }
	
	public function get_IV(){
		$size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
		return mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
	}
}
?>