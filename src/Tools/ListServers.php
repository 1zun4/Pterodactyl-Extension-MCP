<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\Server;
use Pterodactyl\Models\User;

final class ListServers implements Tool
{
    public function name(): string
    {
        return 'list_servers';
    }

    public function description(): string
    {
        return 'List the game servers you own or were added to, with their identifiers.';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => (object) []];
    }

    public function writes(): bool
    {
        return false;
    }

    public function call(User $user, array $arguments): array
    {
        return ['servers' => $user->accessibleServers()->with(['egg', 'node'])->orderBy('servers.name')->get()->map(static fn (Server $server): array => [
            'identifier' => $server->uuidShort,
            'name' => $server->name,
            'description' => $server->description,
            'game' => $server->egg->name,
            'node' => $server->node->name,
            'owner' => $server->owner_id === $user->id,
            'suspended' => $server->isSuspended(),
        ])->values()->all()];
    }
}
