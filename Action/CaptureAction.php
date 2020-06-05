<?php
namespace Payum\Klarna\Payment\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Sync;
use Payum\Klarna\Payment\Request\CaptureOrder;
use Payum\Klarna\Payment\Request\CreateOrder;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use HttpExceptionTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($status = new GetHumanStatus($model));
        if (!$status->isAuthorized()) {
            throw new \LogicException('Status is not authorized');
        }

        if ($model->validateNotEmpty(['order_id'], false) === false) {
            // create an order if we have no order id
            if ($model->validateNotEmpty(['token_id'], false) || $model->validateNotEmpty(['authorization_token'],false)) {
                if ($model->offsetExists('captured_amount')) {
                    $capAmount = $model['captured_amount'];
                }
                try {
                    $this->gateway->execute(new CreateOrder($model));
                    $this->gateway->execute(new Sync($model));
                } catch (HttpException $e) {
                    $this->handleHttpException($model, $e);
                }
                $this->gateway->execute($status = new GetHumanStatus($model));
            } else {
                throw new \LogicException('Cannot create order without token');
            }
        }
        // if autocapture was set, the order state should be captured now
        if ($status->isAuthorized()) {
            // Capture order otherwise
            if (!empty($capAmount)) {
                $model->replace(['captured_amount' => $capAmount]);
            }
            try {
                $this->gateway->execute(new CaptureOrder($model));
                $this->gateway->execute(new Sync($model));
            } catch (HttpException $e) {
                $this->handleHttpException($model, $e);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
