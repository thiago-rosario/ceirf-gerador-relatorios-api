<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use src\Modules\Identity\Model\User;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Infra\Mapper\ReportPersistenceMapper;
use src\Modules\Report\Model\Report;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'created_by' => User::factory(),
            'revision_number' => 0,
            'status' => 'DRAFT',
            'created_at' => now(),
            'updated_at' => now(),
            'report_series_id' => fn (array $attributes): int => DB::table('report_series')->insertGetId([
                'uuid' => $attributes['uuid'],
                'created_by' => $attributes['created_by'],
                'created_at' => $attributes['created_at'],
                'updated_at' => $attributes['updated_at'],
            ]),
            'payload' => fn (array $attributes): array => (new ReportPersistenceMapper)->toPayload(new ReportEntity(
                id: $attributes['uuid'],
                createdBy: User::query()->whereKey($attributes['created_by'])->firstOrFail()->uuid,
                createdAt: $attributes['created_at'],
                updatedAt: $attributes['updated_at'],
            )),
        ];
    }
}
