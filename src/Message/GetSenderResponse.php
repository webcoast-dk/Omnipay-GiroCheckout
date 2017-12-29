<?php

namespace Academe\GiroCheckout\Message;

/**
 *
 */

use Omnipay\Common\Message\RedirectResponseInterface;
use Academe\GiroCheckout\Gateway;

class GetSenderResponse extends Response
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == static::RESPONSE_CODE_INITIALISE_SUCCESS;
    }

    /**
     * @return string The BIC.
     */
    public function getBic()
    {
        return $this->getDataItem('bic');
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->getDataItem('accountholder');
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->getDataItem('iban');
    }
}