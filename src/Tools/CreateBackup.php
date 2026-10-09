<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Models\User;
use Throwable;

final class CreateBackup extends ServerTool
{
    public function name(): string
    {
        return 'create_backup';
    }

    public function description(): string
    {
        return 'Start a backup of a server. It finishes in the background.';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['server' => self::serverArgument(), 'name' => ['type' => 'string', 'maxLength' => 191]], 'required' => ['server']];
    }

    public function writes(): bool
    {
        return true;
    }

    public function call(User $user, array $arguments): array
    {
        $server = $this->server($user, $arguments, 'backup.create');

        try {
            $backup = $this->panel->backup($user, $server, isset($arguments['name']) ? (string) $arguments['name'] : null);
        } catch (Throwable $exception) {
            throw new ToolError($exception->getMessage());
        }

        return ['backup' => $backup->uuid, 'name' => $backup->name];
    }
}
