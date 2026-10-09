<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Pterodactyl\Exceptions\Http\Server\FileSizeTooLargeException;
use Pterodactyl\Models\User;
use Pterodactyl\Services\Extensions\ExtensionSettingsRegistry;
use PterodactylMcp\Panel\Panel;

final class ReadFile extends ServerTool
{
    public function __construct(Panel $panel, private readonly ExtensionSettingsRegistry $settings)
    {
        parent::__construct($panel);
    }

    public function name(): string
    {
        return 'read_file';
    }

    public function description(): string
    {
        return 'Read a text file from a server, such as a config file or a log.';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['server' => self::serverArgument(), 'path' => ['type' => 'string']], 'required' => ['server', 'path']];
    }

    public function call(User $user, array $arguments): array
    {
        $server = $this->server($user, $arguments, 'file.read-content');
        $maxBytes = max(1, (int) ($this->settings->get('mcp')?->get('max_read_kb') ?? 256)) * 1024;
        try {
            $contents = $this->panel->read($user, $server, (string) ($arguments['path'] ?? ''), $maxBytes);
        } catch (FileSizeTooLargeException) {
            throw new ToolError('The file is larger than '.intdiv($maxBytes, 1024).' KB.');
        }

        if (! mb_check_encoding($contents, 'UTF-8')) {
            throw new ToolError('This file is not a text file.');
        }

        return ['path' => $arguments['path'], 'contents' => $contents];
    }
}
