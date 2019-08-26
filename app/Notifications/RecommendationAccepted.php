<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;
use Carbon\Carbon;

class RecommendationAccepted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $client)
    {
        $this->client = $client;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $recommendation_title = $notifiable->title;
        $recommendation_date = Carbon::parse($notifiable->created_at)->format('Y-m-d H:i');
        $client_name = $this->client->getUserName();

        return (new MailMessage)
            ->subject('Ответ на рекомендацию')
            ->markdown('mail.recommendation.accepted', [
                'recommendation_title' => $recommendation_title,
                'recommendation_date' => $recommendation_date,
                'client_name' => $client_name
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
}
