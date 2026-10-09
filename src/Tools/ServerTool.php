<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\Server;
use Pterodactyl\Models\User;
use PterodactylMcp\Panel\Panel;

abstract class ServerTool implements Tool
{
    public function __construct(protected readonly Panel $panel) {}

    public function writes(): bool
    {
        return false;
    }

    /** @return array<string, mixed> */
    protected static function serverArgument(): array
    {
        return ['type' => 'string', 'description' => 'The server identifier from list_servers, such as "3b01dadb".'];
    }

    /** @param array<string, mixed> $arguments */
    protected function server(User $user, array $arguments, ?string $permission = null): Server
    {
        $identifier = (string) ($arguments['server'] ?? '');
        $server = $identifier === '' ? null : Server::query()
            ->where(static fn ($query) => $query->where('uuidShort', $identifier)->orWhere('uuid', $identifier))
            ->first();

        if ($server === null || ! self::isMember($user, $server)) {
            throw new ToolError("There is no server \"{$identifier}\" you can access. Use list_servers to see yours.");
        }

        if ($permission !== null && ! $user->can($permission, $server)) {
            throw new ToolError("You do not have the {$permission} permission on {$server->name}.");
        }

        return $server;
    }

    private static function isMember(User $user, Server $server): bool
    {
        return $user->root_admin || $server->owner_id === $user->id || $server->subusers()->where('user_id', $user->id)->exists();
    }
}
