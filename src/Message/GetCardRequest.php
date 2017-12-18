<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway "query pseudo card information" Request,
 * Gets the cardReference and other details from a previous transaction.
 * The original transaction must have requested for the PKN to be saved
 * using `['createCard' => true]`
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#pseudo_card_numbers_pkn
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

class GetCardRequest extends AbstractRequest
{
    /**
     * @return array
     */
    public function getData()
    {
        // Construction of the data will depend on the payment type.

        $paymentType = $this->getPaymentType(true);

        // First five parameters are mandatory and common to all payment methods.

        $data = [];
        $data['merchantId']     = $this->getMerchantId(true);
        $data['projectId']      = $this->getProjectId(true);

        // Reference the previous transaction that was used to save the card details.

        $data['reference'] = $this->getTransactionReference();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }

    /**
     * @return string Absolute endpoint URL.
     */
    public function getEndpoint($path = null)
    {
        if ($this->getPaymentType() === Gateway::PAYMENT_TYPE_DIRECTDEBIT) {
            $path = 'directdebit/pkninfo';
        } else {
            $path = 'creditcard/pkninfo';
        }

        return parent::getEndpoint($path);
    }

    /**
     * Create the response object.
     *
     * @return GetCardResponse
     */
    protected function createResponse(array $data)
    {
        return $this->response = new GetCardResponse($this, $data);
    }
}
