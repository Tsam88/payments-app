<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }


    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // sometimes we may need to get the exception message and always return a generic message to use
        // for example for internal server errors.
        // keep exception message there and log this if exist
        $devMessage = null;

        $headers = [];

        try {
            // get logger from container
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $exception;
        }

        $error = [
            'code' => 500,
        ];

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $error['code'] = 404;
            $error['message'] = "Not found";
        } elseif ($exception instanceof ModelNotFoundException) {
            $error['code'] = 404;
            $error['message'] = "Not found";
        } elseif ($exception instanceof  \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
            // in case of basic authentication failure (used by telescope), pass the headers
            $headers = $exception->getHeaders();

            $error['code'] = 401;
            $error['message'] = "Unauthorized";
        } elseif ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $error['code'] = 401;
            $error['message'] = "Unauthorized";
        } elseif ($exception instanceof \Illuminate\Routing\Exceptions\InvalidSignatureException) {
            $error['code'] = 401;
            $error['message'] = "Unauthorized";
        } elseif ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $error['code'] = 403;
            $error['message'] = "Forbidden";
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
            $error['code'] = 403;
            $error['message'] = "Forbidden";
        } elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
            $error['code'] = 422;
            $error['message'] = 'Validation error';
            $error['errors'] = $exception->errors();
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $error['code'] = 405;
            $error['message'] = 'Method not allowed';
        } elseif ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            $error['code'] = 429;
            $error['message'] = 'Too many requests';
        } elseif ($exception instanceof \App\Exceptions\InternalServerErrorException) {
            $error['code'] = $exception->getHttpCode();
            $devMessage = $exception->getExceptionMessage();
            $error['message'] = $exception->getDefaultErrorMessage(true);
        } elseif ($exception instanceof \App\Exceptions\AbstractException) {
            $error['code'] = $exception->getHttpCode();
            $error['message'] = $exception->getExceptionMessage();

            // check if exception has it's own errors.
            $exceptionErrors = $exception->getExceptionErrors(true);
            if (null !== $exceptionErrors) {
                $error['errors'] = $exceptionErrors;
            }
        } else {
            $error['code'] = 500;
            $error['message'] = "Unexpected error";
            $error['errors'] = $exception->getMessage();
        }

        // log error
        $logger->log(
            ($devMessage ?? $error['message']),
            array_merge($this->context(), ['exception' => $exception], ['errors' => ($error['errors'] ?? [])]),
        );

        return new JsonResponse($error, $error['code'], $headers);
    }
}
