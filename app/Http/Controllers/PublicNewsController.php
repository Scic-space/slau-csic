<?php

namespace App\Http\Controllers;

use App\Models\News;
use Inertia\Inertia;
use Inertia\Response;

class PublicNewsController extends Controller
{
    public function index(): Response
    {
        $featured = News::published()
            ->featured()
            ->with('author')
            ->latest('published_at')
            ->first();

        $articles = News::published()
            ->with('author')
            ->latest('published_at')
            ->get();

        return Inertia::render('public/News', [
            'featured' => $featured ? array_merge($featured->only([
                'id', 'title', 'slug', 'excerpt', 'category', 'content_type',
                'source_name', 'source_url', 'published_at',
            ]), [
                'embed_url' => $featured->embed_url,
                'video_url' => $featured->public_video_url,
                'thumbnail_url' => $featured->public_thumbnail_url,
            ]) : null,
            'articles' => $articles->map(fn (News $news) => array_merge($news->only([
                'id', 'title', 'slug', 'excerpt', 'category', 'content_type',
                'source_name', 'source_url', 'published_at',
            ]), [
                'embed_url' => $news->embed_url,
                'video_url' => $news->public_video_url,
                'thumbnail_url' => $news->public_thumbnail_url,
            ])),
        ]);
    }

    public function show(string $slug): Response
    {
        $news = News::published()
            ->where('slug', $slug)
            ->with('author')
            ->firstOrFail();

        $related = News::published()
            ->where('id', '!=', $news->id)
            ->where('category', $news->category)
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map(fn (News $n) => $n->only([
                'id', 'title', 'slug', 'excerpt', 'category', 'content_type',
                'source_name', 'published_at',
            ]));

        return Inertia::render('public/NewsShow', [
            'article' => array_merge($news->only([
                'title', 'slug', 'excerpt', 'content', 'category', 'content_type',
                'source_name', 'source_url', 'published_at',
            ]), [
                'author' => $news->author?->name ?? 'CSIC Admin',
                'embed_url' => $news->embed_url,
                'video_url' => $news->public_video_url,
                'thumbnail_url' => $news->public_thumbnail_url,
            ]),
            'related' => $related,
        ]);
    }
}
