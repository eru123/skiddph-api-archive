<?php

namespace Api\Auth;

use Firebase\JWT\JWT as fJWT;
use Firebase\JWT\Key as fKey;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;
use Exception;
use Error;

use Api\Lib\Date;
use Auth;
use Config;

class JWT
{
    public static function encode(array $payload, $exp = 0): string
    {
        $cfg = Auth::config();
        $time = Date::now();
        $key = $cfg->get('JWT_KEY');
        $alg = $cfg->get('JWT_ALG', 'HS256');
        $payload['iat'] = $time;
        $exp_t = is_string($exp) && !empty(trim($exp)) ? Date::parse($exp, "s", Date::UNIT) : ((int) $exp > 0 ? (int) $exp : false);
        if ($exp_t) {
            $payload['exp'] = $time + $exp_t;
        }
        return fJWT::encode($payload, $key, $alg);
    }

    public static function decode(string $token): array
    {
        $cfg = Auth::config();
        $key = $cfg->get('JWT_KEY');
        $alg = $cfg->get('JWT_ALG', 'HS256');

        try {
            return (array) fJWT::decode($token, new fKey($key, $alg));
        } catch (SignatureInvalidException $e) {
            throw new Exception('Invalid signature', 401);
        } catch (BeforeValidException $e) {
            throw new Exception('Token not valid yet', 401);
        } catch (ExpiredException $e) {
            throw new Exception('Token expired', 401);
        } catch (DomainException $e) {
            throw new Exception('Invalid token', 401);
        } catch (InvalidArgumentException $e) {
            throw new Exception('Invalid token', 401);
        } catch (UnexpectedValueException $e) {
            throw new Exception('Invalid token', 401);
        } catch (Exception $e) {
            throw new Exception('Invalid token', 401);
        } catch (Error $e) {
            throw new Exception('Invalid token', 401);
        }
    }
}
