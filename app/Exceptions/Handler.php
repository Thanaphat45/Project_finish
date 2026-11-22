<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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

        // Expired/mismatched CSRF token (419): send the user back to log in
        // with a Thai message instead of the bare "PAGE EXPIRED" screen.
        // Laravel turns TokenMismatchException into a 419 HttpException before
        // these callbacks run, so match on the status code.
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
                return null;
            }
            return redirect()->route('login')
                ->withInput($request->except($this->dontFlash))
                ->with('fail', 'หน้านี้หมดอายุแล้ว กรุณาเข้าสู่ระบบอีกครั้ง');
        });
    }
}
