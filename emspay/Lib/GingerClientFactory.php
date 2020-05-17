<?php

namespace Lib;

use Ginger\Ginger;

class GingerClientFactory {

    /**
     * Create Ginger client
     * 
     * @param \Lib\GingerClientFactoryParams $params
     * @return \Ginger\ApiClient
     * @throws \InvalidArgumentException
     */
    public static function create(GingerClientFactoryParams $params) {
        switch ($params->getClientType()) {
            case 'emspay':
                try {
                    if (null === $params->getApiKey()) {
                        throw new \InvalidArgumentException('APIKEY is not provided');
                    }

			  return Ginger::createClient(
				  Helper::GINGER_ENDPOINT,
				  $params->getApiKey(),
				  (null !== $params->getBundleCa()) ?
					  [
						  CURLOPT_CAINFO => Helper::getCaCertPath()
					  ] : []
			  );
                } catch (\Assert\InvalidArgumentException $exception) {
                    throw $exception;
                }
                break;

            case 'ginger':
		    return Ginger::createClient(
			    Helper::GINGER_ENDPOINT,
			    $params->getApiKey(),
			    (null !== $params->getBundleCa()) ?
				    [
					    CURLOPT_CAINFO => Helper::getCaCertPath()
				    ] : []
		    );
            default:
                throw new \InvalidArgumentException('Client is not supported');
        }
    }

}
