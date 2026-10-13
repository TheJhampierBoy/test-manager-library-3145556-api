<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Entities\User;
use Illuminate\Support\Facades\DB;

final class EloquentUserRepository implements UserRepository
{
    private const TABLE = 'library."user"';

    public function findByUsername(string $username): ?User
    {
        $row = DB::table(DB::raw('library."user"'))->where('username', $username)->first();
        return $row ? new User($row->id, $row->username, $row->password_hash, $row->role) : null;
    }

    public function findById(string $id): ?User
    {
        $row = DB::table(DB::raw('library."user"'))->where('id', $id)->first();
        return $row ? new User($row->id, $row->username, $row->password_hash, $row->role) : null;
    }

    public function save(User $user): void
    {
        $data = ['id' => $user->id(), 'username' => $user->username(), 'password_hash' => $user->passwordHash(), 'role' => $user->role()];
        $exists = DB::table(DB::raw('library."user"'))->where('id', $user->id())->exists();
        $exists
            ? DB::table(DB::raw('library."user"'))->where('id', $user->id())->update($data)
            : DB::table(DB::raw('library."user"'))->insert($data);
    }

    public function existsByUsername(string $username): bool
    {
        return DB::table(DB::raw('library."user"'))->where('username', $username)->exists();
    }
}
