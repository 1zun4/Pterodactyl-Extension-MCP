<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;

final class WriteFile extends ServerTool
{
    public function name(): string
    {
        return 'write_file';
    }

    public function description(): string
    {
        return 'Create or overwrite a text file on a server.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['server' => self::serverArgument(), 'path' => ['type' => 'string'], 'content' => ['type' => 'string']],
            'required' => ['server', 'path', 'content'],
        ];
    }

    public function writes(): bool
    {
        return true;
    }

    public function call(User $user, array $arguments): array
    {
        $server = $this->server($user, $arguments, 'file.update');

        if (! $user->can('file.create', $server)) {
            throw new ToolError("You do not have the file.create permission on {$server->name}.");
        }

        $path = (string) ($arguments['path'] ?? '');
        $content = (string) ($arguments['content'] ?? '');
        $this->panel->write($user, $server, $path, $content);

        return ['path' => $path, 'bytes' => mb_strlen($content, '8bit')];
    }
}
