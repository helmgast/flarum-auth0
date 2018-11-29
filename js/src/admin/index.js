import app from 'flarum/app';

import Auth0SettingsModal from './components/Auth0SettingsModal';

app.initializers.add('flarum-auth-auth0', () => {
  app.extensionSettings['flarum-auth-auth0'] = () => app.modal.show(new Auth0SettingsModal());
});
