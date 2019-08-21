<?php

namespace Lib;

class GingerClientFactoryParams {

    private $clientType;
    private $apiKey;
    private $product;
    private $bundleCa;

    public function __construct($clientType, $apiKey, $bundleCa) {
        $this->clientType = $clientType;
        $this->apiKey = $apiKey;
        $this->bundleCa = $bundleCa;
    }

    public function getClientType() {
        return $this->clientType;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getBundleCa() {
        return $this->bundleCa;
    }

}
