<?php


namespace Helmgast\Auth\Auth0;

use Exception;
use Flarum\Forum\Auth\Registration;
use Flarum\Forum\Auth\ResponseFactory;
use Flarum\Settings\SettingsRepositoryInterface;
use Riskio\OAuth2\Client\Provider\Auth0;
#use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

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

/*         $provider = new Github([
            'clientId' => $this->settings->get('flarum-auth-github.client_id'),
            'clientSecret' => $this->settings->get('flarum-auth-github.client_secret'),
            'redirectUri' => $redirectUri
        ]); */

        $provider = new Auth0([
            'account'      => $this->settings->get('flarum-auth-auth0.account'),
            'clientId'     => $this->settings->get('flarum-auth-auth0.client_id'),
            'clientSecret' => $this->settings->get('flarum-auth-auth0.client_secret'),
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

        /** @var GithubResourceOwner $user */
        $user = $provider->getResourceOwner($token);

        return $this->response->make(
            'github', $user->getId(),
            function (Registration $registration) use ($user, $provider, $token) {
                $registration
                    ->provideTrustedEmail($user->getEmail() ?: $this->getEmailFromApi($provider, $token))
                    ->provideAvatar(array_get($user->toArray(), 'avatar_url'))
                    ->suggestUsername($user->getNickname())
                    ->setPayload($user->toArray());
            }
        );
    }

    private function getEmailFromApi(Github $provider, AccessToken $token)
    {
        $url = $provider->apiDomain.'/user/emails';

        $response = $provider->getResponse(
            $provider->getAuthenticatedRequest('GET', $url, $token)
        );

        $emails = json_decode($response->getBody(), true);

        foreach ($emails as $email) {
            if ($email['primary'] && $email['verified']) {
                return $email['email'];
            }
        }
    }
}
