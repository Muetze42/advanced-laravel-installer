<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     * Determine if the exception handler response should be JSON.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $e
     *
     * @return bool
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->expectsJson() || str_starts_with(trim($request->path(), '/'), 'api');
    }
}
