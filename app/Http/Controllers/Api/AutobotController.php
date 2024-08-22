<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AutobotResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Models\User;

class AutobotController extends Controller
{
    public function index()
    {
        $autobots = User::paginate(10);
        return AutobotResource::collection($autobots);
    }

    public function posts(User $autobot)
    {
        $posts = $autobot->posts()->paginate(10);
        return PostResource::collection($posts);
    }

    public function comments(Post $post)
    {
        $comments = $post->comments()->paginate(10);
        return CommentResource::collection($comments);
    }
}
