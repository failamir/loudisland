<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * RedisPublisher — Real-time Chat Channel via Redis Pub/Sub
 *
 * Publishes messages to Redis Pub/Sub channel so all PHP containers
 * (app1, app2, app3, app4) can receive and broadcast to their WebSocket clients.
 *
 * Architecture: Client → Nginx LB → Any PHP Container → Redis Pub/Sub → All Containers
 */
class RedisPublisher
{
    protected string $connection = 'pubsub';

    /**
     * Publish a chat message to a room channel.
     *
     * @param string $roomId   Chat room or channel identifier
     * @param array  $payload  Message data to broadcast
     */
    public function publishMessage(string $roomId, array $payload): bool
    {
        try {
            $channel = "chat.room.{$roomId}";
            $message = json_encode(array_merge($payload, [
                'timestamp' => now()->toISOString(),
                'container' => env('CONTAINER_NAME', gethostname()),
            ]));

            Redis::connection($this->connection)->publish($channel, $message);

            Log::channel('stack')->debug("Published to Redis channel [{$channel}]", $payload);
            return true;
        } catch (\Exception $e) {
            Log::error("RedisPublisher failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Publish a presence/status event (user online/offline).
     *
     * @param int    $userId
     * @param string $status  'online' | 'offline'
     */
    public function publishPresence(int $userId, string $status): bool
    {
        try {
            $channel = "presence.user.{$userId}";
            $message = json_encode([
                'user_id' => $userId,
                'status' => $status,
                'timestamp' => now()->toISOString(),
            ]);

            Redis::connection($this->connection)->publish($channel, $message);
            return true;
        } catch (\Exception $e) {
            Log::error("RedisPublisher presence failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store session data in Redis (shared across containers).
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl  Seconds until expiry
     */
    public function setSessionData(string $key, mixed $value, int $ttl = 3600): void
    {
        Redis::connection('session')->setex(
            "session_data:{$key}",
            $ttl,
            is_array($value) ? json_encode($value) : $value
        );
    }

    /**
     * Get session data from Redis.
     */
    public function getSessionData(string $key): mixed
    {
        $raw = Redis::connection('session')->get("session_data:{$key}");
        if (!$raw)
            return null;

        $decoded = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
    }
}
