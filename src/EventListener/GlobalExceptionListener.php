<?php


namespace App\EventListener;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Transforms exceptions to JSON responses that are machine and human readable
 */
class GlobalExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getException();

        $response = new JsonResponse();

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->setJson(
            json_encode(
                $this->serializeException($exception),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        $event->setResponse($response);
    }

    private function serializeException(\Exception $exception) {
        return [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'previous' => $exception->getPrevious() !== null
                ? $this->serializeException($exception->getPrevious())
                : null
        ];
    }
}
