<?php


namespace App\Exceptions;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class PhpErrorHandler extends ErrorHandler
{
    public function __invoke(Request $request, Response $response, Throwable $error) {
        $data = [
            'error' => 'Something went wrong!'
        ];

        if ($this->isDebug()) {
            $data['message'] = $error->getMessage();
            $data['file'] = $error->getFile();
            $data['line'] = $error->getLine();
            $data['trace'] = $error->getTrace();
        }

        return $response->withJson($data, 500);
    }
}