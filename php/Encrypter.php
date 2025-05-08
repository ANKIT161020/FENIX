<?php
class Encrypter {
    private $key;
    private $method = 'aes-256-cbc';
    
    public function __construct($key = null) {
        // Generate a key if none provided
        if ($key === null) {
            $this->key = openssl_random_pseudo_bytes(32);
        } else {
            $this->key = $key;
        }
    }
    
    public function encrypt($sourcePath, $outputPath) {
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        // Read the file
        $data = file_get_contents($sourcePath);
        
        // Create IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->method));
        
        // Encrypt the data
        $encrypted = openssl_encrypt($data, $this->method, $this->key, 0, $iv);
        
        // Combine IV and encrypted data
        $encryptedData = base64_encode($iv . base64_decode($encrypted));
        
        // Save the encrypted file
        file_put_contents($outputPath, $encryptedData);
        
        return true;
    }
    
    public function decrypt($sourcePath, $outputPath) {
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        // Read the encrypted file
        $encryptedData = file_get_contents($sourcePath);
        $encryptedData = base64_decode($encryptedData);
        
        // Extract IV
        $ivLength = openssl_cipher_iv_length($this->method);
        $iv = substr($encryptedData, 0, $ivLength);
        $encrypted = base64_encode(substr($encryptedData, $ivLength));
        
        // Decrypt the data
        $decrypted = openssl_decrypt($encrypted, $this->method, $this->key, 0, $iv);
        
        // Save the decrypted file
        file_put_contents($outputPath, $decrypted);
        
        return true;
    }
    
    public function getKey() {
        return base64_encode($this->key);
    }
    
    public function setKey($encodedKey) {
        $this->key = base64_decode($encodedKey);
    }
}