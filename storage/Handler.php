<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Throwable;
use Illuminate\Support\Facades\Route;

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
        return $request->expectsJson() || str_starts_with(trim($request->path(), '/') . '/', 'api/');
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function unauthenticated(
        $request,
        AuthenticationException $exception
    ): Response|JsonResponse|RedirectResponse {
        if ($this->shouldReturnJson($request, $exception)) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }
        if ($redirectTo = $exception->redirectTo()) {
            return redirect()->guest($redirectTo);
        }
        if (Route::has('login')) {
            return redirect()->guest(route('login'));
        }
        abort(401);
    }
}
