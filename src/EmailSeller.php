<?php


namespace MarketBoard;

use Anddye\Mailer\Mailable;

class EmailSeller extends Mailable
{
    private $emailFields;

    public function __construct($emailFields, $subject)
    {
        $this->emailFields = $emailFields;
        $this->setSubject($subject);
    }

    public function build()
    {
        $this->setView('emails/email_seller.html.twig', [
            'params' => $this->emailFields,
        ]);

        return $this;
    }
}
