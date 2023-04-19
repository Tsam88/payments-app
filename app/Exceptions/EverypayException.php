<?php

declare(strict_types = 1);

namespace App\Exceptions;

use Illuminate\Http\Response;

final class EverypayException extends AbstractException
{
    protected const DEFAULT_HTTP_CODE = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected const DEFAULT_ERROR_MESSAGE = 'everypay-failure';
    protected const DEFAULT_SEVERITY = 'error';
}
