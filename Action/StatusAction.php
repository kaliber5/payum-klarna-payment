<?php
namespace Payum\Klarna\Payment\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $errors = $model->getArray('errors');

        if (count($errors) > 0) {
            $request->markFailed();

            return;
        }

        if ($model->offsetExists('status')) {
            switch ($model->get('status')) {
                case 'AUTHORIZED':
                    $request->markAuthorized();
                    break;
                case 'CAPTURED':
                case 'PART_CAPTURED':
                    $request->markCaptured();
                    break;
                case 'CANCELLED':
                    $request->markCanceled();
                    break;
                case 'EXPIRED':
                    $request->markExpired();
                    break;
                case 'CLOSED': // @TODO what means 'CLOSED'???
                default:
                    $request->markUnknown();
            }

            if (!$request->isUnknown()) { // try to find out by other values
                return;
            }

        }
        if ($model->offsetExists('capture_id')) {
            $request->markCaptured();

            return;
        }
        if ($model->offsetExists('order_id')) {
            if ($model->get('fraud_status') === 'REJECTED') {
                $request->markFailed();

                return;
            }
            if ($model->get('fraud_status') === 'ACCEPTED' && $model->validateNotEmpty(['captures'], false)) {
                $request->markCaptured();

                return;
            }
            $request->markAuthorized();

            return;
        }

        if (!$model->offsetExists('session_id')) {
            $request->markNew();

            return;
        }

        $local = ArrayObject::ensureArrayObject($model->get('local', []));
        $recurring = $local->offsetExists('recurring') && $local->get('recurring') === true;

        if (!$recurring) {
            if (!$model->offsetExists('authorization_token')) {
                $request->markPending();
            } else {
                $request->markAuthorized();
            }

            return;
        } else {
            if (!$model->offsetExists('token_id')) {
                $request->markPending();
            } else {
                $request->markAuthorized();
            }

            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
