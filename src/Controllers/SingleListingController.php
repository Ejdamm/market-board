<?php


namespace MarketBoard\Controllers;

use Exception;
use MarketBoard\EmailSeller;
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
            return $this->view->render($response->withStatus(404), 'errors/error404.html.twig', [
                'language' => $this->language,
                'settings' =>$this->settings,
            ]);
        }

        return $this->view->render($response, 'single_listing.html.twig', [
            'listing' => $listing,
            'language' => $this->language,
            'captcha' => Utils::createCaptcha($this->session),
            'settings' => $this->settings,
        ]);
    }

    public function post($request, $response, $args): ResponseInterface
    {
        $params = $request->getParams();

        if (array_key_exists("removal_form", $params)) {
            $responseParams = $this->processRemove($args['id'], $params['removal_code']);
            return $this->view->render($response, 'remove_listing.html.twig', $responseParams);
        } elseif (array_key_exists("email_form", $params)) {
            $responseParams = $this->processContact($args['id'], $params);
            return $this->view->render($response, 'single_listing.html.twig', $responseParams);
        } elseif (array_key_exists("refresh_captcha", $params)) {
            return $this->view->render($response, 'single_listing.html.twig', [
                'listing' => $this->listings->getSingleListing($args['id']),
                'language' => $this->language,
                'captcha' => Utils::createCaptcha($this->session),
                'params' => $params,
                'settings' => $this->settings,
            ]);
        } else {
            throw new Exception("Unknown post request was sent.");
        }
    }

    private function processContact($listingId, $params)
    {
        $listing = $this->listings->getSingleListing($listingId);

        if (!$params['captcha'] || $this->session->get('captcha') != $params['captcha']) {
            $alertText = $this->language['wrong_captcha'];
            $alert = ['level' => 'warning', 'text' => $alertText];
        } else {
            $this->sendEmail($listingId, $params['email_from'], $listing['email'], $params["email_text"]);
            $alertText = "<strong>" . $this->language['success'] . "</strong> " . $this->language['your_email_was_sent'];
            $alert = ['level' => 'info', 'text' => $alertText];

            //We only refill form if it was unsuccessful
            $params = null;
        }

        return [
            'alert' => $alert,
            'listing' => $listing,
            'language' => $this->language,
            'captcha' =>Utils::createCaptcha($this->session),
            'params' => $params,
            'settings' => $this->settings,
        ];
    }

    private function processRemove($listingId, $removalCode)
    {
        $affectedRows = $this->listings->removeListing($listingId, $removalCode);

        $alert = [];
        if ($affectedRows >= 1) {
            $alert['text'] = "<strong>" . $this->language['success'] . "</strong> "
                . $this->language['listing_removed_success'] . " <a href=\"/\">" . $this->language['go_back_to_start'] . "</a>";
            $alert['level'] = "success";
            $this->logger->addInfo("Listing removed: " . $listingId);
        } else {
            $alert['text'] = "<strong>" . $this->language['warning'] . "</strong> "
                . $this->language['listing_removed_failed']
                . " <a href=\"/listings/" . $listingId . "\">" . $this->language['go_back_to_listing'] . "</a>";
            $alert['level'] = "warning";
            $this->logger->addWarning("Listing was not removed: " . $listingId);
        }

        return [
            'alert' => $alert,
            'language' => $this->language,
            'captcha' => Utils::createCaptcha($this->session),
            'settings' => $this->settings,
        ];
    }

    private function sendEmail($listingId, $sender, $receiver, $text)
    {
        $this->logger->addDebug("Sending email from: " . $sender . " to: " . $receiver);
        $message = new stdClass;
        $message->listingId = $listingId;
        $message->message = $text;

        // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
        if (getenv('TRAVIS') != 'true') {
            $emailSeller = new EmailSeller($message, $this->language['email_contact_seller_subject'], $this->settings['domain']);
            $emailSeller->setReplyTo($sender);
            $this->mailer->setTo($receiver)->sendMessage($emailSeller);
        }
    }
}
