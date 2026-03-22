<?php

namespace App\Http\Resources\Blog;

use App\Models\Article;
use App\Models\ContentType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Article */
class ArticleDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contentType = $this->contentType;
        $creator = $this->creator;

        return [
            'id' => $this->id,
            'content_type' => [
                'id' => $contentType instanceof ContentType ? $contentType->id : null,
                'name' => $contentType instanceof ContentType ? $contentType->name : null,
                'slug' => $contentType instanceof ContentType ? $contentType->slug : null,
            ],
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'view_count' => $this->view_count,
            'thumbnail_url' => $this->thumbnail_url,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'created_by' => $creator instanceof User ? $creator->id : null,
            'created_by_name' => $creator instanceof User ? $creator->name : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
