<?php

declare(strict_types=1);

namespace PterodactylMcp\Protocol;

use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;
use Pterodactyl\Models\User;
use PterodactylMcp\Tools\Registry;
use PterodactylMcp\Tools\Tool;
use PterodactylMcp\Tools\ToolError;
use Throwable;

/**
 * Answers MCP requests (JSON-RPC 2.0) for one signed-in user.
 */
final readonly class McpServer
{
    public const array VERSIONS = ['2025-11-25', '2025-06-18', '2025-03-26'];

    public function __construct(private Registry $tools) {}

    /**
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>|null Null for notifications.
     */
    public function handle(array $message, User $user): ?array
    {
        if (! JsonRpc::isValid($message)) {
            return JsonRpc::error($message['id'] ?? null, JsonRpc::INVALID_REQUEST, 'Invalid JSON-RPC request.');
        }

        if (JsonRpc::isNotification($message)) {
            return null;
        }

        $id = $message['id'];
        $params = $message['params'] ?? [];

        return match ($message['method']) {
            'initialize' => JsonRpc::result($id, $this->initialize($params)),
            'ping' => JsonRpc::result($id, (object) []),
            'tools/list' => JsonRpc::result($id, ['tools' => array_values(array_map($this->describe(...), $this->tools->all()))]),
            'tools/call' => $this->call($id, $params, $user),
            default => JsonRpc::error($id, JsonRpc::METHOD_NOT_FOUND, "Unknown method \"{$message['method']}\"."),
        };
    }

    /** @return array<string, mixed> */
    private static function content(string $text, bool $error): array
    {
        return ['content' => [['type' => 'text', 'text' => $text]], 'isError' => $error];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function initialize(array $params): array
    {
        $requested = (string) ($params['protocolVersion'] ?? '');

        return [
            'protocolVersion' => in_array($requested, self::VERSIONS, true) ? $requested : self::VERSIONS[0],
            'capabilities' => ['tools' => ['listChanged' => false]],
            'serverInfo' => ['name' => 'pterodactyl', 'title' => 'Pterodactyl', 'version' => '1.0.0'],
            'instructions' => 'Manage the game servers of the signed-in Pterodactyl user. Call list_servers first to get server identifiers.',
        ];
    }

    /** @return array<string, mixed> */
    private function describe(Tool $tool): array
    {
        return [
            'name' => $tool->name(),
            'description' => $tool->description(),
            'inputSchema' => $tool->schema(),
            'annotations' => ['readOnlyHint' => ! $tool->writes() && ! in_array($tool->name(), ['send_command', 'power'], true)],
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function call(int|string $id, array $params, User $user): array
    {
        $tool = $this->tools->all()[(string) ($params['name'] ?? '')] ?? null;

        if ($tool === null) {
            return JsonRpc::error($id, JsonRpc::INVALID_PARAMS, 'Unknown tool "'.($params['name'] ?? '').'".');
        }

        try {
            $data = $tool->call($user, is_array($params['arguments'] ?? null) ? $params['arguments'] : []);
        } catch (ToolError $exception) {
            return JsonRpc::result($id, self::content($exception->getMessage(), true));
        } catch (DaemonConnectionException) {
            return JsonRpc::result($id, self::content('The machine running this server could not be reached or refused the request.', true));
        } catch (Throwable $exception) {
            report($exception);

            return JsonRpc::result($id, self::content('The tool failed: '.$exception->getMessage(), true));
        }

        return JsonRpc::result($id, ['structuredContent' => (object) $data] + self::content(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), false));
    }
}
