<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;

interface Tool
{
    public function name(): string;

    public function description(): string;

    /** @return array<string, mixed> The JSON schema of the arguments. */
    public function schema(): array;

    /** Whether the tool changes anything; those are off unless an administrator allows them. */
    public function writes(): bool;

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<array-key, mixed> Data returned to the model as JSON.
     */
    public function call(User $user, array $arguments): array;
}
