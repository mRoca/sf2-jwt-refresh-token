<?php

namespace AuthBundle\Services;

interface JwtRefreshManagerInterface
{
    public function create($jwtToken);

    public function verify($jwtToken, $refreshToken);

    public function flushAll();
}
