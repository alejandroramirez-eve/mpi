<?php

namespace PlacetoPay\MPI\Messages;

use PlacetoPay\MPI\Constants\MPI;
use PlacetoPay\MPI\Contracts\LookupResponse;

class LookupResponseVersionTwo extends LookupResponse
{

    private $redirectUrl;
    private $transactionId;
    private $sessionToken;

    public function __construct($data)
    {
        $this->sessionToken = $data['sessionToken'];
        $this->redirectUrl = $data['redirectURL'];
        $this->transactionId = $data['transactionID'];
    }

    public static function loadFromResult($result)
    {
        parent::loadFromResult($result);

        $data = [
            'sessionToken' => $result['sessionToken'],
            'redirectURL' => isset($result['redirectURL']) ? $result['redirectURL'] : null,
            'transactionID' => isset($result['transactionID']) ? $result['transactionID'] : null
        ];

        return new self($data);
    }

    public function toArray()
    {
        return [
            'threeDSVersion' => MPI::VERSION_TWO,
            'sessionToken' => $this->sessionToken(),
            'redirectUrl' => $this->processUrl(),
            'identifier' => $this->identifier(),
        ];
    }

    /**
     * Return true if the user can be authenticated through the MPI
     * @return bool
     */
    public function canAuthenticate(): bool
    {
        if ($this->redirectUrl) {
            return true;
        }
        return false;
    }

    public function sessionToken()
    {
        return $this->sessionToken;
    }

    public function processUrl(): string
    {
        return $this->redirectUrl;
    }

    public function identifier(): string
    {
        return $this->transactionId;
    }
}