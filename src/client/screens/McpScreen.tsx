import { CopyOnClick, PageContentBlock, PanelLink, TitledGreyBox } from '@pterodactyl/sdk';
import { writeToolsAllowed } from '../config';

const TOOLS = [
    { name: 'list_servers', description: 'Your servers and their identifiers.' },
    { name: 'get_server', description: 'State, usage, limits and address.' },
    { name: 'read_console', description: 'The latest console output.', permission: 'Console access' },
    { name: 'send_command', description: 'Run a console command.', permission: 'Send console commands' },
    { name: 'power', description: 'Start, stop, restart or kill.', permission: 'Power actions' },
    { name: 'list_files', description: 'Browse folders.', permission: 'List files' },
    { name: 'read_file', description: 'Read config files and logs.', permission: 'Read file contents' },
    { name: 'write_file', description: 'Create or change files.', permission: 'Update files', writes: true },
    { name: 'create_backup', description: 'Start a backup.', permission: 'Create backups', writes: true },
];

function Code({ children }: { children: string }) {
    return (
        <CopyOnClick text={children}>
            <pre className='mcp:rounded-sm mcp:bg-terminal mcp:px-4 mcp:py-3 mcp:font-mono mcp:text-sm mcp:break-all mcp:whitespace-pre-wrap mcp:text-terminal-foreground'>{children}</pre>
        </CopyOnClick>
    );
}

export default function McpScreen() {
    const url = `${window.location.origin}/api/client/extensions/mcp`;
    const json = JSON.stringify({ mcpServers: { pterodactyl: { type: 'http', url, headers: { Authorization: 'Bearer ptlc_YOUR_KEY' } } } }, null, 2);

    return (
        <PageContentBlock title='MCP'>
            <div className='mcp:grid mcp:gap-6'>
                <TitledGreyBox title='Connect an AI assistant'>
                    <ol className='mcp:grid mcp:list-decimal mcp:gap-3 mcp:pl-5 mcp:text-sm'>
                        <li>
                            Create an API key under{' '}
                            <PanelLink destination={{ to: '/account/api' }} className='mcp:text-primary mcp:underline'>
                                API Credentials
                            </PanelLink>
                            . Limit it to your computer's IP address if you can.
                        </li>
                        <li>Add the server below to your assistant and replace the key.</li>
                        <li>Ask it something like "Restart my survival server and show me the console".</li>
                    </ol>
                    <p className='mcp:mt-4 mcp:mb-1 mcp:text-xs mcp:uppercase mcp:text-muted-foreground'>Endpoint</p>
                    <Code>{url}</Code>
                </TitledGreyBox>
                <TitledGreyBox title='Claude Code'>
                    <Code>{`claude mcp add --transport http pterodactyl ${url} --header "Authorization: Bearer ptlc_YOUR_KEY"`}</Code>
                </TitledGreyBox>
                <TitledGreyBox title='Cursor, VS Code, Claude Desktop and others'>
                    <p className='mcp:mb-2 mcp:text-sm mcp:text-muted-foreground'>Add this to the MCP settings of your app. Apps that only connect with an OAuth login are not supported.</p>
                    <Code>{json}</Code>
                </TitledGreyBox>
                <TitledGreyBox title='Tools'>
                    <p className='mcp:mb-3 mcp:text-sm mcp:text-muted-foreground'>The assistant acts as you, so it can only use what your server permissions allow.</p>
                    <ul className='mcp:grid mcp:gap-2 mcp:sm:grid-cols-2'>
                        {TOOLS.filter((tool) => !tool.writes || writeToolsAllowed()).map((tool) => (
                            <li key={tool.name} className='mcp:rounded-sm mcp:border mcp:border-border mcp:p-3'>
                                <p className='mcp:font-mono mcp:text-sm mcp:text-foreground'>{tool.name}</p>
                                <p className='mcp:text-xs mcp:text-muted-foreground'>
                                    {tool.description}
                                    {tool.permission && ` Needs: ${tool.permission}.`}
                                </p>
                            </li>
                        ))}
                    </ul>
                </TitledGreyBox>
            </div>
        </PageContentBlock>
    );
}
