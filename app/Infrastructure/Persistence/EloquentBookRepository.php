<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\Outbound\BookRepository;
use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Isbn;
use App\Domain\ValueObjects\Quantity;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

/** Persistence adapter: book repository (T-12). */
final class EloquentBookRepository implements BookRepository
{
    private const TABLE = 'library.book';

    public function findById(string $id): ?Book
    {
        $row = DB::table(self::TABLE)->where('id', $id)->first();
        return $row ? $this->hydrate($row) : null;
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) return [];
        $rows = DB::table(self::TABLE)
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->get();
        return $rows->map(fn($r) => $this->hydrate($r))->all();
    }

    public function save(Book $book): void
    {
        $data = [
            'id'               => $book->id(),
            'title'            => $book->title(),
            'author'           => $book->author(),
            'isbn'             => (string) $book->isbn(),
            'category_id'      => $book->categoryId(),
            'total_copies'     => $book->totalCopies(),
            'available_copies' => $book->availableCopies(),
            'cover_key'        => $book->coverKey(),
            'deleted_at'       => $book->deletedAt()?->format('Y-m-d H:i:sP'),
            'version'          => $book->version() + 1,
        ];

        $exists = DB::table(self::TABLE)->where('id', $book->id())->exists();
        if ($exists) {
            // Optimistic concurrency: update only if version matches (ADR-002)
            $affected = DB::table(self::TABLE)
                ->where('id', $book->id())
                ->where('version', $book->version())
                ->update($data);
            if ($affected === 0) {
                throw new \App\Domain\Exceptions\BookNotAvailableException($book->title());
            }
        } else {
            DB::table(self::TABLE)->insert($data);
        }
    }

    public function existsByIsbnActive(string $isbn, ?string $excludeId = null): bool
    {
        $q = DB::table(self::TABLE)->where('isbn', $isbn)->whereNull('deleted_at');
        if ($excludeId) $q->where('id', '!=', $excludeId);
        return $q->exists();
    }

    public function hasOpenLoans(string $bookId): bool
    {
        return DB::table('library.loan_item')
            ->where('book_id', $bookId)
            ->whereNull('returned_at')
            ->exists();
    }

    public function search(?string $q, ?string $categoryId, int $page, int $size): array
    {
        $query = DB::table(self::TABLE)->whereNull('deleted_at');

        if ($q) {
            $like = '%' . $q . '%';
            $query->where(fn($sub) => $sub->where('title', 'ilike', $like)->orWhere('author', 'ilike', $like));
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $total = (clone $query)->count();
        $rows  = $query->orderBy('title')->offset(($page - 1) * $size)->limit($size)->get();

        return [
            'items' => $rows->map(fn($r) => $this->hydrate($r))->all(),
            'total' => $total,
        ];
    }

    private function hydrate(object $row): Book
    {
        return new Book(
            id:              $row->id,
            title:           $row->title,
            author:          $row->author,
            isbn:            new Isbn($row->isbn),
            categoryId:      $row->category_id,
            totalCopies:     new Quantity((int) $row->total_copies),
            availableCopies: (int) $row->available_copies,
            coverKey:        $row->cover_key,
            deletedAt:       $row->deleted_at
                ? new DateTimeImmutable($row->deleted_at, new DateTimeZone('UTC'))
                : null,
            version:         (int) $row->version,
        );
    }
}
