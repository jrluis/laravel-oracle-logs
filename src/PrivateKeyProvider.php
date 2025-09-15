<?php

namespace Jrluis\LaravelOracleLogs;

use Hitrov\OCI\KeyProvider\KeyProviderInterface;

class PrivateKeyProvider implements KeyProviderInterface
{
    private string $privateKey;
    private string $tenancyId;
    private string $userId;
    private string $fingerprint;

    public function __construct(string $privateKey, string $tenancyId, string $userId, string $fingerprint)
    {
        $this->privateKey = $privateKey;
        $this->tenancyId = $tenancyId;
        $this->userId = $userId;
        $this->fingerprint = $fingerprint;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function getKeyId(): string
    {
        return implode('/', [
            $this->tenancyId,
            $this->userId,
            $this->fingerprint,
        ]);
    }
}
