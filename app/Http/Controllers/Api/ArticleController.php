<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ArticleRequest;
use App\Services\ArticleService;

class ArticleController extends Controller
{

    public function __construct(
        private ArticleService $articleService
    ) {}
    /**
     * Display a listing of articles with optional filters.
     *
     * @param ArticleRequest $request
     * @return JsonResponse
     */
    public function index(ArticleRequest $request): JsonResponse
    {
        // Validate request parameters
        $request->validated();


        // Build cache key
        $cacheKey = 'articles_' . md5(json_encode($request->all()));

        // Try to get from cache first (cache for 5 minutes)
        $result = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Article::query();

            // Apply filters
            if ($request->filled('source')) {
                $query->bySource($request->source);
            }

            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('from_date') || $request->filled('to_date')) {
                $query->byDateRange($request->from_date, $request->to_date);
            }

            // Order by publication date (newest first)
            $query->latestByPublished();

            // Paginate results
            $perPage = $request->get('per_page', 20);
            $articles = $query->paginate($perPage);

            return [
                'data' => $articles->items(),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'from' => $articles->firstItem(),
                    'to' => $articles->lastItem(),
                ]
            ];
        });

        return response()->json($result);
    }

    /**
     * Display the specified article.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(Article $article): JsonResponse
    {
        // Cache individual article for 10 minutes
        $cacheKey = "article_{$article->id}";

        $result = Cache::remember($cacheKey, 600, function () use ($article) {

            if (!$article) {
                return [
                    'success' => false,
                    'message' => 'Article not found'
                ];
            }

            return [
                'data' => $article
            ];
        });

        return response()->json($result);
    }

    /**
     * Get available sources.
     *
     * @return JsonResponse
     */
    public function sources(): JsonResponse
    {
        $sources = $this->articleService->getSources();

        return response()->json([
            'data' => $sources
        ]);
    }

    /**
     * Get available categories.
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = $this->articleService->getStandardCategories();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Get statistics about articles.
     *
     * @return JsonResponse
     */
}
