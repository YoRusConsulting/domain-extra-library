<?php

namespace YoRus\DomainExtraLibrary\Domain\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * JsonRequestListener
 */
class JsonRequestListener
{
    /**
     * @param ControllerEvent $event event
     *
     * @return void
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if ('json' !== $request->getContentTypeFormat() || !$request->getContent()) {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new BadRequestHttpException(sprintf('invalid json body: %s', json_last_error_msg()));
        }

        $request->request->replace(is_array($data) ? $data : array());
    }
}
