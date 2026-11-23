import './bootstrap';

import Alpine from 'alpinejs';
import { iniciarAnimaciones, registrarComponentes } from './dinamico';

window.Alpine = Alpine;

registrarComponentes(Alpine);
Alpine.start();

iniciarAnimaciones();
