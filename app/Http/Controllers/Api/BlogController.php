<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\ArticleIndexRequest;
use App\Http\Requests\Blog\StoreArticleRequest;
use App\Http\Resources\Blog\ArticleCardResource;
use App\Http\Resources\Blog\ArticleDetailResource;
use App\Models\Article;
use App\Models\ContentType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function contentTypes(): JsonResponse
    {
        $contentTypes = ContentType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (ContentType $type): array => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ])
            ->values()
            ->all();

        return $this->successResponse($contentTypes, 'Daftar tipe konten berhasil diambil.');
    }

    public function index(ArticleIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $keyword = trim((string) ($validated['q'] ?? ''));
        $normalizedKeyword = mb_strtolower($keyword);
        $sort = (string) ($validated['sort'] ?? 'latest');
        $perPage = (int) ($validated['per_page'] ?? 12);

        $query = Article::query()
            ->published()
            ->with(['contentType'])
            ->when(isset($validated['content_type_id']), fn ($q) => $q->where('content_type_id', (int) $validated['content_type_id']))
            ->when(isset($validated['content_type_slug']), function ($q) use ($validated) {
                $q->whereHas('contentType', fn ($typeQuery) => $typeQuery->where('slug', $validated['content_type_slug']));
            });

        if ($keyword !== '') {
            $query->where(function ($builder) use ($normalizedKeyword) {
                $pattern = "%{$normalizedKeyword}%";

                $builder->whereRaw('LOWER(title) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(body) LIKE ?', [$pattern]);
            });
        }

        if ($sort === 'most_read') {
            $query->orderByDesc('view_count')
                ->orderByDesc('published_at')
                ->orderByDesc('created_at');
        } else {
            $query->orderByDesc('published_at')
                ->orderByDesc('created_at');
        }

        $paginator = $query
            ->paginate($perPage)
            ->appends($request->query());

        $paginator->setCollection(
            collect(ArticleCardResource::collection($paginator->getCollection())->resolve())
        );

        return $this->paginatedResponse($paginator, 'Daftar artikel berhasil diambil.');
    }

    public function mostRead(Request $request): JsonResponse
    {
        $limit = max(1, min(20, (int) $request->query('limit', 6)));

        $articles = Article::query()
            ->published()
            ->with(['contentType'])
            ->orderByDesc('view_count')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $this->successResponse(
            ArticleCardResource::collection($articles)->resolve(),
            'Daftar artikel paling banyak dibaca berhasil diambil.'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->with(['contentType', 'creator'])
            ->first();

        if (! $article instanceof Article) {
            return $this->errorResponse('Artikel tidak ditemukan.', 404);
        }

        $article->increment('view_count');
        $article->refresh()->loadMissing(['contentType', 'creator']);

        return $this->successResponse(
            (new ArticleDetailResource($article))->resolve(),
            'Detail artikel berhasil diambil.'
        );
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        if ($user->role !== User::ROLE_ADMIN) {
            return $this->errorResponse('Hanya admin yang dapat menambahkan artikel.', 403);
        }

        $validated = $request->validated();
        $isPublished = (bool) ($validated['is_published'] ?? true);

        $article = Article::query()->create([
            'content_type_id' => $validated['content_type_id'],
            'title' => $validated['title'],
            'slug' => $this->generateUniqueSlug($validated['title']),
            'body' => $validated['body'],
            'view_count' => 0,
            'thumbnail_path' => $request->file('thumbnail')->store('blog/thumbnails', 'public'),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($validated['published_at'] ?? now()) : null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $article->loadMissing(['contentType', 'creator']);

        return $this->successResponse(
            (new ArticleDetailResource($article))->resolve(),
            'Artikel berhasil dibuat.',
            201
        );
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'article';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (Article::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
