<?php

namespace AuthBundle\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthController extends Controller
{
    public function getTokenAction()
    {
        // The security layer will intercept this request
        return new JsonResponse(null, 401);
    }

    public function refreshTokenAction(Request $request)
    {
        $token = $request->request->get('token');
        $refreshToken = $request->request->get('refresh_token');

        if (null === $refreshToken) {
            throw new BadRequestHttpException('You must provide the token and the refresh_token parameters.');
        }

        $refreshTokenManager = $this->get('auth.services.jwt_refresh_manager');

        if (false === $refreshTokenPayload = $refreshTokenManager->verify($token, $refreshToken)) {
            throw new AccessDeniedHttpException('The refresh token is invalid and has been revoked.');
        }

        $authToken = new JWTUserToken();
        $authToken->setRawToken($refreshToken);
        $authToken = $this->get('security.authentication.manager')->authenticate($authToken);
        $request->attributes->set('refresh_token_key', 'refreshed');

        return $this->get('lexik_jwt_authentication.handler.authentication_success')->onAuthenticationSuccess($request, $authToken);
    }
}
