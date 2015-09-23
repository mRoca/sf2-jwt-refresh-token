<?php

namespace AuthBundle\Services;

use Doctrine\Common\Cache\CacheProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class JwtRefreshManager implements JwtRefreshManagerInterface
{
    private $encoder;
    private $cache;
    private $ttl;

    const DEFAULT_TTL = 2 * 24 * 3600;

    public function __construct(JWTEncoderInterface $encoder, CacheProvider $cache, $ttl = self::DEFAULT_TTL)
    {
        $this->encoder = $encoder;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function create($jwtToken)
    {
        $payload['token'] = $this->getPayloadFromToken($jwtToken);
        $payload['exp'] = time() + $this->ttl;
        $payload['username'] = $payload['token']['username'];

        $cacheId = $this->generateHash($jwtToken);
        $refreshToken = $this->encoder->encode($payload);

        if (!$this->cache->save($cacheId, $refreshToken, $this->ttl)) {
            return null;
        }

        return $refreshToken;
    }

    public function verify($jwtToken, $refreshToken)
    {
        // Decode and check the refreshToken validity
        if (!($refreshPayload = $this->encoder->decode($refreshToken))) {
            return false;
        }

        $cacheId = $this->generateHash($jwtToken);
        $cacheValue = $this->cache->fetch($cacheId);
        $this->cache->delete($cacheId);

        if ($cacheValue !== $refreshToken) {
            return false;
        }

        return $refreshPayload;
    }

    public function flushAll()
    {
        return $this->cache->deleteAll();
    }

    private function getPayloadFromToken($jwtToken)
    {
        $tokenBody = json_decode(base64_decode(explode('.', $jwtToken)[1]), true);

        if (null === $tokenBody) {
            throw new \InvalidArgumentException('Invalid jwt');
        }

        return $tokenBody;
    }

    private function generateHash($data)
    {
        return sha1(serialize($data));
    }
}
