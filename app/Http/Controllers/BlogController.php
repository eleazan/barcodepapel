<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Post::published()
            ->with('author')
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('store.blog.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published && $post->published_at?->lte(now()), 404);

        $post->load('author');

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('store.blog.show', compact('post', 'related'));
    }
}
