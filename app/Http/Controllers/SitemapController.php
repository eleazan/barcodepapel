<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect();

        // Static pages
        $urls->push(['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0']);
        $urls->push(['loc' => route('catalog'), 'changefreq' => 'daily', 'priority' => '0.9']);
        $urls->push(['loc' => route('delivery'), 'changefreq' => 'monthly', 'priority' => '0.6']);
        $urls->push(['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.5']);
        $urls->push(['loc' => route('blog.index'), 'changefreq' => 'weekly', 'priority' => '0.8']);

        // Legal pages
        $urls->push(['loc' => route('terms'), 'changefreq' => 'yearly', 'priority' => '0.3']);
        $urls->push(['loc' => route('privacy'), 'changefreq' => 'yearly', 'priority' => '0.3']);
        $urls->push(['loc' => route('legal'), 'changefreq' => 'yearly', 'priority' => '0.3']);

        // Categories
        Category::active()->orderBy('sort_order')->each(function ($category) use ($urls) {
            $urls->push([
                'loc'        => route('catalog', ['categoria' => $category->slug]),
                'lastmod'    => $category->updated_at->toW3cString(),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ]);
        });

        // Products
        Product::active()->orderBy('updated_at', 'desc')->each(function ($product) use ($urls) {
            $urls->push([
                'loc'        => route('product', $product),
                'lastmod'    => $product->updated_at->toW3cString(),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ]);
        });

        // Blog posts
        if (class_exists(Post::class)) {
            Post::where('is_published', true)
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->each(function ($post) use ($urls) {
                    $urls->push([
                        'loc'        => route('blog.show', $post),
                        'lastmod'    => $post->updated_at->toW3cString(),
                        'changefreq' => 'monthly',
                        'priority'   => '0.7',
                    ]);
                });
        }

        $content = view('sitemap', compact('urls'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
