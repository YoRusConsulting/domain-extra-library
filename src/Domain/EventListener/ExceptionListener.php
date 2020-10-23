<?php

namespace AppInWeb\DomainExtraLibrary\Domain\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

/**
 * ExceptionListener.
 */
class ExceptionListener
{
    const HTTP_ERROR_CODES = [
        Response::HTTP_NOT_FOUND,
        Response::HTTP_BAD_REQUEST,
        Response::HTTP_CONFLICT,
    ];

    const HTTP_CONTEXT_PREFIX = 'HTTP.';

    /**
     * @param GetResponseForExceptionEvent $event event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $e = $event->getException();

        if (false === $e instanceof ValidationFailedException) {
            return;
        }

        $mainErrorCode = null;
        $mainErrorMessage = null;

        $violations = $e->getViolations();
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'path' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];

            if (null !== $violation->getCode() && null === $mainErrorCode) {
                if (false !== strpos($violation->getCode(), self::HTTP_CONTEXT_PREFIX)) {
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
