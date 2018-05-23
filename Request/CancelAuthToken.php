<?php

namespace Payum\Klarna\Payment\Request;

use Payum\Core\Request\Generic;

/**
 * Class CancelAuthToken
 *
 * @package Payum\Klarna\Payment\Request
 */
class CancelAuthToken extends Generic
{

    /**
     * @return null|string
     */
    public function getAuthToken(): ?string
    {
        if (is_string($this->getModel())) {
            return $this->getModel();
        }

        return null;
    }
}
