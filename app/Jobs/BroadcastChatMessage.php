<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\RedisPublisher;

/**
 * BroadcastChatMessage — Asynchronous Chat Message Dispatch via RabbitMQ
 *
 * Flow:
 *   1. Controller dispatches this job → goes to RabbitMQ queue
 *   2. Background Worker consumes job from RabbitMQ
 *   3. Worker calls RedisPublisher::publishMessage()
 *   4. Redis Pub/Sub notifies all PHP containers
 *   5. Each container pushes to their connected WebSocket clients
 *
 * Architecture: Controller → RabbitMQ → Worker → Redis Pub/Sub → All PHP Containers → WebSocket Clients
 */
class BroadcastChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Maximum seconds job can run before timing out.
     */
    public int $timeout = 30;

    public function __construct(
        protected string $roomId,
        protected int $senderId,
        protected string $message,
        protected string $messageType = 'text',
        protected array $metadata = [],
    ) {
    }

    /**
     * Execute the job.
     * Called by Background Worker consuming from RabbitMQ.
     */
    public function handle(RedisPublisher $publisher): void
    {
        Log::info("Processing BroadcastChatMessage", [
            'room_id' => $this->roomId,
            'sender_id' => $this->senderId,
            'type' => $this->messageType,
        ]);

        $success = $publisher->publishMessage($this->roomId, [
            'event' => 'message.sent',
            'room_id' => $this->roomId,
            'sender_id' => $this->senderId,
            'message' => $this->message,
            'message_type' => $this->messageType,
            'metadata' => $this->metadata,
        ]);

        if (!$success) {
            throw new \RuntimeException("Failed to publish message to Redis Pub/Sub");
        }
    }

    /**
     * Handle job failure — called after $tries exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("BroadcastChatMessage failed permanently", [
            'room_id' => $this->roomId,
            'sender_id' => $this->senderId,
            'error' => $exception->getMessage(),
        ]);
    }
}
