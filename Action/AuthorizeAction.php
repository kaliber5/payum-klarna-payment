<?php
namespace Payum\Klarna\Payment\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;
use Payum\Klarna\Payment\Request\CreateCustomerToken;
use Payum\Klarna\Payment\Request\CreateSession;
use Payum\Klarna\Payment\Request\GetAuthorizationToken;

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

        if (!$model->offsetExists('session_id')) {
            $this->gateway->execute(new CreateSession($model));

            return;
        }

        if (!$model->offsetExists('authorization_token') && !$model->offsetExists('customer_token')) {
            $this->gateway->execute(new GetAuthorizationToken($model));
        }

        if ($model->offsetExists('authorization_token') && !$model->offsetExists('customer_token')) {

            $local = ArrayObject::ensureArrayObject($model->get('local'));
            if ($local->offsetExists('recurring') && $local->get('recurring') === true) {
                $model->replace(['intended_use' => 'SUBSCRIPTION']);
                $this->gateway->execute(new CreateCustomerToken($model));
                // deletes auth token, isn't necessary if we have the customer token
                $this->gateway->execute(new Cancel($model));
            }
        }
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
