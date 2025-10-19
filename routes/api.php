<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post("/register", [UserController::class, "register"]);
Route::post("/login", [UserController::class, "auth"])->name("login");
Route::get("/logout", [UserController::class, "logout"])->middleware("auth:sanctum");

Route::get("/user/{user_id}", [UserController::class, "show"])->middleware("auth:sanctum");

Route::get("posts", [PostController::class, "index"])->middleware("auth:sanctum");
Route::post("posts", [PostController::class, "store"])->middleware("auth:sanctum");
Route::post("posts/{post_id}/like", [PostController::class, "toLikePost"])->middleware("auth:sanctum");
Route::post("posts/{post_id}/unlike", [PostController::class, "toUnlikePost"])->middleware("auth:sanctum");
Route::patch("posts/{post_id}", [PostController::class, "update"])->middleware("auth:sanctum");
Route::delete("posts/{post_id}", [PostController::class, "destroy"])->middleware("auth:sanctum");
