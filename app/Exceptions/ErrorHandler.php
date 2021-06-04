<?php


namespace App\Exceptions;
use Exception;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class ErrorHandler extends ExceptionHandler
{
    public function __invoke(Request $request, Response $response, Exception $exception) {
        $data = [
            'error' => $exception->getMessage()
        ];
        if ($exception instanceof ValidationException) {
            $data['error_messages'] = $exception->errors();
        }

        if ($this->isDebug()) {
            $data['message'] = $exception->getMessage();
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
            $data['trace'] = $exception->getTrace();
        }

        return $response->withJson($data, 200);
    }
}