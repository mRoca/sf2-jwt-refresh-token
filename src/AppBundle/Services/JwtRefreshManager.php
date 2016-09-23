<?php

namespace AppBundle\Services;

use Doctrine\Common\Cache\CacheProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class JwtRefreshManager
{
    /** @var JWTEncoderInterface */
    private $encoder;

    /** @var CacheProvider */
    private $cache;

    /** @var int */
    private $ttl;

    const DEFAULT_TTL = 2 * 24 * 3600;

    public function __construct(JWTEncoderInterface $encoder, CacheProvider $cache, int $ttl = self::DEFAULT_TTL)
    {
        $this->encoder = $encoder;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * Creates a refresh token from jwt.
     *
     * @param string $jwtToken
     *
     * @return string
     */
    public function create(string $jwtToken): string
    {
        $payload['token'] = $this->getPayloadFromToken($jwtToken);
        $payload['exp'] = time() + $this->ttl;
        $payload['username'] = $payload['token']['username'] ?? null;

        $refreshToken = $this->encoder->encode($payload);

        if (!$this->store($jwtToken, $refreshToken)) {
            throw new \RuntimeException('Unable to store refresh token in cache.');
        }

        return $refreshToken;
    }

    /**
     * @param string $jwtToken
     * @param string $refreshToken
     *
     * @return bool
     */
    public function store(string $jwtToken, string $refreshToken): bool
    {
        $cacheId = $this->generateHash($jwtToken);

        return $this->cache->save($cacheId, $refreshToken, $this->ttl);
    }

    /**
     * @param string $jwtToken
     * @param string $refreshToken
     *
     * @return array
     */
    public function verify(string $jwtToken, string $refreshToken): array
    {
        /** @var array $payload */
        $payload = $this->encoder->decode($refreshToken);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Invalid jwt token');
        }

        $cacheValue = $this->cache->fetch($this->generateHash($jwtToken));

        if ($cacheValue !== $refreshToken) {
            throw new \InvalidArgumentException('The jwt token does not match.');
        }

        return $payload;
    }

    /**
     * @param string $jwtToken
     *
     * @return bool
     */
    public function delete(string $jwtToken): bool
    {
        return $this->cache->delete($this->generateHash($jwtToken));
    }

    /**
     * @return bool
     */
    public function flushAll(): bool
    {
        return $this->cache->deleteAll();
    }

    /**
     * @param string $jwtToken
     *
     * @return array
     */
    private function getPayloadFromToken(string $jwtToken): array
    {
        $tokenBody = json_decode(base64_decode(explode('.', $jwtToken)[1], true), true);

        if (null === $tokenBody) {
            throw new \InvalidArgumentException('Invalid jwt');
        }

        return $tokenBody;
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private function generateHash(string $data): string
    {
        return sha1(serialize($data));
    }
}
