import app from 'flarum/app';
import SettingsModal from 'flarum/components/SettingsModal';

export default class Auth0SettingsModal extends SettingsModal {
  className() {
    return 'Auth0SettingsModal Modal--small';
  }

  title() {
    return app.translator.trans('helmgast-auth0.admin.settings.title');
  }

  form() {
    return [
      <div className="Form-group">
        <label>{app.translator.trans('helmgast-auth0.admin.settings.account_label')}</label>
        <input className="FormControl" bidi={this.setting('helmgast-auth0.account')}/>
      </div>,

      <div className="Form-group">
        <label>{app.translator.trans('helmgast-auth0.admin.settings.client_id_label')}</label>
        <input className="FormControl" bidi={this.setting('helmgast-auth0.client_id')}/>
      </div>,

      <div className="Form-group">
        <label>{app.translator.trans('helmgast-auth0.admin.settings.client_secret_label')}</label>
        <input className="FormControl" bidi={this.setting('helmgast-auth0.client_secret')}/>
      </div>
    ];
  }
}
