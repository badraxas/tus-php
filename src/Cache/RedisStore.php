<?php

namespace TusPhp\Cache;

use Carbon\Carbon;
use Redis;
use TusPhp\Config;
use Predis\Client as Predis;

class RedisStore extends AbstractCache
{
    /** @var Predis|Redis */
    protected $redis;

    /**
     * RedisStore constructor.
     *
     * @param array $options
     * @throws \RedisException
     */
    public function __construct(array $options = [])
    {
        $options = empty($options) ? Config::get('redis') : $options;

        if (class_exists(Redis::class)) {
            $this->redis = new Redis($options);
            $this->redis->select($options['database']);
        }

        if ( ! isset($this->redis)) {
            $this->redis = new Predis($options);
        }
    }

    /**
     * Get redis.
     *
     * @return Predis|Redis
     */
    public function getRedis(): Predis|Redis
    {
        return $this->redis;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, bool $withExpired = false)
    {
        $prefix = $this->getPrefix();

        if (false === strpos($key, $prefix)) {
            $key = $prefix . $key;
        }

        $contents = $this->redis->get($key);
        if (null !== $contents) {
            $contents = json_decode($contents, true);
        }

        if ($withExpired) {
            return $contents;
        }

        if ( ! $contents) {
            return null;
        }

        $isExpired = Carbon::parse($contents['expires_at'])->lt(Carbon::now());

        return $isExpired ? null : $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, $value)
    {
        $contents = $this->get($key) ?? [];

        if (\is_array($value)) {
            $contents = $value + $contents;
        } else {
            $contents[] = $value;
        }

        $status = $this->redis->set($this->getPrefix() . $key, json_encode($contents));

        return 'OK' === $status->getPayload();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        $prefix = $this->getPrefix();

        if (false === strpos($key, $prefix)) {
            $key = $prefix . $key;
        }

        return $this->redis->del([$key]) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function keys(): array
    {
        return $this->redis->keys($this->getPrefix() . '*');
    }
}
