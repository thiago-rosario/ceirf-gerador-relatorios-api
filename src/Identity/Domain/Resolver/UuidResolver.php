<?php

declare(strict_types=1);

namespace src\Identity\Domain\Resolver;

use Ramsey\Uuid\Uuid as RamseyUuid;
use src\Identity\Domain\Exception\InvalidUserIdException;

/**
 * Encapsula um UUID válido usado como identificador do usuário.
 * Delega a validação e a geração à biblioteca Ramsey UUID e expõe o valor como string.
 */
class UuidResolver
{
    /**
     * @throws InvalidUserIdException Quando o valor informado não é um UUID válido.
     */
    public function __construct(
        protected string $value
    ) {
        $this->ensureIsValid($value);
    }

    /**
     * Gera um UUID aleatório de versão 4 e o devolve encapsulado e validado.
     */
    public static function random(): self
    {
        return new self(RamseyUuid::uuid4()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value();
    }

    /**
     * Sinaliza um UUID inválido com a exceção do domínio, incluindo o valor na mensagem.
     */
    private function ensureIsValid(string $id): void
    {
        if (! RamseyUuid::isValid($id)) {
            throw new InvalidUserIdException(self::class, $id);
        }
    }
}
