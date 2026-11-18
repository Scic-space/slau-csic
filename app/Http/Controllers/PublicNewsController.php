<?php

namespace App\Http\Controllers;

use App\Models\News;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PublicNewsController extends Controller
{
    public function index(): Response
    {
        $newsQuery = News::published()
            ->with('author')
            ->withCount('views')
            ->withMax('views as last_viewed_at', 'viewed_at');

        $featured = (clone $newsQuery)
            ->featured()
            ->latest('published_at')
            ->first();

        $articles = (clone $newsQuery)
            ->latest('published_at')
            ->get();

        return Inertia::render('public/News', [
            'featured' => $featured ? $this->newsPayload($featured) : null,
            'articles' => $articles->map(fn (News $news) => $this->newsPayload($news)),
        ]);
    }

    public function show(string $slug): Response
    {
        $news = News::published()
            ->where('slug', $slug)
            ->with('author')
            ->firstOrFail();

        $news->markAsViewedBySession();
        $news->loadCount('views')->loadMax('views as last_viewed_at', 'viewed_at');

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
            'article' => array_merge($this->newsPayload($news, true), [
                'author' => $news->author?->name ?? 'CSIC Admin',
            ]),
            'related' => $related,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function newsPayload(News $news, bool $withContent = false): array
    {
        $fields = [
            'id', 'title', 'slug', 'excerpt', 'category', 'content_type',
            'source_name', 'source_url', 'published_at',
        ];

        if ($withContent) {
            $fields[] = 'content';
        }

        return array_merge($news->only($fields), [
            'view_count' => $news->views_count,
            'last_viewed_at' => $news->last_viewed_at ? Carbon::parse($news->last_viewed_at)->toIso8601String() : null,
            'embed_url' => $news->embed_url,
            'video_url' => $news->public_video_url,
            'thumbnail_url' => $news->public_thumbnail_url,
        ]);
    }
}
