<?php

declare(strict_types=1);

use src\Modules\Identity\Infra\Repositories\Queries\PaginateUserEloquentRepository;

test('rejects invalid pagination limits before accessing persistence', function (int $page, int $perPage): void {
    $repository = new PaginateUserEloquentRepository;

    expect(fn () => $repository->paginate($page, $perPage))
        ->toThrow(InvalidArgumentException::class, 'A página e a quantidade de itens por página devem ser maiores que zero.');
})->with([
    'zero page' => [0, 10],
    'negative page' => [-1, 10],
    'zero page size' => [1, 0],
    'negative page size' => [1, -1],
]);
