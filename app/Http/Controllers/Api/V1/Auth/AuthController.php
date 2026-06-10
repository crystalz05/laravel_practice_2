<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    protected AuthService $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request){
        $result = $this->authService->register($request->validated());
        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registeration Successful', 201);
    }

    public function login(LoginRequest $request){
        $result = $this->authService->login($request->validated());
        return $this->success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token']
        ], 'Login successful');
    }

    public function logout(Request $request){
        $this->authService->logout($request->user());
        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request){
        return $this->success(new UserResource($request->user()), 'User fetched');
    }


}
