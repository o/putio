<?php
if (!function_exists('curl_init')) {
    throw new PutioException('Putio needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new PutioException('Putio needs the JSON PHP extension.');
}

class PutioException extends Exception
{
    /**
     * 
     */
}

/**
 * Provides access to the Putio JSON API
 *
 * @package Putio PHP
 * @author Osman Üngür <osmanungur@gmail.com>
 */
class Putio
{
    private $apiKey;
    private $apiSecret;
    private $curl;
    private $curlOptions = array(CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60, CURLOPT_USERAGENT => 'putio-php-1.0');
    private $apiUrl = 'http://api.put.io/v1/';
    
    function __construct($apiKey, $apiSecret)
    {
        $this->setApiKey($apiKey);
        $this->setApiSecret($apiSecret);
    }
    
    private function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }
    
    private function getApiKey()
    {
        return $this->apiKey;
    }
    
    private function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
        return $this;
    }
    
    private function getApiSecret()
    {
        return $this->apiSecret;
    }
    
    private function getApiUrl()
    {
        return $this->apiUrl;
    }
    
    private function makeRequest($url)
    {
        $this->curl = curl_init();
        $this->curlOptions[CURLOPT_URL] = $url;
        curl_setopt_array($this->curl, $this->curlOptions);
        $content = curl_exec($this->curl);
        curl_close($this->curl);
        return $content;
    }
    
    private function getResponse($class, $method, array $attributes = NULL)
    {
        $params = new ArrayObject;
        if (count($attributes)) {
            $params = new ArrayObject($attributes[0]);
        }
        $request = array(
            'api_key' => $this->getApiKey(),
            'api_secret' => $this->getApiSecret(),
            'params' => $params
        );
        $jsonresponse = $this->makeRequest($this->getApiUrl() . $class . "?method=" . $method . "&request=" . json_encode($request));
        $response = json_decode($jsonresponse);
        if (!$response) {
            throw new PutioException("Error Processing Request", 1);
        } elseif ($response->error) {
            throw new PutioException($response->error_message, 1);
        }
        return $response;
    }
    
    public function __call($method, array $arguments = NULL)
    {
        $name = preg_split('/(?<=[a-z])(?=[A-Z])/', $method);
        $name = array_map('strtolower', $name);
        return $this->getResponse($name[0], $name[1], $arguments);
    }
}

// Construct with api key and secret
$putio = new Putio('api_key', 'api_secret');

// If you make an api call with parameters you must pass an associated array
$fileinfo = $putio->FilesInfo(array(
    'id' => 6017104
));

// API call without parameters
$user = $putio->UserInfo();

// $putio->UserFriends calls User class and Friends function
$friends = $putio->UserFriends();

// False class name requests or null curl responses throws Putio Exception "PutioException: Error Processing Request"
// $foo = $putio->FooList();

// False function name or argument requests throws Putio Exception with error message like "PutioException: service "user" has no method "delete_action"
// $bar = $putio->UserDelete();

// Printing results
echo '<pre>';
print_r($fileinfo);
print_r($user);
print_r($friends);
echo '</pre>';