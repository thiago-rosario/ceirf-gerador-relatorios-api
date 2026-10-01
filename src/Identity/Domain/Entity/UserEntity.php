<?php

declare(strict_types=1);

namespace src\Identity\Domain\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use src\Identity\Domain\Enum\UserRoleEnum;
use src\Identity\Domain\Resolver\UuidResolver;
use src\Identity\Domain\Trait\MethodsMagicsTraits;
use src\Identity\Domain\Validation\UserValidation;
use src\Identity\Domain\ValueObject\EmailValueObject;

class UserEntity
{
    use MethodsMagicsTraits;

    private const DEFAULT_PASSWORD = 'sspba123';

    private UuidResolver $id;

    private EmailValueObject $email;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * Normaliza os dados recebidos e valida a entidade antes de concluir sua criação.
     * Quando o identificador não é informado, gera um UUID; quando as datas são
     * omitidas, usa o instante atual para criação e o mesmo valor para alteração.
     * Datas fornecidas como objetos mutáveis são convertidas em cópias imutáveis.
     */
    public function __construct(
        UuidResolver|string|null $id = null,
        private string $name = '',
        EmailValueObject|string $email = '',
        private string $password = '',
        private bool $isActive = true,
        private UserRoleEnum $role = UserRoleEnum::OPERATOR,
        private bool $mustChangePassword = false,
        DateTimeInterface|string|null $createdAt = null,
        DateTimeInterface|string|null $updatedAt = null,
    ) {
        $this->id = $this->resolveUuid($id);
        $this->name = trim($name);
        $this->email = $this->resolveEmail($email);
        $this->createdAt = $this->resolveDateTime($createdAt);
        $this->updatedAt = $this->resolveDateTime($updatedAt, $this->createdAt);
        $this->validate();
    }

    public function id(): UuidResolver
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): EmailValueObject
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function role(): UserRoleEnum
    {
        return $this->role;
    }

    public function mustChangePassword(): bool
    {
        return $this->mustChangePassword;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function activate(): void
    {
        if ($this->isActive) {
            return;
        }

        $this->isActive = true;
        $this->touch();
    }

    public function deactivate(): void
    {
        if (! $this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->touch();
    }

    /**
     * Remove espaços das extremidades e valida o nome antes de alterar a entidade.
     * Um nome inválido mantém o nome anterior e a data da última alteração.
     */
    public function changeName(string $name): void
    {
        $name = trim($name);

        UserValidation::validateName($name);

        $this->name = $name;
        $this->touch();
    }

    /**
     * Delega a normalização e a validação ao objeto de valor antes de substituir o e-mail.
     * Um endereço inválido mantém o e-mail anterior e a data da última alteração.
     */
    public function changeEmail(EmailValueObject|string $email): void
    {
        $newEmail = $this->resolveEmail($email);

        $this->email = $newEmail;
        $this->touch();
    }

    public function changeRole(UserRoleEnum $role): void
    {
        $this->role = $role;
        $this->touch();
    }

    /**
     * Redefine a senha para o valor padrão e exige uma troca posterior pelo usuário.
     */
    public function resetPassword(): void
    {
        $this->password = self::DEFAULT_PASSWORD;
        $this->mustChangePassword = true;
        $this->touch();
    }

    /**
     * Valida a nova senha antes de atribuí-la e encerra a exigência de troca.
     * O valor recebido é preservado; a entidade não gera nem verifica hashes.
     */
    public function changePassword(string $password): void
    {
        UserValidation::validatePassword($password);

        $this->password = $password;
        $this->mustChangePassword = false;
        $this->touch();
    }

    /**
     * Verifica as regras de nome e senha por meio de UserValidation.
     * UUID e e-mail já são validados pelos respectivos objetos na construção.
     */
    public function validate(): void
    {
        UserValidation::validate($this);
    }
}
