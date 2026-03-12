<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\MeRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::query()->create($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Opération réussie', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return $this->errorResponse($this->emptyObject(), 'Erreur', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
        ]);
    }

    public function logout(LogoutRequest $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse($this->emptyObject());
    }

    public function me(MeRequest $request)
    {
        return $this->successResponse([
            'user' => $request->user(),
        ]);
    }
}
