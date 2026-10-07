<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\ValueObject;

use src\Modules\Identity\Domain\Exception\InvalidEmailException;

/**
 * Representa um endereço de e-mail estruturalmente válido em sua forma normalizada.
 * Remove espaços das extremidades e converte o endereço para letras minúsculas.
 * A validação verifica o formato, sem consultar a existência do endereço ou do domínio.
 */
class EmailValueObject
{
    private string $value;

    /**
     * @throws InvalidEmailException Quando o endereço normalizado tem formato inválido.
     */
    public function __construct(string $value)
    {
        $normalizedEmail = strtolower(trim($value));

        if (filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmailException;
        }

        $this->value = $normalizedEmail;
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compara os valores normalizados, independentemente da identidade das instâncias.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
