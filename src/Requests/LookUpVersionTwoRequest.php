<?php

namespace PlacetoPay\MPI\Requests;

use PlacetoPay\MPI\Constants\MPI;
use PlacetoPay\MPI\Contracts\Request;

class LookUpVersionTwoRequest implements Request
{
    /**
     * @var string
     */
    private $accNumber;

    /**
     * @var string
     */
    private $cardExpiryDate;

    /**
     * @var string
     */
    private $purchaseAmount;

    /**
     * @var string
     */
    private $purchaseCurrency;

    /**
     * @var string
     */
    private $redirectURI;

    /**
     * @var string
     */
    private $threeDSAuthenticationInd;

    /**
     * @var string
     */
    private $reference;

    public function __construct($data)
    {
        $this->accNumber = $data['card']['number'];
        $this->cardExpiryDate = $data['card']['expirationYear'] . $data['card']['expirationMonth'];
        $this->purchaseAmount = $data['amount'];
        $this->purchaseCurrency = $data['currency'];
        $this->redirectURI = $data['redirectUrl'];
        $this->threeDSAuthenticationInd = '01'; //Validate this
        $this->reference = $data['reference'] ?? null;
    }

    public function toArray(): array 
    {
        return [
            'acctNumber' => $this->accNumber,
            'cardExpiryDate' => $this->cardExpiryDate,
            'purchaseAmount' => $this->purchaseAmount,
            'purchaseCurrency' => $this->purchaseCurrency,
            'redirectURI' => $this->redirectURI,
            'threeDSAuthenticationInd' => $this->threeDSAuthenticationInd,
            'reference' => $this->reference,
        ];
    }

    public function endpoint(): string
    {
        return MPI::LOOKUP_ENDPOINTS[MPI::VERSION_TWO];
    }
}
