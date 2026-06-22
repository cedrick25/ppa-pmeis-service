<?php

declare(strict_types=1);

namespace App\Repository;

use App\Service\Cmis\CmisSource;

class CmisClientRepositoryRegistry
{
    public function __construct(
        private CmisF5T7Repository $f5t7Repository,
        private CmisF5T11Repository $f5t11Repository,
        private CmisF21T8ParolRepository $f21t8ParolRepository,
        private CmisF21T8PardonRepository $f21t8PardonRepository,
    ){}

    public function get(string $source): AbstractCmisClientTableRepository
    {
        return match ($source) {
            CmisSource::F5T7 => $this->f5t7Repository,
            CmisSource::F5T11 => $this->f5t11Repository,
            CmisSource::F21T8_PAROL => $this->f21t8ParolRepository,
            CmisSource::F21T8_PARDON => $this->f21t8PardonRepository,
            default => throw new \InvalidArgumentException(sprintf('Unsupported CMIS source "%s".', $source)),
        };
    }
}
