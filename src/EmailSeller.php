<?php


namespace MarketBoard;

use Anddye\Mailer\Mailable;

class EmailSeller extends Mailable
{
    protected $emailFields;

    public function __construct($emailFields)
    {
        $this->emailFields = $emailFields;
    }

    public function build()
    {
        $this->setSubject('You have got a message from Market Board');
        $this->setView('emails/email_seller.html.twig', [
            'params' => $this->emailFields,
        ]);

        return $this;
    }
}
