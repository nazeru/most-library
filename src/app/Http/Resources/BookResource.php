<?php

namespace App\Http\Resources;

use App\Enums\BookCopyStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'isbn13' => $this->isbn13,
            'published' => $this->published ?? 'Unknown',
            'author' => $this->author->name ?? 'Unknown',
            'publisher' => $this->publisher->name ?? 'Unknown',
            'available_copies' => $this->availableCopiesCount(),
        ];
    }

    private function availableCopiesCount(): int
    {
        return $this->copies()
                    ->where('status', BookCopyStatus::AVAILABLE)
                    ->count();
    }

}
