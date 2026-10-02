<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Queries;

use App\Models\User as UserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use src\Identity\Domain\Entity\UserEntity;
use src\Shared\Contract\PaginationInterface;

class PaginateUserEloquentRepository
{
    public function paginate(int $page = 1, int $perPage = 10, string $filter = '', string $orderBy = 'DESC'): PaginationInterface
    {
        if ($page < 1 || $perPage < 1) {
            throw new InvalidArgumentException('A página e a quantidade de itens por página devem ser maiores que zero.');
        }

        $paginator = UserModel::query()
            ->when($filter !== '', fn (Builder $query): Builder => $query->where(
                fn (Builder $filterQuery): Builder => $filterQuery
                    ->where('name', 'like', "%{$filter}%")
                    ->orWhere('email', 'like', "%{$filter}%"),
            ))
            ->orderBy('created_at', $orderBy)
            ->orderBy('id', $orderBy)
            ->paginate($perPage, ['*'], 'page', $page);

        $users = $paginator->getCollection()
            ->map(fn (UserModel $model): UserEntity => UserEntity::fromModel($model));

        return new class($users, $paginator->total(), $paginator->perPage(), $paginator->currentPage(), $paginator->getOptions()) extends LengthAwarePaginator implements PaginationInterface {};
    }
}
