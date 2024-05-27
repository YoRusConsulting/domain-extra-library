<?php

namespace YoRus\DomainExtraLibrary\Domain\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

/**
 * ExceptionListener.
 */
class ExceptionListener
{
    public const HTTP_ERROR_CODES = [
        Response::HTTP_NOT_FOUND,
        Response::HTTP_BAD_REQUEST,
        Response::HTTP_CONFLICT,
    ];

    public const HTTP_CONTEXT_PREFIX = 'HTTP.';

    /**
     * @param ExceptionEvent $event event
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if (false === $e instanceof ValidationFailedException && false === $e instanceof HandlerFailedException) {
            return;
        }

        $mainErrorCode = null;
        $mainErrorMessage = null;

        $nestedExceptions = $e->getNestedExceptions();
        $errors = [];

        foreach ($nestedExceptions as $violation) {
            $errors[] = [
                'message' => $violation->getMessage(),
            ];

            if (null !== $violation->getCode() && null === $mainErrorCode) {
                if (str_contains($violation->getCode(), self::HTTP_CONTEXT_PREFIX)) {
                    $code = str_replace(self::HTTP_CONTEXT_PREFIX, '', $violation->getCode());
                    if (in_array($code, self::HTTP_ERROR_CODES)) {
                        $mainErrorCode = $code;
                        $mainErrorMessage = Response::$statusTexts[$mainErrorCode];
                    }
                }
            }
        }

        if (null === $mainErrorCode) {
            $mainErrorCode = Response::HTTP_BAD_REQUEST;
            $mainErrorMessage = Response::$statusTexts[Response::HTTP_BAD_REQUEST];
        }

        $body = [
            'code' => $mainErrorCode,
            'message' => $mainErrorMessage,
            'errors' => $errors,
        ];

        $event->setResponse(new JsonResponse($body, $mainErrorCode));
    }
}
