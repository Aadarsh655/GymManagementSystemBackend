<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Welcome to Our Platform')
                    ->html($this->getContent());
    }

    private function getContent()
    {
        return "
        <html>
            <body>
                <h1>Welcome, {$this->user->name}!</h1>
                <p>Your account has been successfully created. Here are your login details:</p>
                <p><strong>Email:</strong> {$this->user->email}</p>
                <p><strong>Password:</strong> {$this->password}</p>
                <p>Please log in and change your password at your earliest convenience.</p>
                <p>Thank you for joining us!</p>
            </body>
        </html>
        ";
    }
}
