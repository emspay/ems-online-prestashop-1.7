<?php

namespace Lib;

class GingerClientFactory {

    /**
     * Create Ginger client
     * 
     * @param \Lib\GingerClientFactoryParams $params
     * @return GingerPayments\Payment\Client
     * @throws \Assert\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public static function create(GingerClientFactoryParams $params) {
        switch ($params->getClientType()) {
            case 'emspay':
                try {
                    if (null === $params->getApiKey()) {
                        throw new \InvalidArgumentException('APIKEY is not provided');
                    }

                    $ginger = \GingerPayments\Payment\Ginger::createClient( $params->getApiKey());
                    if (null !== $params->getBundleCa()) {
                        $ginger->useBundledCA();
                    }
                    return $ginger;
                } catch (\Assert\InvalidArgumentException $exception) {
                    throw $exception;
                }
                break;

            case 'ginger':
                return \GingerPayments\Payment\Ginger::createClient($params->getApiKey());
            default:
                throw new \InvalidArgumentException('Client is not supported');
        }
    }

}
