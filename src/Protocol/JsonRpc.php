<?php

declare(strict_types=1);

namespace PterodactylMcp\Protocol;

final class JsonRpc
{
    public const int PARSE_ERROR = -32700;

    public const int INVALID_REQUEST = -32600;

    public const int METHOD_NOT_FOUND = -32601;

    public const int INVALID_PARAMS = -32602;

    public const int INTERNAL_ERROR = -32603;

    /** @return array<string, mixed> */
    public static function result(int|string|null $id, mixed $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    /** @return array<string, mixed> */
    public static function error(int|string|null $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }

    /** @param array<string, mixed> $message */
    public static function isValid(array $message): bool
    {
        return ($message['jsonrpc'] ?? null) === '2.0'
            && is_string($message['method'] ?? null)
            && (! array_key_exists('id', $message) || is_int($message['id']) || is_string($message['id']))
            && (! array_key_exists('params', $message) || is_array($message['params']));
    }

    /** Notifications have no id and never get a response. */
    public static function isNotification(array $message): bool
    {
        return ! array_key_exists('id', $message);
    }
}
