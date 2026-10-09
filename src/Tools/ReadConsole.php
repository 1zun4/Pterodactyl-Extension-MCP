<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;

final class ReadConsole extends ServerTool
{
    public function name(): string
    {
        return 'read_console';
    }

    public function description(): string
    {
        return 'Read the latest console output of a server, oldest line first.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['server' => self::serverArgument(), 'lines' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 50]],
            'required' => ['server'],
        ];
    }

    public function call(User $user, array $arguments): array
    {
        $server = $this->server($user, $arguments, 'websocket.connect');
        $lines = max(1, min(100, (int) ($arguments['lines'] ?? 50)));

        return ['lines' => array_map(static fn (string $line): string => (string) preg_replace('/\e\[[0-9;?]*[A-Za-z]/', '', $line), $this->panel->console($server, $lines))];
    }
}
