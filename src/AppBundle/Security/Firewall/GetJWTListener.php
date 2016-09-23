<?php

namespace AppBundle\Security\Firewall;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/*
 * From https://github.com/gfreeau/GfreeauGetJWTBundle
 */

class GetJWTListener implements ListenerInterface
{
    protected $providerKey;
    protected $logger;

    private $tokenStorage;
    private $authenticationManager;
    private $successHandler;
    private $failureHandler;

    /**
     * @param TokenStorageInterface                 $tokenStorage
     * @param AuthenticationManagerInterface        $authenticationManager
     * @param                                       $providerKey
     * @param AuthenticationSuccessHandlerInterface $successHandler
     * @param AuthenticationFailureHandlerInterface $failureHandler
     * @param array                                 $options
     * @param LoggerInterface                       $logger
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, $providerKey, AuthenticationSuccessHandlerInterface $successHandler, AuthenticationFailureHandlerInterface $failureHandler = null, array $options = [], LoggerInterface $logger = null)
    {
        if (empty($providerKey)) {
            throw new InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $username = trim($request->request->get('username', null, true));
        $password = $request->request->get('password', null, true);

        try {
            if (null === $username || null === $password) {
                throw new AuthenticationException('You must provide a username and a password.');
            }

            $token = $this->authenticationManager->authenticate(new UsernamePasswordToken($username, $password, $this->providerKey));
            $this->tokenStorage->setToken($token);
            $response = $this->onSuccess($event, $request, $token);
        } catch (AuthenticationException $e) {
            if (null === $this->failureHandler) {
                throw $e;
            }

            $response = $this->onFailure($event, $request, $e);
        }

        $event->setResponse($response);
    }

    protected function onSuccess(GetResponseEvent $event, Request $request, TokenInterface $token)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('User "%s" has retrieved a JWT', $token->getUsername()));
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Success Handler did not return a Response.');
        }

        return $response;
    }

    protected function onFailure(GetResponseEvent $event, Request $request, AuthenticationException $failed)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('JWT request failed: %s', $failed->getMessage()));
        }

        $response = $this->failureHandler->onAuthenticationFailure($request, $failed);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Failure Handler did not return a Response.');
        }

        return $response;
    }
}
