<?php

namespace App\Http\Resources\Blog;

use App\Models\Article;
use App\Models\ContentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Article */
class ArticleCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contentType = $this->contentType;

        return [
            'id' => $this->id,
            'content_type' => [
                'id' => $contentType instanceof ContentType ? $contentType->id : null,
                'name' => $contentType instanceof ContentType ? $contentType->name : null,
                'slug' => $contentType instanceof ContentType ? $contentType->slug : null,
            ],
            'title' => $this->title,
            'slug' => $this->slug,
            'view_count' => $this->view_count,
            'thumbnail_url' => $this->thumbnail_url,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
        ];
    }
}
