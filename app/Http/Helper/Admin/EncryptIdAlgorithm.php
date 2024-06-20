<?php namespace App\Http\Helper\Admin;

use Session;

class EncryptIdAlgorithm {
	
    public static function base64_alg($data,$type)
	{
		if($type == 'encode') {
			return base64_encode($data);
		} else {
            return base64_decode($data);
		}
	}
	
	public static function base64_rot13_alg($data,$type)
	{
		if($type == 'encode') {
			return str_rot13(base64_encode($data));
		} else {
            return base64_decode(str_rot13($data));
		}
	}
	
	public static function base64_rand_alg($data,$type)
	{
		$new_encode_decode_val = Session::get('new_encode_decode_val');
		if($type == 'encode') {
			$data = $data.$new_encode_decode_val;
            return base64_encode($data);
		} else {
            $dec_val = base64_decode($data);
			return rtrim($dec_val,$new_encode_decode_val);
		}
	}
	
	public static function base64_rot13_rand_alg($data,$type)
	{
		$new_encode_decode_val = Session::get('new_encode_decode_val');
		if($type == 'encode'){
			$data = $data.$new_encode_decode_val;
            return str_rot13(base64_encode($data));
		} else {
            $dec_val = base64_decode(str_rot13($data));
			return rtrim($dec_val,$new_encode_decode_val);
		}
	}
	
	/*** Start encode and decode algorithm [Vijay]***/
	public static function id_encode_alg($data,$type)
	{
		if($type == 'encode')
		{
			return self::base64url_encode($data.'-'.substr(sha1($data), 0, 6));
		}
		else
		{
			$parts = explode('-', self::base64url_decode($data));
			if (count($parts) != 2) {
			   
				return 0;
			}
		   
			$int = $parts[0];
			return substr(sha1($int), 0, 6) === $parts[1]
				? (int)$int
				: 0;
		}
	}
	
	// Inner function
	public static function base64url_encode($data) 
	{
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	} 

	// Inner function
	public static function base64url_decode($data) 
	{
	  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	} 
	
	/*** End algorithm [Vijay] ***/
	
	/*** Start Encode and Decode Algorithym [Gopal] ***/
	public static function getEncodeAndDecodeOfId($id, $type = 'encode')
    {
		$encrypt_key = "#&#";
		$id = ($type == 'encode') ? self::getEncodeString($id,$encrypt_key) : self::getDecodeString($enc_id,$encrypt_key);
		return $id;
	}
	### Encode Function starts ###
	public static function getEncodeString($id,$encryption_key)
    {
		$random_key = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM );
		$encrypted_string =$random_key.
			mcrypt_encrypt(
				MCRYPT_RIJNDAEL_128,
				hash('sha256', $encryption_key, true),
				$id,
				MCRYPT_MODE_CBC,
				$random_key
			);
		$data = strtr(base64_encode($encrypted_string), '+/=', '-_$');
		return $data;
	} 
	
	### Decode Function starts ###
	public static function getDecodeString($id,$encryption_key)
    {
		$data = base64_decode(strtr($id, '-_$', '+/='));
		$random_key = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
		$decrypted_string = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_128, hash('sha256', $encryption_key, true), substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $random_key ), "\0" );
		return $decrypted_string;
	}
	/*** End Encode and Decode Algorithym [Gopal] ***/
	
	/*** Start Encode and Decode Algorithym [Nallasivam] ***/
	public static function PekkaEncodeAndDecodeOfId($id, $type = 'encode')
    {
		$id = ($type == 'encode') ? self::pekka_encode($id) : self::pekka_decode($id);
		return $id;
	}
	### Encode Function starts ###
	public static function pekka_encode($id)
	{
		base64_encode($id);
		$encode = '';
		$id = base64_encode($id);
		for ($i=0;$i<strlen($id); $i++) {
			$encode .= sprintf("%03d", ord($id[$i]));     
		}
		return $encode;
	}
	### Decode Function starts ###
	// bit smaller with some dechex() conversion
	public static function pekka_decode($id)
	{
		$decode = '';		
		for ($i=0;$i<strlen($out);$i+=3) {
			$decode .= chr($out[$i].$out[$i+1].$out[$i+2]);
		}
		$decode = base64_decode($decode);
	}
	/*** End Encode and Decode Algorithym [Nallasivam] ***/
}