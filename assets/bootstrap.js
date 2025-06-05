import { startStimulusApp } from '@symfony/stimulus-bundle';



const app = startStimulusApp();
// assets/bootstrap.js
import { Application } from '@hotwired/stimulus';

// Démarrer l'application Stimulus
const application = Application.start();

// Enregistrer manuellement les contrôleurs
// import HelloController from './controllers/hello_controller';
// application.register('hello', HelloController);

export { application };