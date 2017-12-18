<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

/**
 * GiroCheckout Gateway Capture Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class CaptureRequest extends AbstractRequest
{
    /**
     * @var string The values for the Paydirekt capture "final" flag
     * Not a 1/0 like most of the boolean parameters, but a true/false string.
     */
    const PAYDIREKT_FINAL_FLAG_YES = 'true';
    const PAYDIREKT_FINAL_FLAG_NO = 'false';

    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_CREDIT_CARD,
        Gateway::PAYMENT_TYPE_DIRECTDEBIT,
        Gateway::PAYMENT_TYPE_MAESTRO,
        Gateway::PAYMENT_TYPE_PAYDIREKT,
    ];

    /**
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/capture';

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
        $data['merchantTxId']   = $this->getTransactionId();
        $data['amount']         = (string)$this->getAmountInteger();
        $data['currency']       = $this->getCurrency();

        // NOTE: the online documentation has the purpose and reference swapped
        // around. However, that causes invalid hash errors against the live
        // API. I will assume the documentation is incorrect, at least at the the
        // time this is being written.
        // http://api.girocheckout.de/en:girocheckout:creditcard:start
        // The "purpose" is mandatory for Paydirekt, but optional for other supported
        // payment types.

        if ($purpose = $this->getDescription() || $paymentType === Gateway::PAYMENT_TYPE_PAYDIREKT) {
            $data['purpose'] = substr($purpose, 0, static::PURPOSE_LENGTH);
        }

        // GiroCheckout transaction ID from a previous transaction, which
        // the capture or refund is for.

        $data['reference'] = $this->getTransactionReference();

        if ($paymentType === Gateway::PAYMENT_TYPE_PAYDIREKT) {
            $data = $this->getPaydirektData($data);
        }

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }

    /**
     * @param array $data The data so far
     * @return array
     */
    public function getPaydirektData(array $data = [])
    {
        $data['merchantReconciliationReferenceNumber'] = $this->getMerchantReconciliationReferenceNumber();
        $data['final'] = (bool)$this->getFinal() ? static::PAYDIREKT_FINAL_FLAG_YES : static::PAYDIREKT_FINAL_FLAG_NO;

        return $data;
    }

    /**
     * Create the response object.
     *
     * @return CaptureResponse
     */
    protected function createResponse(array $data)
    {
        return $this->response = new CaptureResponse($this, $data);
    }

    /**
     * @return string
     */
    public function getMerchantReconciliationReferenceNumber()
    {
        return $this->getParameter('merchantReconciliationReferenceNumber');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setMerchantReconciliationReferenceNumber($value)
    {
        return $this->setParameter('merchantReconciliationReferenceNumber', $value);
    }

    /**
     * @return string
     */
    public function getFinal()
    {
        return $this->getParameter('merchantReconciliationReferenceNumber');
    }

    /**
     * @param string $value That will be cast to boolean
     * @return $this
     */
    public function setFinal($value)
    {
        return $this->setParameter('final', $value);
    }
}