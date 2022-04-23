<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Services\User\AuthRepository;
use Illuminate\Contracts\Container\Container;

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

        return response()->json([
            "status" => true,
            "data" => $user,
        ], 200);
    }
}
