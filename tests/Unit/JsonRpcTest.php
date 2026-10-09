<?php

declare(strict_types=1);

namespace PterodactylMcp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PterodactylMcp\Protocol\JsonRpc;

final class JsonRpcTest extends TestCase
{
    public function test_validates_requests_and_notifications(): void
    {
        $this->assertTrue(JsonRpc::isValid(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list']));
        $this->assertTrue(JsonRpc::isNotification(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']));
        $this->assertFalse(JsonRpc::isValid(['jsonrpc' => '1.0', 'id' => 1, 'method' => 'ping']));
        $this->assertFalse(JsonRpc::isValid(['jsonrpc' => '2.0', 'id' => ['x'], 'method' => 'ping']));
        $this->assertFalse(JsonRpc::isValid(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping', 'params' => 'nope']));
    }

    public function test_builds_results_and_errors(): void
    {
        $this->assertSame(['jsonrpc' => '2.0', 'id' => 'a', 'result' => ['ok' => true]], JsonRpc::result('a', ['ok' => true]));
        $this->assertSame(['jsonrpc' => '2.0', 'id' => 7, 'error' => ['code' => -32601, 'message' => 'Nope.']], JsonRpc::error(7, JsonRpc::METHOD_NOT_FOUND, 'Nope.'));
    }
}
