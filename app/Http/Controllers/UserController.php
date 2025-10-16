<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "nickname" => "required|max:20",
            "email" => "required|email",
            "password" => "required|min:3"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        $input = $validator->validated();
        $input["password"] = bcrypt($input["password"]);
        $user = User::create($input);

        return response()->json([
            [
                "data" => [
                    "user" => $user->only("nickname", "email")
                ]
            ]
        ], 201);
    }

    public function auth(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|min:3"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        $input = $validator->validated();

        if (Auth::attempt([
            "email" => $input["email"],
            "password" => $input["password"]
        ])) {
            $user = Auth::user();
            $token = $user->createToken("MyApp")->plainTextToken;

            return response()->json([
                [
                    "credentials" => [
                        "token" => $token
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                "message" => "failed"
            ], 401);
        }
    }
}
