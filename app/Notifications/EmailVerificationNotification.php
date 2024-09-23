<?php

namespace App\Notifications;

use Ichtrojan\Otp\Otp as OtpOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Ichtrojan\Otp\Otp;
// use Otp;

class EmailVerificationNotification extends Notification
{
    use Queueable;
    public $message ;
    public $subject ;
    public $fromEmail ;
    public $mailer ;
    public $otp ;



    /**
     * Create a new notification instance.
     *
     * @return void
     */


    public function __construct()
    {
        //
        $this->message ='Use the below code for the Verification Process';
        $this->subject ='Verification Needed';
        $this->fromEmail ='hml-no-reply@blue-tech.ae';
        $this->mailer ='smtp';
        $this->otp =new Otp;


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
        $otp= $this->otp->generate($notifiable->email,6,60);
        return (new MailMessage)
                    ->mailer('smtp')
                    ->subject($this->subject)
                    ->greeting('Hello '.$notifiable->first_name)
                    ->line($this->message)
                    // ->action('Notification Action', url('/'))
                    ->line('code :'.$otp->token);
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
