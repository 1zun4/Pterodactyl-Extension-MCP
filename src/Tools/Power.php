<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;

final class Power extends ServerTool
{
    private const array PERMISSIONS = ['start' => 'control.start', 'stop' => 'control.stop', 'restart' => 'control.restart', 'kill' => 'control.stop'];

    public function name(): string
    {
        return 'power';
    }

    public function description(): string
    {
        return 'Start, stop, restart or kill a server. Kill stops it at once without saving.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['server' => self::serverArgument(), 'signal' => ['type' => 'string', 'enum' => array_keys(self::PERMISSIONS)]],
            'required' => ['server', 'signal'],
        ];
    }

    public function call(User $user, array $arguments): array
    {
        $signal = (string) ($arguments['signal'] ?? '');

        if (! isset(self::PERMISSIONS[$signal])) {
            throw new ToolError('The signal must be start, stop, restart or kill.');
        }

        $server = $this->server($user, $arguments, self::PERMISSIONS[$signal]);
        $this->panel->power($user, $server, $signal);

        return ['signal' => $signal, 'server' => $server->uuidShort];
    }
}
