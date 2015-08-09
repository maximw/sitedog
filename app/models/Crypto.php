<?php

namespace App\Models;

use Nette;

class Crypto extends Nette\Object
{
    protected $isOpenSSL = false;
    protected $isMcrypt  = false;

    public function __construct()
    {
        $this->isOpenSSL = function_exists('openssl_encrypt');
        $this->isMcrypt = function_exists('mcrypt_encrypt');
    }

    public function encrypt($text, $key)
    {
        if ($this->isOpenSSL) {
            return $this->encryptOpenSSL($text, $key);
        //} elseif ($this->isMcrypt) {
            //return $this->encryptMcrypt($text, $key);
        } else {
            throw new \Exception('No any crypto extension');
        }
    }

    public function decrypt($text, $key)
    {
        if ($this->isOpenSSL) {
            return $this->decryptOpenSSL($text, $key);
        //} elseif ($this->isMcrypt) {
            //return $this->decryptMcrypt($text, $key);
        } else {
            throw new \Exception('No any crypto extension');
        }
    }

    public function encryptOpenSSL($text, $key)
    {
        $iv_size = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($iv_size);
        if (strlen($iv) != $iv_size) {
            throw new \Exception('Error receiving IV');
        }
        $result = $iv.openssl_encrypt($text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $result;
    }

    public function decryptOpenSSL($text, $key)
    {
        $iv_size = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($text, 0, $iv_size);
        $text = substr($text, $iv_size);
        $result = openssl_decrypt($text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $result;
    }


    public function encryptMcrypt($text, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
        if (strlen($iv) != $iv_size) {
            throw new \Exception('Error receiving IV');
        }
        $result = $iv.mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_CBC, $iv);
        return $result;
    }

    public function decryptMcrypt($text, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = substr($text, 0, $iv_size);
        $text = substr($text, $iv_size);
        $result = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_CBC, $iv);
        return $result;
    }

    /**
     * Get random raw string given $length
     */
    public function getRandom($length, $safe = false)
    {
        $length = (int)$length;
        if ($this->isOpenSSL) {
            return $this->getRandomOpenSSL($length, $safe);
        } else {
            throw new \Exception('No any crypto extension');
        }
    }

    /**
     * Get random raw string given $length
     */
    public function getRandomOpenSSL($length, $safe = false)
    {
        $length = (int)$length;
        $result = openssl_random_pseudo_bytes($length);
        if (strlen($result) !== $length) {
            throw new \Exception('Failed to get random bytes');
        }
        return $safe ? $this->safe($result) : $result;
    }

    public function expandKey($key)
    {
        //TODO PBKDF2
        return hash('sha256', $key, true);
    }

    /*
     * RFC3548
     */
    public function safe($text)
    {
        return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
    }

    public function raw($text)
    {
        return base64_decode(str_pad(strtr($text, '-_', '+/'), strlen($text) % 4, '=', STR_PAD_RIGHT));
    }

    public function hashPassword($password)
    {
        $password = (string)$password;
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT);
        }

        $algo = '$2y$31$';
        if (version_compare(PHP_VERSION, '5.3.7', '<')) {
            $algo = '$2a$31$';
        }

        $salt = base64_encode(static::getRandom(16));
        $salt = str_replace(array('+', '='), array('.', ''), $salt);
        $salt = $algo.$salt;
        return crypt($password, $salt);
    }

    public function checkPassword($password, $hash)
    {
        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }
        $hashed_password = crypt($password, $hash);
        if (function_exists('hash_equals')) {
            return hash_equals($hashed_password, $hash);
        }
        $len_newhash = strlen($hashed_password);
        $len_oldhash = strlen($hash);
        if ($len_newhash != $len_oldhash) {
            return false;
        }
        // Timing-safe comparison
        $result = 0;
        for ($i = 0; $i < $userLen; $i++) {
            $result |= (ord($hashed_password[$i]) ^ ord($hash[$i]));
        }
        return 0 === $result;
    }

    /**
     * Generate RSA key pair.
     */
    public function getRSAKeys()
    {
        if (!$this->isOpenSSL) {
            throw new \Exception('No crypto extension OpenSSL');
        }

        $result['public_key']  = null;
        $result['private_key'] = null;

        $openssl_conf = array('private_key_bits' => 2048);
        $pkey = openssl_pkey_new($openssl_conf);
        openssl_pkey_export($pkey, $result['private_key']);
        $result['public_key'] = openssl_pkey_get_details($pkey);
        $result['public_key'] = $result['public_key']['key'];
        return $result;
    }

    public static function UUID4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

}