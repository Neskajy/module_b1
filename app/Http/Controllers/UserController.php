<?php

namespace App\Http\Controllers;

use http\Env\Response;
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

    public function auth(Request $request): JsonResponse
    {
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

    public function logout(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "message" => "Вы и так не авторизованы, лол"
            ], 403);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logged out"
        ], 204);
    }

    public function show(Request $request, $user_id): JsonResponse
    {
        $user = User::query()
            ->where("id", $user_id)
            ->where("role", "!=", "admin")
            ->select("users.*")
            ->first();

        if (!$user) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        if ($user->isBanned) {
            return response()->json([
                "message" => "User has been banned"
            ], 404);
        }

        $posts = $user->posts()->paginate(10);

        return response()->json([
            "data" => [
                "nickname" => $user->nickname,
                "posts" => $posts
            ]
        ], 200);
    }
}
