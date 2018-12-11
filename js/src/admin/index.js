import { extend } from 'flarum/extend';
import app from 'flarum/app';
import Auth0SettingsModal from './components/Auth0SettingsModal';

app.initializers.add('helmgast-auth0', () => {
  app.extensionSettings['helmgast-auth0'] = () => app.modal.show(new Auth0SettingsModal());
});
