<?php

namespace AppBundle\EventListener;

use AppBundle\Services\JwtRefreshManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshTokenListener
{
    /** @var JwtRefreshManager */
    private $jwtRefreshManager;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(JwtRefreshManager $jwtRefreshManager, RequestStack $requestStack)
    {
        $this->jwtRefreshManager = $jwtRefreshManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        $refreshTokenKey = $this->requestStack->getCurrentRequest()->request->get('refresh_token_key', null);

        if (!$user instanceof UserInterface || !$refreshTokenKey) {
            return;
        }

        $data['refresh_token'] = $this->jwtRefreshManager->create($data['token']);

        $event->setData($data);
    }
}
