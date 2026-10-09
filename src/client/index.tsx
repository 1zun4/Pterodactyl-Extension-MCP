import './styles.css';
import { definePterodactylExtension } from '@pterodactyl/sdk';
import { setConfig } from './config';

export default definePterodactylExtension({
    setup({ config, screens }) {
        setConfig(config);
        screens.register('mcp', () => import('./screens/McpScreen'));
    },
});
