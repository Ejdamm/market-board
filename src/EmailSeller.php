<?php


namespace MarketBoard;

use Anddye\Mailer\Mailable;

class EmailSeller extends Mailable
{
    private $emailFields;
    private $domain;

    public function __construct($emailFields, $subject, $domain)
    {
        $this->emailFields = $emailFields;
        $this->setSubject($subject);
        $this->domain = $domain;
    }

    public function build()
    {
        $this->setView('emails/email_seller.html.twig', [
            'params' => $this->emailFields,
            'server_address' => $_SERVER['REQUEST_SCHEME'] . "://" . $this->domain,
        ]);

        return $this;
    }
}
