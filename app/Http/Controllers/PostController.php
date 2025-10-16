<?php

namespace App\Http\Controllers;

use App\Models\PivotLike;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
            ->select("posts.*")
            ->withCount("likes");

        if ($request->filled("search")) {
            $query->where("posts.title", "like", "%" . $request->search . "%");
        }

        $posts = $query->paginate(10);

        return response()->json([
            "data" => [
                $posts,
            ]
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
            $input["img"] = $path;
        }

        $input["post_maker_id"] = $user->id;
        $postCreated = Post::create($input);
        $post = Post::find($postCreated["id"]);

        $count_likes = $post->likes()->count();

        return response()->json([
            "data" => [
                ...$post->toArray(),
                "count_likes" => $count_likes,
                "liked_id" => $count_likes > 0 ? true : false
            ]
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
    public function update(Request $request, $post_id)
    {

        \Log::info($request->all());

        $validator = Validator::make($request->all(), [
            "title" => "sometimes|string|min:3",
            "description" => "sometimes|string|min:10",
            "img" => "sometimes|image|mimes:jpg,jpeg,png|max:4608"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        $current_post = Post::find($post_id);

        if (!$current_post) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        }

        $user = auth()->user();

        if ($user->id !== $current_post->post_maker_id) {
            return response()->json([
                'message'   => 'GET OUT!!',
                'error_code' => '4444',
            ], 403);
        }

        $input = $validator->validated();

        if ($request->hasFile("img")) {
            if (!empty($current_post->img)) {
                Storage::disk("public")->delete($current_post->img);
            }

            $path = $request->file("img")->store("posts", "public");
            $input["img"] = $path;
        }

        $current_post->update($input);

        $count_likes = $current_post->likes()->count();


        $updated_post = Post::find($post_id);
        return response()->json([
            "data" => [
                ...$updated_post->toArray(),
                "count_likes" => $count_likes,
                "liked_id" => $count_likes > 0 ? true : false
            ]
        ], 201);
    }

    public function toLikePost($post_id): JsonResponse
    {
        $user = auth()->user();

        $post = Post::find($post_id);

        if (!$post) {
            return response()->json([
                "message" => "post not found"
            ], 404);
        }

        if ($post->likes()->where("user_id", "=", $user->id)->exists()) {
            return response()->json([
                "message" => "There’s already a like"
            ], 403);
        }

        $post->likes()->create(
            [
                "user_id" => $user->id,
                "post_id" => $post_id
            ]
        );

        return response()->json([
            "message" => "success"
        ], 201);

    }

    public function toUnlikePost($post_id): JsonResponse
    {
        $user = auth()->user();

        $post = Post::find($post_id);

        if (!$post) {
            return response()->json([
                "message" => "post not found"
            ], 404);
        }

        if (!$post->likes()->where("user_id", "=", $user->id)->exists()) {
            return response()->json([
                "message" => "no likes"
            ], 403);
        }

        $like = $post->likes()->where("user_id", "=", $user->id);

        $like->delete();

        return response()->json([
            "message" => "success"
        ], 201);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $post_id): JsonResponse
    {
        $current_post = Post::find($post_id);

        if (!$current_post) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        $user = auth()->user();

        if ($current_post->post_maker_id !== $user->id) {
            return response()->json([
                'message'   => 'GET OUT!!',
                'error_code' => '4444',
            ], 403);
        }

        if (!empty($current_post->img)) {
            Storage::disk("public")->delete($current_post->img);
        }

        $current_post->delete();

        return response()->json([
            "message" => "Successful"
        ], 204);
    }
}
