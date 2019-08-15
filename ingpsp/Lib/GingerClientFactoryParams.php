<?php

namespace Lib;

class GingerClientFactoryParams {

    private $clientType;
    private $apiKey;
    private $product;
    private $bundleCa;

    public function __construct($clientType, $apiKey, $product, $bundleCa) {
        $this->clientType = $clientType;
        $this->apiKey = $apiKey;
        $this->product = $product;
        $this->bundleCa = $bundleCa;
    }

    public function getClientType() {
        return $this->clientType;
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getProduct() {
        return $this->product;
    }

    public function getBundleCa() {
        return $this->bundleCa;
    }

}
