import { extend, override } from 'flarum/extend';
import app from 'flarum/app';
import LogInButtons from 'flarum/components/LogInButtons';
import LogInButton from 'flarum/components/LogInButton';
import SignUpModal from 'flarum/components/SignUpModal';
import LogInModal from 'flarum/components/LogInModal';
import HeaderSecondary from 'flarum/components/HeaderSecondary';
import SettingsPage from 'flarum/components/SettingsPage';
import Session from 'flarum/Session';

app.initializers.add('helmgast-auth0', () => {
  extend(LogInButton.prototype, 'items', function(items) {
    items.add('auth0',
      <LogInButton
        className="Button Button--primary Button--block LogInButton--auth0"
        icon="fab fa-sign-in-alt"
        path="/auth/auth0">
        {app.translator.trans('helmgast-auth0.forum.log_in.with_auth0_button')}
      </LogInButton>
    );
  });
  
  extend(LogInButtons.prototype, 'items', function(items) {
    items.add('auth0',
      <LogInButton
        className="Button Button--primary Button--block LogInButton--auth0"
        icon="fab fa-github"
        path="/auth/auth0">
        {app.translator.trans('helmgast-auth0.forum.log_in.with_auth0_button')}
      </LogInButton>
    );
  });
  extend(LogInModal.prototype, 'fields', function (items) {
    if (!(window.location.hash ==='#admin')) {
      items.remove('identification');
      items.remove('password');
      items.remove('remember');
      items.remove('submit');
    }
    return items;
  });
  override(LogInModal.prototype, 'footer', function () {
    // Hide hint for forgot password etc
    return [
    ];
  });

  extend(HeaderSecondary.prototype, 'items', function(items) {
    items.remove('signUp');
    return items;
  });

  extend(SettingsPage.prototype, 'accountItems', function(items) {
    items.remove('changePassword');
    items.remove('changeEmail');
    // Add link to Helmgast account page later
    return items;
  });

  override(SignUpModal.prototype, 'footer', function () {
    // Hide hint for login
    return [
    ];
  });

  override(Session.prototype, 'logout', function () {
    // this will be bound to Session when run
    // setting('helmgast-auth0.account')
    let logout_url = app.forum.attribute('baseUrl') + '/logout?token=' + this.csrfToken;
    const account = 'helmgast.eu'; //app.forum.attribute('helmgast-auth0.account');
    if (account) {
      logout_url = `https://${account}.auth0.com/v2/logout?returnTo=${encodeURIComponent(logout_url)}`;
    }
    window.location = logout_url;
  });

});
//