<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;

final class SendCommand extends ServerTool
{
    public function name(): string
    {
        return 'send_command';
    }

    public function description(): string
    {
        return 'Run a console command on a running server. Read the console afterwards to see the result.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['server' => self::serverArgument(), 'command' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000]],
            'required' => ['server', 'command'],
        ];
    }

    public function call(User $user, array $arguments): array
    {
        $server = $this->server($user, $arguments, 'control.console');
        $command = mb_trim(str_replace(["\r", "\n"], ' ', (string) ($arguments['command'] ?? '')));

        if ($command === '' || mb_strlen($command) > 1000) {
            throw new ToolError('The command must be between 1 and 1000 characters.');
        }

        if ($this->panel->state($server)->state !== 'running') {
            throw new ToolError("{$server->name} is not running. Start it first.");
        }

        $this->panel->command($user, $server, $command);

        return ['sent' => $command];
    }
}
