<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;

final class ListFiles extends ServerTool
{
    public function name(): string
    {
        return 'list_files';
    }

    public function description(): string
    {
        return 'List the files and folders in a server directory.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['server' => self::serverArgument(), 'directory' => ['type' => 'string', 'default' => '/']],
            'required' => ['server'],
        ];
    }

    public function call(User $user, array $arguments): array
    {
        $server = $this->server($user, $arguments, 'file.read');

        return ['entries' => $this->panel->files($server, (string) ($arguments['directory'] ?? '/'))];
    }
}
