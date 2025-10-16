<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->join("users", "posts.post_maker_id", "=", "users.id")
            ->where("users.isBanned", false)
            ->select("posts.*");

        if ($request->filled("search")) {
            $query->where("posts.title", "like", "%" . $request->search . "%");
        }

        $posts = $query->paginate(10);

        return response()->json([
            $posts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string|min:3",
            "description" => "nullable|string|min:10",
            "img" => "nullable|image|mimes:jpg,jpeg,png|max:4608"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $input = $validator->validated();

        if ($request->hasFile("img")) {
            $path = $request->file("img")->store("posts", "public");
            $input["img"] = asset(Storage::url($path));
        }

        $input["post_maker_id"] = $user->id;
        $postCreated = Post::create($input);
        $post = Post::find($postCreated["id"]);

        return response()->json([
            "data" => $post
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
