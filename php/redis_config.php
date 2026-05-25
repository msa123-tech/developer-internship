<?php
// Redis for login sessions (PHP extension or redis-cli fallback)
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('SESSION_TTL', 3600);
define('REDIS_CLI', '/opt/homebrew/bin/redis-cli');

function runRedisCli(...$args)
{
    if (!is_file(REDIS_CLI)) {
        throw new Exception('redis-cli not found. Run: brew install redis && brew services start redis');
    }

    $cmd = escapeshellcmd(REDIS_CLI);
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg($arg);
    }

    exec($cmd . ' 2>&1', $output, $code);

    if ($code !== 0) {
        throw new Exception('Redis error: ' . implode(' ', $output));
    }

    return trim(implode("\n", $output));
}

function saveSession($token, $userData)
{
    $key = 'session:' . $token;
    $value = json_encode($userData);

    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $redis->setex($key, SESSION_TTL, $value);
        $redis->close();
        return;
    }

    runRedisCli('SETEX', $key, (string) SESSION_TTL, $value);
}

function getSession($token)
{
    $key = 'session:' . $token;

    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $data = $redis->get($key);
        $redis->close();

        if ($data === false) {
            return null;
        }

        return json_decode($data, true);
    }

    $data = runRedisCli('GET', $key);

    if ($data === '' || $data === '(nil)') {
        return null;
    }

    return json_decode($data, true);
}

function deleteSession($token)
{
    $key = 'session:' . $token;

    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        $redis->del($key);
        $redis->close();
        return;
    }

    runRedisCli('DEL', $key);
}
