<?php


namespace Helmgast\Auth\Auth0;

use Exception;
use Flarum\Forum\Auth\Registration;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Settings\SettingsRepositoryInterface;
use Riskio\OAuth2\Client\Provider\Auth0;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Flarum\User\User;
use Flarum\User\LoginProvider;
use Intervention\Image\ImageManager;

class Auth0AuthController implements RequestHandlerInterface
{
    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param ResponseFactory $response
     */
    public function __construct(ResponseFactory $response, SettingsRepositoryInterface $settings)
    {
        $this->response = $response;
        $this->settings = $settings;
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(Request $request): ResponseInterface
    {
        $redirectUri = (string) $request->getAttribute('originalUri', $request->getUri())->withQuery('');

        $provider = new Auth0([
            'account'      => $this->settings->get('helmgast-auth0.account'),
            'clientId'     => $this->settings->get('helmgast-auth0.client_id'),
            'clientSecret' => $this->settings->get('helmgast-auth0.client_secret'),
            'redirectUri'  => $redirectUri
        ]);

        $session = $request->getAttribute('session');
        $queryParams = $request->getQueryParams();

        $code = array_get($queryParams, 'code');

        if (! $code) {
            $authUrl = $provider->getAuthorizationUrl(); #$provider->getAuthorizationUrl(['scope' => ['user:email']]);
            $session->put('oauth2state', $provider->getState());

            return new RedirectResponse($authUrl.'&display=popup');
        }

        $state = array_get($queryParams, 'state');

        if (! $state || $state !== $session->get('oauth2state')) {
            $session->remove('oauth2state');

            throw new Exception('Invalid state');
        }

        $token = $provider->getAccessToken('authorization_code', compact('code'));

        $user = $provider->getResourceOwner($token);
        $user_array = $user->toArray();
        $linked = array_get($user_array, 'app_metadata.linked');
        $email = $user->getEmail();
        if ($linked) {
            // Fetches the first item on the linked auth list, and splits first part as the email
            // This email is the preferred primary email, even if we actually logged in with a
            // different email
            $email = explode(":",array_get($user->toArray(),'app_metadata.linked')[0])[0];
        }

        return $this->response->make(
            'auth0', $email,
            function (Registration $registration) use ($user, $provider, $token) {
                $registration
                    ->provideTrustedEmail($email)
                    ->provideAvatar(array_get($user_array, 'picture'))
                    ->suggestUsername($user->getNickname())
                    ->setPayload($user_array);
            }
        );
    }
}
