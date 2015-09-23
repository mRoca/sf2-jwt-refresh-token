<?php

namespace AuthBundle\EventListener;

use AuthBundle\Services\JwtRefreshManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshTokenListener
{
    /** @var JwtRefreshManagerInterface */
    private $jwtRefreshManager;

    public function __construct(JwtRefreshManagerInterface $jwtRefreshManager)
    {
        $this->jwtRefreshManager = $jwtRefreshManager;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        $request = $event->getRequest();

        $refreshTokenKey = $request->get('refresh_token_key');
        if (!$user instanceof UserInterface || !$refreshTokenKey) {
            return;
        }

        $data['refresh_token'] = $this->jwtRefreshManager->create($data['token']);

        $event->setData($data);
    }
}
