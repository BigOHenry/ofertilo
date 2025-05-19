import { startStimulusApp } from '@symfony/stimulus-bridge';
import './controllers';

const app = startStimulusApp(require.context(
    './controllers',
    true,
    /\.(js|ts)$/
));