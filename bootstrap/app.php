<?php

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // ← add this
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated', 'data' => null], 401);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'data' => $e->errors()], 422);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return response()->json(['status' => 'error', 'message' => 'Resource not found', 'data' => null], 404);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden', 'data' => null], 403);
        });
    })->create();
