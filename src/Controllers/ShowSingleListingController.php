<?php


namespace Startplats\Controllers;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Startplats\EmailSeller;
use Startplats\Listings;
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
            return $this->view->render($response, 'single_listing.html.twig', [
                'listing' => $single_listing,
                'language' => $this->language,
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/" . $args['id'] . " GET threw exception: " . $e);
            return $this->view->render($response, 'error.html.twig', [
                'alert' => ['level' => 'danger', 'text' => $this->language['internal_server_error']],
                'language' => $this->language,
            ]);
        }
    }

    public function post($request, $response, $args): ResponseInterface
    {
        try {
            if (array_key_exists("removal_form", $request->getParams())) {
                $listings = new Listings($this->db);
                $affected_rows = $listings->removeListing($args['id'], $request->getParams()['removal_code']);
                $this->logger->addInfo("Listing removed: " . $args['id']);

                $alert = [];
                if ($affected_rows >= 1) {
                    $alert['text'] = "<strong>" . $this->language['success'] . "</strong> "
                        . $this->language['listing_removed_success'] . " <a href=\"/\">" . $this->language['go_back_to_start'] . "</a>";
                    $alert['level'] = "success";
                } else {
                    $alert['text'] = "<strong>" . $this->language['warning'] . "</strong> "
                        . $this->language['listing_removed_failed']
                        . " <a href=\"/listings/" . $args['id'] . "\">" . $this->language['go_back_to_listing'] . "</a>";
                    $alert['level'] = "warning";
                }

                return $this->view->render($response, 'remove_listing.html.twig', [
                    'alert' => $alert,
                    'language' => $this->language,
                ]);
            } elseif (array_key_exists("email_form", $request->getParams())) {
                $listings = new Listings($this->db);
                $single_listing = $listings->getSingleListing($args['id']);

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

                return $this->view->render($response, 'single_listing.html.twig', [
                    'alert' => ['level' => 'info', 'text' => $alert_text],
                    'listing' => $single_listing,
                    'language' => $this->language,
                ]);
            }
        } catch (Exception $e) {
            $this->logger->addError("/listings/" . $args['id'] . " POST threw exception: " . $e);
            return $this->view->render($response, 'error.html.twig', [
                'alert' => ['level' => 'danger', 'text' => $this->language['internal_server_error']],
                'language' => $this->language,
            ]);
        }
    }
}
