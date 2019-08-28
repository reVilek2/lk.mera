<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Recommendation;
use App\Models\User;

class RecommendationCreated extends Notification
{
    use Queueable;

    /**
     * @var Recommendation
     */
    private $recommendation;
    /**
     * @var User
     */
    private $sender;
    /**
     * @var User
     */
    private $receiver;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Recommendation $recommendation, User $sender, User $receiver)
    {
        $this->recommendation = $recommendation;
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [NotificationDbChannel::class, 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return[
            'recommendation' => [
                'id' => $this->recommendation->id,
                'title' => $this->recommendation->title,
                'created_at' => $this->recommendation->created_at,
                'created_at_humanize' => humanize_date($this->recommendation->created_at, 'd F, H:i'),
            ],
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->getUserName(),
                'role' => $this->sender->getUserRole(),
                'avatar' => $this->sender->getAvatar('thumb'),
            ]
        ];
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'recommendation' => [
                'id' => $this->recommendation->id,
                'title' => $this->recommendation->title,
                'created_at' => $this->recommendation->created_at,
                'created_at_humanize' => humanize_date($this->recommendation->created_at, 'd F, H:i'),
            ],
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->getUserName(),
                'role' => $this->sender->getUserRole(),
                'avatar' => $this->sender->getAvatar('thumb'),
            ]
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * @return array|PrivateChannel
     */
    public function broadcastOn()
    {
        $receiver_id = $this->receiver->id;
        return new PrivateChannel("recommendation.user.{$receiver_id}");
    }
}
