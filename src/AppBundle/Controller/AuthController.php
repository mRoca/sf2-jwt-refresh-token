<?php

namespace AppBundle\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthController extends Controller
{
    /**
     * @Route(path="get_token", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function getTokenAction()
    {
        // The security layer will intercept this request
        return new JsonResponse(null, 401);
    }

    /**
     * @Route(path="refresh_token", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function refreshTokenAction(Request $request)
    {
        $refreshToken = $request->request->get('refresh_token', null);
        $token = $request->request->get('token', null);

        if (null === $refreshToken) {
            throw new BadRequestHttpException('You must provide the token and the refresh_token parameters.');
        }

        $refreshTokenManager = $this->get('app.services.jwt_refresh_manager');

        try {
            $refreshTokenManager->verify($token, $refreshToken);
        } catch (\InvalidArgumentException $e) {
            throw new AccessDeniedHttpException('The refresh token is invalid and has been revoked.', $e);
        } finally {
            $refreshTokenManager->delete($token);
        }

        $authToken = new JWTUserToken();
        $authToken->setRawToken($refreshToken);
        $authToken = $this->get('security.authentication.manager')->authenticate($authToken);
        $request->attributes->set('refresh_token_key', 'refreshed');

        return $this->get('lexik_jwt_authentication.handler.authentication_success')->onAuthenticationSuccess($request, $authToken);
    }
}
