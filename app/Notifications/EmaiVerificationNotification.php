<?php

namespace App\Notifications;

use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\OwnerTypeEnum;
use App\Helpers\ResponseHelper;
use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmaiVerificationNotification extends Notification
{
    use Queueable;
    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    public $otp;

    public $tokenGenerated;
    /**
     * Create a new notification instance.
     */
    public function __construct(string $tokenGenerated)
    {
        $this->message='use the bellow code for verification';
        $this->subject='verification needed';
        $this->fromEmail='fadi@gmail.com';
        $this->mailer='smtp';
        $this->otp=new Otp();

        $this->tokenGenerated = $tokenGenerated;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
       // $otp = $this->otp->generate($notifiable->email,'alpha_numeric', 9, 60); //generate recieve(email, number of code, duration of code to expired)

        $otp = $this->otp->generate($notifiable->email,'alpha_numeric', $this->tokenGenerated, 60);
        // $otp = $this->otp->generate($notifiable->email,'alpha_numeric', 9, 60);

        return (new MailMessage)
                        ->mailer('smtp')
                        ->subject($this->subject)
                        ->greeting('Hello '.$notifiable->name )  //notifiable  have all user info
                        ->line('code: ' .$otp->token)
                        ->line($this->message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
