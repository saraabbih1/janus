<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\MeRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // public function register(RegisterRequest $request)
    // {
    //     $user = User::query()->create($request->validated());
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return $this->successResponse([
    //         'user' => $user,
    //         'token' => $token,
    //     ], 'Opération réussie', 201);
    // }

    public function register(Request $request){
       $req =  $request->validate([
            'name' => ['string', 'max:255'],
            'email' => ['string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['string', 'min:8'],
        ]);
        $user = User::create($req);
        return  response()->json($req);
    }
    public function login(Request $request)
    {
        $request->validate( [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
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
