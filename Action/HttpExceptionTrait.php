<?php

namespace Payum\Klarna\Payment\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;

/**
 * Trait HttpExceptionTrait
 *
 * @package Action
 */
trait HttpExceptionTrait
{
    public function handleHttpException(ArrayObject $model, HttpException $exception)
    {
        $errors = $model->getArray('errors');
        $errors[] = [$exception->getCode() => $exception->getMessage()];
        $model->replace(['errors' => (array) $errors]);
    }
}
