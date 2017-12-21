<?php

namespace Academe\GiroCheckout\Message;

/**
 * Handles the Capture, Refund and Void responses.
 */

use Academe\GiroCheckout\Gateway;

class CaptureResponse extends Response
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == static::RESPONSE_CODE_INITIALISE_SUCCESS
            && $this->getReasonCode() == Gateway::RESULT_PAYMENT_SUCCESS;
    }

    /**
     * @return string GiroCheckout transaction ID of the original base transaction
     */
    public function getParentTransactionReference()
    {
        return $this->getDataItem('referenceParent');
    }

    /**
     * For CC and DD Capture and Refund.
     *
     * @return string Unique transaction id of the merchant
     */
    public function getTransactionId()
    {
        return $this->getDataItem('merchantTxId');
    }

    /**
     * For CC and DD Capture and Refund.
     *
     * @return int Expressed in minor units
     */
    public function getAmountInteger()
    {
        return (int)$this->getDataItem('amount');
    }

    /**
     * For CC and DD Capture and Refund.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getDataItem('currency');
    }
}
