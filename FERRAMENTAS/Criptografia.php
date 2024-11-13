<?php
namespace Ferramentas;

class Criptografia{

    function __construct(){
        
    }

    private function getRandomHex($str) {
        return bin2hex($str);
    }
    
    private function getRandomString($length = 40, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        $str = '';

        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $size - 1)];
        }

        return $str;
    }

    public function fazChave(){
        return $this->getRandomHex($this->getRandomString());
    }


    public function encrypt($mensagem, $chave){
        error_reporting(0);
        $key = hex2bin($chave);
        $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $cipherText = openssl_encrypt(
            $mensagem, 
        'aes-256-ctr',
        $key,
        OPENSSL_RAW_DATA,
        $nonce
        );
        return base64_encode($nonce.$cipherText);
    }

    public function decrypt($mensagem, $chave){
        error_reporting(0);
        $key = hex2bin($chave);

        $mensagem = base64_decode($mensagem);
        $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
        $nonce = mb_substr($mensagem, 0, $nonceSize, '8bit');
        $cipherText = mb_substr($mensagem, $nonceSize, null, '8bit');


        $texto = openssl_decrypt(
            $cipherText,
            'aes-256-ctr',
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
        return $texto;
    }

    public function criptChave($chave){
        return base64_encode($chave);
    }

    public function decriptChave($chave){
        return base64_decode($chave);
    }

}