<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\User\AuthRepository;
use Auth;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Validator;

class AuthController extends Controller
{

    protected $authRepository;

    public function __construct(Container $app)
    {
        $this->authRepository = $app->make(AuthRepository::class);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users,name|max:20',
            'email' => 'required|email|unique:users,email|max:50',
            'password' => 'required|min:8|max:20',
            'confirm_password' => 'required|same:password',
            'role' => 'required|exists:groups,name'
          ]);
      
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "error" => $validator->errors(),
            ], 422);
        }
        $input = $request->only('name', 'email', 'password', 'role');

        $user = $this->authRepository->store($input);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "status" => true,
            "data" => $user,
            "access_token" => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password')))
        {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = $this->authRepository->getByMail($request['email']);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token, 
            'token_type' => 'Bearer',
        ]);
    }

    // method for user logout and delete token
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'success' => true,
            'message' => 'You have successfully logged out and the token was successfully deleted'
        ];
    }
}
