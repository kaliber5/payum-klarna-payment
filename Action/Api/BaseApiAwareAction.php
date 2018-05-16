<?php
namespace Payum\Klarna\Payment\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Klarna\Payment\Api;

/**
 * Class BaseApiAwareAction
 *
 * @package Payum\Klarna\Payment\Action\Api
 */
abstract class BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @return null|Api
     */
    public function getApi():? Api
    {
        return $this->api;
    }
}
