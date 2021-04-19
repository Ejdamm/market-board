<?php


namespace MarketBoard\Controllers;

use Exception;
use MarketBoard\EmailAdvertiser;
use MarketBoard\Listings;
use MarketBoard\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class SingleListingController extends BaseController
{
    private $listings;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->listings = new Listings($this->db);
    }

    public function get($request, $response, $args): ResponseInterface
    {
        $listing = $this->listings->getSingleListing($args['id']);
        if ($listing == null) {
            $this->logger->addInfo(get_class($this) . " 404 Tried to access non-existing listing: " . $args['id']);
            return $this->render404($response);
        }

        return $this->render($response, 'single_listing.html.twig', [
            'listing' => $listing,
            'captcha' => Utils::createCaptcha($this->session),
        ]);
    }

    public function post($request, $response, $args): ResponseInterface
    {
        $params = $request->getParams();

        if (array_key_exists("removal_form", $params)) {
            $responseParams = $this->processRemove($args['id'], $params['removal_code']);
            return $this->render($response, 'remove_listing.html.twig', $responseParams);
        } elseif (array_key_exists("email_form", $params)) {
            $responseParams = $this->processContact($args['id'], $params);
            return $this->render($response, 'single_listing.html.twig', $responseParams);
        } elseif (array_key_exists("refresh_captcha", $params)) {
            return $this->render($response, 'single_listing.html.twig', [
                'listing' => $this->listings->getSingleListing($args['id']),
                'captcha' => Utils::createCaptcha($this->session),
                'params' => $params,
            ]);
        } else {
            throw new Exception("Unknown post request was sent.");
        }
    }

    private function processContact($listingId, $params)
    {
        $listing = $this->listings->getSingleListing($listingId);

        if (!$params['captcha'] || $this->session->get('captcha') != $params['captcha']) {
            $alert = 'alerts/wrong_captcha.html.twig';
        } else {
            $this->sendEmail($listingId, $params['email_from'], $listing['email'], $params["email_text"]);
            $alert = 'alerts/contact_email_sent.html.twig';

            //We only refill form if it was unsuccessful
            $params = null;
        }

        return [
            'alert' => $alert,
            'listing' => $listing,
            'captcha' =>Utils::createCaptcha($this->session),
            'params' => $params,
        ];
    }

    private function processRemove($listingId, $removalCode)
    {
        $affectedRows = $this->listings->removeListing($listingId, $removalCode);

        if ($affectedRows >= 1) {
            $alert = 'alerts/listing_removed.html.twig';
            $this->logger->addInfo("Listing removed: " . $listingId);
        } else {
            $alert = 'alerts/listing_not_removed.html.twig';
            $this->logger->addWarning("Listing was not removed: " . $listingId);
        }

        return [
            'listingId' => $listingId,
            'alert' => $alert,
            'captcha' => Utils::createCaptcha($this->session),
        ];
    }

    private function sendEmail($listingId, $sender, $receiver, $text)
    {
        $this->logger->addDebug("Sending email from: " . $sender . " to: " . $receiver);
        $message = new stdClass;
        $message->listingId = $listingId;
        $message->message = $text;
        $message->language = $this->language;

        // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
        if (getenv('TRAVIS') != 'true') {
            $emailAdvertiser = new EmailAdvertiser($message, $this->language['email_contact_advertiser_subject'], $this->settings['domain']);
            $emailAdvertiser->setReplyTo($sender);
            $this->mailer->setTo($receiver)->sendMessage($emailAdvertiser);
        }
    }
}
