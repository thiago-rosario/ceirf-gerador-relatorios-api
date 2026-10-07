<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\User as UserModel;
use src\Modules\Shared\Contract\PaginationInterface;

class PaginateUserEloquentRepository
{
    public function paginate(int $page = 1, int $perPage = 10, string $filter = '', string $orderBy = 'DESC'): PaginationInterface
    {
        if ($page < 1 || $perPage < 1) {
            throw new InvalidArgumentException('A página e a quantidade de itens por página devem ser maiores que zero.');
        }

        $direction = match (strtoupper($orderBy)) {
            'ASC' => 'asc',
            'DESC' => 'desc',
            default => throw new InvalidArgumentException('A ordenação deve ser ASC ou DESC.'),
        };

        $paginator = UserModel::query()
            ->with('roles')
            ->when($filter !== '', fn (Builder $query): Builder => $query->where(
                fn (Builder $filterQuery): Builder => $filterQuery
                    ->where('name', 'like', "%{$filter}%")
                    ->orWhere('email', 'like', "%{$filter}%"),
            ))
            ->orderBy('created_at', $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $users = $paginator->getCollection()
            ->map(fn (UserModel $model): UserEntity => UserEntity::fromModel($model));

        return new class($users, $paginator->total(), $paginator->perPage(), $paginator->currentPage(), $paginator->getOptions()) extends LengthAwarePaginator implements PaginationInterface {};
    }
}
