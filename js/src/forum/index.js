import { extend } from 'flarum/extend';
import app from 'flarum/app';
import LogInButtons from 'flarum/components/LogInButtons';
import LogInButton from 'flarum/components/LogInButton';

app.initializers.add('flarum-auth-auth0', () => {
  extend(LogInButtons.prototype, 'items', function(items) {
    items.add('auth0',
      <LogInButton
        className="Button LogInButton--auth0"
        icon="fab fa-github"
        path="/auth/auth0">
        {app.translator.trans('flarum-auth-github.forum.log_in.with_github_button')}
      </LogInButton>
    );
  });
});
