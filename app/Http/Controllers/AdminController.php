<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminController extends Controller
{
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|min:3"
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator->errors());
        }

        if (!\auth()->attempt($validator->validated())) {
            return back()->withErrors([
                "error" => "Не верный логин или пароль"
            ]);
        }

        return redirect("admin/admin_panel");
    }

    public function logout() {
        \auth()->logout();
        return redirect("/login");
    }

    public function ban_user($user_id) {
        $user = User::findOrFail($user_id);

        if (!$user) {
            return back()->withErrors([
                "toast" => "Пользователя с id $user_id не существует"
            ]);
        }

        if ($user->isBanned) {
            return back()->withErrors([
                "toast" => "Пользователь уже забанен"
            ]);
        }

        if ($user->role === "admin") {
            return back()->withErrors([
                "toast" => "вы не можете забанить администратора"
            ]);
        }

        $user->isBanned = true;
        $user->save();

        return back()->with("success", "Пользователь забанен");
    }

    public function unban_user($user_id) {
        $user = User::findOrFail($user_id);

        if (!$user) {
            return back()->withErrors([
                "toast" => "Пользователя с id $user_id не существует"
            ]);
        }

        if ($user->isBanned === false) {
            return back()->withErrors([
                "toast" => "Пользователь и так не забанен"
            ]);
        }

        $user->isBanned = false;
        $user->save();

        return back()->with("success", "Пользователь разбанен");
    }

    public function search_user(Request $request) {
        $users = User::query()
            ->where("nickname", "like", "%" . $request->search_query . "%")
            ->orWhere("email", "like", "%{$request->search_query}%")
            ->get();

        return back()->with("users", $users);
    }
}
