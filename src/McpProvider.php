<?php

declare(strict_types=1);

namespace PterodactylMcp;

use Pterodactyl\Extensions\ExtensionProvider;
use Pterodactyl\Services\Extensions\ExtensionSettingDefinition;
use Pterodactyl\Services\Extensions\ExtensionSettingsDefinition;

class McpProvider extends ExtensionProvider
{
    public function boot(): void
    {
        $this->registerApiRoutes();

        $this->registerSettings(new ExtensionSettingsDefinition($this->settings(), [
            ExtensionSettingDefinition::make('enabled', 'enabled', true, ['boolean'])
                ->label('Enabled')
                ->help('Turn the MCP endpoint off without uninstalling the extension.')
                ->field('toggle'),
            ExtensionSettingDefinition::make('allow_write_tools', 'allow_write_tools', false, ['boolean'])
                ->label('Allow writing files and backups')
                ->help('Adds the write_file and create_backup tools. Console and power tools are always available to users with those permissions.')
                ->field('toggle')
                ->frontend()
                ->frontendType('boolean'),
            ExtensionSettingDefinition::make('max_read_kb', 'max_read_kb', 256, ['required', 'integer', 'min:1', 'max:4096'])
                ->label('Largest readable file (KB)')
                ->field('number'),
        ]));
    }
}
