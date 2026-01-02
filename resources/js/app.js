import './bootstrap';
import Alpine from 'alpinejs';
import { chatComponent } from './chat-component.js';

window.Alpine = Alpine;

window.chatComponent = chatComponent;

Alpine.start();