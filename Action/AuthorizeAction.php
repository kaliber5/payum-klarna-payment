<?php
namespace Payum\Klarna\Payment\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
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
    use HttpExceptionTrait;

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
            try {
                $this->gateway->execute(new CreateSession($model));
            } catch (HttpException $e) {
                $this->handleHttpException($model, $e);
            }

            return;
        }

        if (!$model->offsetExists('authorization_token') && !$model->offsetExists('token_id')) {
            try {
                $this->gateway->execute(new GetAuthorizationToken($model));
            } catch (HttpException $e) {
                $this->handleHttpException($model, $e);
            }
        }

        if ($model->offsetExists('authorization_token') && !$model->offsetExists('token_id')) {

            $local = ArrayObject::ensureArrayObject($model->get('local', []));
            if ($local->offsetExists('recurring') && $local->get('recurring') === true) {

                try {
                    $model->replace(['intended_use' => 'SUBSCRIPTION']);
                    $this->gateway->execute(
                        new CreateCustomerToken($model)
                    );// deletes auth token, isn't necessary if we have the customer token
                    $this->gateway->execute(new Cancel($model));
                } catch (\Exception $e) {
                    // @TODO handle error if necessary
                }
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
