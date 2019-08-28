<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Document;
use App\Models\User;

class DocumentCreated extends Notification
{
    use Queueable;

    /**
     * @var Document
     */
    private $document;
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
    public function __construct(Document $document, User $sender, User $receiver)
    {
        $this->document = $document;
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
            'document' => [
                'id' => $this->document->id,
                'name' => $this->document->name,
                'amount' => $this->document->amount,
                'created_at' => $this->document->created_at,
                'created_at_humanize' => humanize_date($this->document->created_at, 'd F, H:i'),
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
            'document' => [
                'id' => $this->document->id,
                'name' => $this->document->name,
                'amount' => $this->document->amount,
                'created_at' => $this->document->created_at,
                'created_at_humanize' => humanize_date($this->document->created_at, 'd F, H:i'),
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
        return new PrivateChannel("document.user.{$receiver_id}");
    }
}
