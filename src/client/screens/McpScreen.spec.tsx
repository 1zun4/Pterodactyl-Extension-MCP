import { afterEach, expect, test } from 'vitest';
import { cleanup, render, screen } from '@testing-library/react';
import { createExtensionTestHost } from '@pterodactyl/sdk/testing';
import { setConfig } from '../config';
import McpScreen from './McpScreen';

let host: ReturnType<typeof createExtensionTestHost> | undefined;

afterEach(() => {
    cleanup();
    host?.dispose();
    host = undefined;
});

test('shows the endpoint and only the allowed tools', () => {
    setConfig({ allow_write_tools: false });
    host = createExtensionTestHost({ extensionId: 'mcp', prefix: 'mcp' });
    render(<McpScreen />, { wrapper: host.Wrapper });

    expect(screen.getAllByText(/\/api\/client\/extensions\/mcp/).length).toBeGreaterThan(0);
    expect(screen.getByText('send_command')).toBeTruthy();
    expect(screen.queryByText('write_file')).toBeNull();
});

test('lists write tools once an administrator allows them', () => {
    setConfig({ allow_write_tools: true });
    host = createExtensionTestHost({ extensionId: 'mcp', prefix: 'mcp' });
    render(<McpScreen />, { wrapper: host.Wrapper });

    expect(screen.getByText('write_file')).toBeTruthy();
    expect(screen.getByText('create_backup')).toBeTruthy();
});
