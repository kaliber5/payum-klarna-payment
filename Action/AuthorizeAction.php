<?php
namespace Payum\Klarna\Payment\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Request\CreateSession;

class AuthorizeAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(
            [
                'purchase_country',
                'purchase_currency',
                'locale',
                'order_amount',
                'order_tax_amount',
                'order_lines',
            ]
        );
        $orderLines = $model['order_lines'];
        foreach ($orderLines as $orderLine) {
            $order = ArrayObject::ensureArrayObject($orderLine);
            $order->validateNotEmpty(
                [
                    'name',
                    'quantity',
                    'unit_price',
                    'total_amount',
                ]
            );
        }

        $this->gateway->execute(new CreateSession($model));


        throw new \LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
