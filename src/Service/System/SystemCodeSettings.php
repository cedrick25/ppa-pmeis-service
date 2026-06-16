<?php

namespace App\Service\System;

use App\Entity\UserAccount;
use App\Repository\SystemCodeSettingsRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SystemCodeSettings
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private SystemCodeSettingsRepository $repository,
    ){}

    /**
     * @return \App\Entity\SystemCodeSettings[]
     */
    public function listAll(): array
    {
        return $this->repository->findAll();
    }

    public function create(string $name, string $value): string
    {
        $user = $this->tokenStorage->getToken()->getUser();
        \assert($user instanceof UserAccount);

        return $this->repository->create($name, $value, $user->getUserAccountId());
    }

    public function update(string $id, string $name, string $value): string
    {
        $user = $this->tokenStorage->getToken()->getUser();
        \assert($user instanceof UserAccount);

        return $this->repository->update($id, $name, $value, $user->getUserAccountId());
    }

    public function delete(string $id): string
    {
        return $this->repository->delete($id);
    }

    public function getByName(string $name): string
    {
        $data = $this->repository->getByName($name);

        return $data->getValue();
    }

    public function getById(string $id): \App\Entity\SystemCodeSettings
    {
        return $this->repository->findOneBy([
            'systemCodeId' =>  $id
        ]);
    }
}