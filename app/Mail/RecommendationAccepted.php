<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Recommendation;

class RecommendationAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public $fromMailer;

    protected $theme = 'recommendations';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Recommendation $recommendation)
    {
        $this->recommendation = $recommendation;
        $this->fromMailer = 'recommendation_accepted';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $recommendation_text = nl2br($this->recommendation->text);

        return $this
            ->subject($this->recommendation->title)
            ->markdown('mail.recommendation.accepted', [
                'recommendation_text' => $recommendation_text,
            ]);
    }
}
