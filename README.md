# Symfony - Refresh JWT token example

## About

This example use the [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
and the [GfreeauGetJWTBundle](https://github.com/gfreeau/GfreeauGetJWTBundle) bundles,
and allow to add a `/refresh_token` route in order to ... refresh a jwt token.

## How it works ?

When getting a token, the client can ask for a refresh token to renew the first one after is validity limit date.
Each refresh token is signed and cached on the server. His default validity period is 2 days (see JwtRefreshManager).

## Dependencies

* [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
* [DoctrineCacheBundle](https://github.com/doctrine/DoctrineCacheBundle)

### RSA keys

You must generate your public & private keys to generate secure tokens. You can use the `bin/generate_jwt_keys.sh` in order to do it.

## Wokflow

### Step 1 : Get the jwt token with a refresh_token_key parameter

> curl -X POST http://foo.bar.docker/get_token -d username=user -d password=password -refresh_token_key=test

```json
{
  "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0NDMwMTk0MzQsInVzZXJuYW1lIjoidXNlciIsImlhdCI6IjE0NDMwMTk0MjkifQ.DmLJbgnhSFQckTT0Z_1yUwSZmeAlOri4JwYnc6e5mCOo0oFyixv1yteTuH9at3LxIyCqIXJlSATW-sGCiHCnmW1NO91-162xU1OkdXsukTpDZN4oZ3yo9rrpk_7kGzjIGOsvEqt9b6jahIDzlvgJKkfRGNuwuH9A081KAl5cpgTVQYrCQ52Kdsxoy_pAVx_heWdqpGex1GrIp8lwOjgbilvmTe189ijZTcAWcS8uV1G0dkud7DC7sLiy8-ma4r94gxNf7Vc-0tQV_PXmELSnT1ZSZdUH1U1Ct7o6lWBgNQJGkW18QB8ji-NLDZ0LxqoZzpwh38rYt_Wno1q6EL4AYQnT3wPYRjfxj2OiAm3M69-ed9wG2ZiYLkiI9i1eBn8tLf1hvNOXAfpFIMg560OtPhI_xdJLIz4yV6pHqQYlGvyd0CWZuH-5BWNlJHl2mqn3FSeZujpiNON6gWVVaHP60j3IDwqv87FTU8ESIussCvi4gTnVQM-vMVS3dKaZLvx36KQvvhpSAarpRh3ZJ58ivo836XFlc1PcbsnsPnfHZLF3tfgLmY0iRBopLSObnrH-lFaPbEeMw-HcaSsgh2JYGR4jNc0Zf8q7-IRrwem4n2n-LqHygil8uhAeZxr9g46wwSfwKapl7r64ImHCoMf28BV6SjbD5gFsvTD--jeiwH4",
  "refresh_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJ0b2tlbiI6eyJleHAiOjE0NDMwMTk0MzQsInVzZXJuYW1lIjoidXNlciIsImlhdCI6IjE0NDMwMTk0MjkifSwiZXhwIjoxNDQzMTkyMjI5LCJ1c2VybmFtZSI6InVzZXIiLCJpYXQiOiIxNDQzMDE5NDI5In0.TwEB42iI9u4UihQ5a24NI24drZFIRgNMdTJzQpXjMz1AtiO4DBGOHiqkOSMCcZj282WxFZT-Ry7G2-QsAl2baoPEboaKdG4-z-sLbjiJJLIoRg73QSga7lk9LZnK8O_0zsTsK4dNY4RFlk8Tu8dDhZ6UBy4ntkdq-rYUvHdhtqd4bXJ2exeAVKDi48y6HxX8OejEgSpc_JsjrZ_O1gE1yG_ZAzxjH63TAZYru8CITdNdUY50Z8lkp0yPAFDwVGl4xx5Zc9Tuh7bP2ud3arSx-_spZbTdbrJEc1_HNnN5-0RHqHfN_F5YxoWraEnnM36jgSxLY3mUyEighjGXdwulyEk473xqauGGSl8pKlmyTR1VhQlQrXqptJtOPJolnOdjdu0fcDoB28fUNbCFvlFXmRu7SyFfIyWJGjb-2wC0H5V0wwVvP1z1FYt8dbPRbyr63u4RjW_xXCpW-McON_4zalHGIsJk43ONJCXGa8FiUfIKl3mW8ipcHybev-6uaJLviCBi2zUpC9cMTElbEYXNqRKRZctHACi3ztb8FU8FO6f9J-radRV4pKaABh7wba_AE3OFdHlNzrvATun8iyshQgWFpUqFzuBPwKBRJlsm1a_4VRHZZzfnj2SSWq9fcxACCpSXKa0iPMT2wQpia7pdeiJPFsM0y_oQirO-Jn6Pimk"
}
```
You can decode the token by using [http://jwt.io/](http://jwt.io/)

In case of invalid credentials, you get the following response :

```json
{"code":401,"message":"Bad credentials"}
```

### Step 2 : use the token on each request

In request header :

```
Authorization: Bearer {token}
```

### Step 3 : the token is outdated

Your request with a token returns a 401 response.

### Step 4 : use the refresh token to get a new token

> curl -X POST http://foo.bar.docker/refresh_token -d token=eyJhbGciOiJS... -d refresh_token=eyJhbGc...

```json
{
  "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0NDMwMTk0MzQsInVzZXJuYW1lIjoidXNlciIsImlhdCI6IjE0NDMwMTk0MjkifQ.DmLJbgnhSFQckTT0Z_1yUwSZmeAlOri4JwYnc6e5mCOo0oFyixv1yteTuH9at3LxIyCqIXJlSATW-sGCiHCnmW1NO91-162xU1OkdXsukTpDZN4oZ3yo9rrpk_7kGzjIGOsvEqt9b6jahIDzlvgJKkfRGNuwuH9A081KAl5cpgTVQYrCQ52Kdsxoy_pAVx_heWdqpGex1GrIp8lwOjgbilvmTe189ijZTcAWcS8uV1G0dkud7DC7sLiy8-ma4r94gxNf7Vc-0tQV_PXmELSnT1ZSZdUH1U1Ct7o6lWBgNQJGkW18QB8ji-NLDZ0LxqoZzpwh38rYt_Wno1q6EL4AYQnT3wPYRjfxj2OiAm3M69-ed9wG2ZiYLkiI9i1eBn8tLf1hvNOXAfpFIMg560OtPhI_xdJLIz4yV6pHqQYlGvyd0CWZuH-5BWNlJHl2mqn3FSeZujpiNON6gWVVaHP60j3IDwqv87FTU8ESIussCvi4gTnVQM-vMVS3dKaZLvx36KQvvhpSAarpRh3ZJ58ivo836XFlc1PcbsnsPnfHZLF3tfgLmY0iRBopLSObnrH-lFaPbEeMw-HcaSsgh2JYGR4jNc0Zf8q7-IRrwem4n2n-LqHygil8uhAeZxr9g46wwSfwKapl7r64ImHCoMf28BV6SjbD5gFsvTD--jeiwH4",
  "refresh_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJ0b2tlbiI6eyJleHAiOjE0NDMwMTk0MzQsInVzZXJuYW1lIjoidXNlciIsImlhdCI6IjE0NDMwMTk0MjkifSwiZXhwIjoxNDQzMTkyMjI5LCJ1c2VybmFtZSI6InVzZXIiLCJpYXQiOiIxNDQzMDE5NDI5In0.TwEB42iI9u4UihQ5a24NI24drZFIRgNMdTJzQpXjMz1AtiO4DBGOHiqkOSMCcZj282WxFZT-Ry7G2-QsAl2baoPEboaKdG4-z-sLbjiJJLIoRg73QSga7lk9LZnK8O_0zsTsK4dNY4RFlk8Tu8dDhZ6UBy4ntkdq-rYUvHdhtqd4bXJ2exeAVKDi48y6HxX8OejEgSpc_JsjrZ_O1gE1yG_ZAzxjH63TAZYru8CITdNdUY50Z8lkp0yPAFDwVGl4xx5Zc9Tuh7bP2ud3arSx-_spZbTdbrJEc1_HNnN5-0RHqHfN_F5YxoWraEnnM36jgSxLY3mUyEighjGXdwulyEk473xqauGGSl8pKlmyTR1VhQlQrXqptJtOPJolnOdjdu0fcDoB28fUNbCFvlFXmRu7SyFfIyWJGjb-2wC0H5V0wwVvP1z1FYt8dbPRbyr63u4RjW_xXCpW-McON_4zalHGIsJk43ONJCXGa8FiUfIKl3mW8ipcHybev-6uaJLviCBi2zUpC9cMTElbEYXNqRKRZctHACi3ztb8FU8FO6f9J-radRV4pKaABh7wba_AE3OFdHlNzrvATun8iyshQgWFpUqFzuBPwKBRJlsm1a_4VRHZZzfnj2SSWq9fcxACCpSXKa0iPMT2wQpia7pdeiJPFsM0y_oQirO-Jn6Pimk"
}
```

## Command

You can revoke all registered refresh tokens by using the command `bin/console jwt:refresh-tokens-flus`.

## Caution

This project is an example, and can't work out of the box. You must add the concerned files in you sf project.
