<?php


namespace MarketBoard;

use Anddye\Mailer\Mailable;

class EmailSeller extends Mailable
{
    protected $email_variables;

    public function __construct($email_variables)
    {
        $this->email_variables = $email_variables;
    }

    public function build()
    {
        $this->setSubject('You have got a message from Market Board');
        $this->setView('emails/email_seller.html.twig', [
            'params' => $this->email_variables,
        ]);

        return $this;
    }
}
