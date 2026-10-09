<?php

declare(strict_types=1);

namespace PterodactylMcp\Panel;

use Pterodactyl\Contracts\Backups\InitiatesBackups;
use Pterodactyl\Contracts\Files\ListsDirectories;
use Pterodactyl\Contracts\Files\ReadsFileContents;
use Pterodactyl\Contracts\Files\WritesFileContents;
use Pterodactyl\Contracts\Servers\ReadsServerLogs;
use Pterodactyl\Contracts\Servers\ReadsServerState;
use Pterodactyl\Contracts\Servers\SendsServerCommands;
use Pterodactyl\Contracts\Servers\SendsServerPower;
use Pterodactyl\Data\ServerState;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Backup;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\User;

/**
 * The only place this extension touches Panel internals. Every call is logged like the matching Client API request.
 */
final readonly class Panel
{
    public function __construct(
        private ReadsServerState $state,
        private ReadsServerLogs $logs,
        private SendsServerCommands $commands,
        private SendsServerPower $power,
        private ListsDirectories $lister,
        private ReadsFileContents $reader,
        private WritesFileContents $writer,
        private InitiatesBackups $backups,
    ) {}

    public function state(Server $server): ServerState
    {
        return $this->state->read($server);
    }

    /** @return list<string> */
    public function console(Server $server, int $lines): array
    {
        return $this->logs->read($server, $lines);
    }

    public function command(User $user, Server $server, string $command): void
    {
        $this->commands->send($server, $command);
        $this->log($user, $server, 'server:console.command', ['command' => $command]);
    }

    public function power(User $user, Server $server, string $signal): void
    {
        $this->power->send($server, $signal);
        $this->log($user, $server, "server:power.{$signal}");
    }

    /** @return list<array<string, mixed>> */
    public function files(Server $server, string $directory): array
    {
        return array_map(static fn (array $entry): array => [
            'name' => $entry['name'] ?? '',
            'directory' => ! ($entry['file'] ?? false),
            'size' => $entry['size'] ?? 0,
            'modified' => $entry['modified'] ?? null,
        ], $this->lister->list($server, $directory));
    }

    public function read(User $user, Server $server, string $path, int $maxBytes): string
    {
        $contents = $this->reader->read($server, $path, $maxBytes);
        $this->log($user, $server, 'server:file.read', ['file' => $path]);

        return $contents;
    }

    public function write(User $user, Server $server, string $path, string $contents): void
    {
        $this->writer->write($server, $path, $contents);
        $this->log($user, $server, 'server:file.write', ['file' => $path]);
    }

    public function backup(User $user, Server $server, ?string $name): Backup
    {
        $backup = $this->backups->initiate($server, $name);
        $this->log($user, $server, 'server:backup.start', ['name' => $backup->name, 'locked' => false]);

        return $backup;
    }

    /** @param array<string, mixed> $properties */
    private function log(User $user, Server $server, string $event, array $properties = []): void
    {
        Activity::event($event)->actor($user)->subject($server)->property($properties + ['via' => 'mcp'])->log();
    }
}
