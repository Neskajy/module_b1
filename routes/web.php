<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckIsAdmin;

Route::view("/", "welcome");

Route::view("/login", "login")->name("login");
Route::post("/admin/login", [AdminController::class, "login"])->name("admin.login");

Route::middleware(CheckIsAdmin::class)->group(function() {
    Route::prefix("admin")->group(function () {
        Route::view("/admin_panel", "admin_panel");
        Route::post("admin/logout", [AdminController::class, "logout"])->name("admin.logout");
        Route::patch("admin/ban_user/{id}", [AdminController::class, "ban_user"])->name("admin.ban_user");
        Route::patch("admin/unban_user/{id}", [AdminController::class, "unban_user"])->name("admin.unban_user");
        Route::post("admin/search_user", [AdminController::class, "search_user"])->name("admin.search_user");
    });
});
