<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::with('author')
            ->when($request->buscar, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'excerpt'      => ['nullable', 'string', 'max:300'],
            'body'         => ['required', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'is_published' => ['boolean'],
        ]);

        $data['slug']    = Str::slug($data['title']);
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        if (! empty($data['is_published'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Artículo creado correctamente.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'slug'         => ['required', 'string', 'max:255', Rule::unique('posts')->ignore($post)],
            'excerpt'      => ['nullable', 'string', 'max:300'],
            'body'         => ['required', 'string'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'is_published' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $wasPublished = $post->is_published;
        if (! empty($data['is_published']) && ! $wasPublished) {
            $data['published_at'] = now();
        } elseif (empty($data['is_published'])) {
            $data['is_published'] = false;
        }

        $post->update($data);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Artículo actualizado correctamente.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Artículo eliminado correctamente.');
    }
}
