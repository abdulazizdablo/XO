<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class AvailableNotification extends Notification
{
    use Queueable;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    // public $deviceTokens ;
    public $title ;
    public $body ;


    public function __construct(//$deviceTokens,
        $title,$body)
    {
        // $this->deviceTokens=$deviceTokens;
        $this->title=$title;
        $this->body=$body;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database','firebase'];
    }
    public function toFirebase($notifiable)
    {
        // $deviceTokens = [
        //     '{TOKEN_1}',
        //     '{TOKEN_2}'
        // ];

        return (new FirebaseMessage)
            ->withTitle($this->title)
            ->withBody($this->body)
            ->asNotification([$notifiable->fcm_token]); // OR ->asMessage($deviceTokens);
            // ->sendNotification($notifiable->fcm_token);
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
