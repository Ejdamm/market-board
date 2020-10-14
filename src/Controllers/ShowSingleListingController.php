<?php


namespace Startplats\Controllers;

use Exception;
use Gregwar\Captcha\CaptchaBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Startplats\EmailSeller;
use Startplats\Listings;
use Startplats\Utils;
use stdClass;

class ShowSingleListingController extends BaseController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function get($request, $response, $args): ResponseInterface
    {
        try {
            $listings = new Listings($this->db);
            $single_listing = $listings->getSingleListing($args['id']);
            if ($single_listing == null) {
                $this->logger->addInfo("/listings/" . $args['id'] . " 404 Tried to access non-existing listing");
                return $this->view->render($response->withStatus(404), 'errors/error404.html.twig', [
                    'language' => $this->language,
                ]);
            }

            $captcha = new CaptchaBuilder;
            $captcha->build();
            $this->session->set('captcha', $captcha->getPhrase());

            return $this->view->render($response, 'single_listing.html.twig', [
                'listing' => $single_listing,
                'language' => $this->language,
                'captcha' => $captcha->inline(),
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/" . $args['id'] . " GET threw exception: " . $e);
            return $this->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                'language' => $this->language,
            ]);
        }
    }

    public function post($request, $response, $args): ResponseInterface
    {
        try {
            $listings = new Listings($this->db);
            $params = $request->getParams();

            if (array_key_exists("removal_form", $params)) {
                $affected_rows = $listings->removeListing($args['id'], $params['removal_code']);

                $alert = [];
                if ($affected_rows >= 1) {
                    $alert['text'] = "<strong>" . $this->language['success'] . "</strong> "
                        . $this->language['listing_removed_success'] . " <a href=\"/\">" . $this->language['go_back_to_start'] . "</a>";
                    $alert['level'] = "success";
                    $this->logger->addInfo("Listing removed: " . $args['id']);
                } else {
                    $alert['text'] = "<strong>" . $this->language['warning'] . "</strong> "
                        . $this->language['listing_removed_failed']
                        . " <a href=\"/listings/" . $args['id'] . "\">" . $this->language['go_back_to_listing'] . "</a>";
                    $alert['level'] = "warning";
                    $this->logger->addWarning("Listing was not removed: " . $args['id']);
                }

                $captcha = new CaptchaBuilder;
                $captcha->build();
                $this->session->set('captcha', $captcha->getPhrase());

                return $this->view->render($response, 'remove_listing.html.twig', [
                    'alert' => $alert,
                    'language' => $this->language,
                    'captcha' => $captcha->inline(),
                ]);
            } elseif (array_key_exists("email_form", $params)) {
                $single_listing = $listings->getSingleListing($args['id']);

                if (!$params['captcha'] || $this->session->get('captcha') != $params['captcha']) {
                    $alert_text = $this->language['wrong_captcha'];
                    $alert = ['level' => 'warning', 'text' => $alert_text];
                } else {
                    $this->logger->addInfo("Sending email from: " . $request->getParam('email_from') . " to: " . $single_listing['email']);
                    $email_variables = new stdClass;
                    $email_variables->listing_id = $args['id'];
                    $email_variables->message = $request->getParam("email_text");

                    // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
                    if (getenv('TRAVIS') != 'true') {
                        $email_seller = new EmailSeller($email_variables);
                        $email_seller->setFrom($request->getParam('email_from'));
                        $this->mailer->setTo($single_listing['email'])->sendMessage($email_seller);
                    }

                    $alert_text = "<strong>" . $this->language['success'] . "</strong> " . $this->language['your_email_was_sent'];
                    $alert = ['level' => 'info', 'text' => $alert_text];

                    //We only refill form if it was unsuccessful
                    $params = null;
                }

                $captcha = new CaptchaBuilder;
                $captcha->build();
                $this->session->set('captcha', $captcha->getPhrase());

                return $this->view->render($response, 'single_listing.html.twig', [
                    'alert' => $alert,
                    'listing' => $single_listing,
                    'language' => $this->language,
                    'captcha' => $captcha->inline(),
                    'params' => $params,
                ]);
            } else {
                throw new Exception("Neither email_form nor removal_form was set.");
            }
        } catch (Exception $e) {
            $this->logger->addError("/listings/" . $args['id'] . " POST threw exception: " . $e);
            return $this->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                'language' => $this->language,
            ]);
        }
    }
}
