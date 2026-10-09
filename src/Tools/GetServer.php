<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;

final class GetServer extends ServerTool
{
    public function name(): string
    {
        return 'get_server';
    }

    public function description(): string
    {
        return 'Show a server\'s state, resource usage, limits and address.';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['server' => self::serverArgument()], 'required' => ['server']];
    }

    public function call(User $user, array $arguments): array
    {
        $server = $this->server($user, $arguments);
        $state = $this->panel->state($server);
        $allocation = $server->allocation;

        return [
            'identifier' => $server->uuidShort,
            'name' => $server->name,
            'game' => $server->egg->name,
            'state' => $state->state,
            'address' => ($allocation->ip_alias ?: $allocation->ip).':'.$allocation->port,
            'usage' => [
                'cpu_percent' => round((float) $state->cpuAbsolute, 1),
                'memory_mib' => intdiv($state->memoryBytes, 1024 * 1024),
                'disk_mib' => intdiv($state->diskBytes, 1024 * 1024),
                'uptime_seconds' => intdiv($state->uptime, 1000),
            ],
            'limits' => [
                'cpu_percent' => $server->cpu,
                'memory_mib' => $server->memory,
                'disk_mib' => $server->disk,
            ],
        ];
    }
}
