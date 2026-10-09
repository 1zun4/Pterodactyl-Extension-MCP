<?php

declare(strict_types=1);

namespace PterodactylMcp\Tools;

use Illuminate\Contracts\Container\Container;
use Pterodactyl\Services\Extensions\ExtensionSettingsRegistry;

final readonly class Registry
{
    private const array TOOLS = [ListServers::class, GetServer::class, ReadConsole::class, SendCommand::class, Power::class, ListFiles::class, ReadFile::class, WriteFile::class, CreateBackup::class];

    public function __construct(private Container $container, private ExtensionSettingsRegistry $settings) {}

    /** @return array<string, Tool> */
    public function all(): array
    {
        $writes = (bool) ($this->settings->get('mcp')?->get('allow_write_tools') ?? false);
        $tools = [];

        foreach (self::TOOLS as $class) {
            $tool = $this->container->make($class);

            if ($writes || ! $tool->writes()) {
                $tools[$tool->name()] = $tool;
            }
        }

        return $tools;
    }
}
