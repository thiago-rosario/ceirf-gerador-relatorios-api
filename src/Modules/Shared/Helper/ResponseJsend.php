<?php

declare(strict_types=1);

namespace src\Modules\Shared\Helper;

use Illuminate\Http\JsonResponse;
use JsonSerializable;
use src\Modules\Identity\Presentation\Exception\InvalidJsendStatusException;
use src\Modules\Identity\Presentation\Exception\JsendErrorMessageRequiredException;

final readonly class ResponseJsend implements JsonSerializable
{
    public const string STATUS_SUCCESS = 'success';

    public const string STATUS_FAIL = 'fail';

    public const string STATUS_ERROR = 'error';

    private const array VALID_STATUSES = [
        self::STATUS_SUCCESS,
        self::STATUS_FAIL,
        self::STATUS_ERROR,
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        private array $data = [],
        private string $status = self::STATUS_SUCCESS,
        private ?string $message = null,
        private ?int $code = null,
    ) {
        if (! in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidJsendStatusException($this->status);
        }

        if ($this->status === self::STATUS_ERROR && $this->message === null) {
            throw new JsendErrorMessageRequiredException;
        }
    }

    /**
     * @return array{status: string, data: array<string, mixed>, message?: string, code?: int}
     */
    public function toArray(): array
    {
        $response = [
            'status' => $this->status,
            'data' => $this->data,
        ];

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        if ($this->code !== null) {
            $response['code'] = $this->code;
        }

        return $response;
    }

    /**
     * @return array{status: string, data: array<string, mixed>, message?: string, code?: int}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  array<string, string|string[]>  $headers
     */
    public function toJsonResponse(int $httpStatus = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return response()->json($this->toArray(), $httpStatus, $headers, $options);
    }
}
