<?php

/**
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Controller;

use App\Newsletter\Exception\EmailAlreadyConfirmedException;
use App\Newsletter\Exception\EmailExistsException;
use App\Newsletter\Exception\GenericNewsletterException;
use App\Newsletter\Exception\InvalidTokenException;
use App\Newsletter\Model\NewsletterEmail;
use App\Newsletter\FormType\NewsletterEmailType;
use App\Newsletter\FormType\NewsletterTokenType;
use App\Newsletter\Model\NewsletterToken;
use App\Newsletter\Service\NewsletterService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NewsletterController
 * @package App\Controller
 */
class NewsletterController extends AbstractController
{
    // ToDo: Clean this class and maybe also outsource some things.
    // ToDo: Create an object and pass it to the template

    private const FORM_EMAIL = 'newsletter_email';
    private const FORM_SUBMIT_STEP_1_NAME = 'newsletter_step_1_done';
    private const FORM_SUBMIT_STEP_2_NAME = 'newsletter_step_2_done';

    /**
     * @var NewsletterService
     */
    private $newsletterService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NewsletterController constructor.
     * @param NewsletterService $newsletterService
     * @param LoggerInterface $logger
     */
    public function __construct(NewsletterService $newsletterService, LoggerInterface $logger)
    {
        $this->newsletterService = $newsletterService;
        $this->logger = $logger;
    }

    /**
     * @Route(path="/newsletter_register", name="newsletter_register", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @return string|Response
     */
    public function register(Request $request, SessionInterface $session)
    {
        $form = $this->createForm(NewsletterEmailType::class, new NewsletterEmail());

        try {

            if ($request->isXmlHttpRequest()) {
                $form->handleRequest($request);

                /** @var NewsletterEmail $newsletterEmail */
                $newsletterEmail = $form->getData();
                $newsletterEmail->setLocale($request->getLocale());
                $this->logger->info('email is ' . $newsletterEmail->getEmail());

                if ($form->isSubmitted()) {
                    $this->logger->debug('is submitted');
                    $session->set(static::FORM_EMAIL, $newsletterEmail->getEmail());
                    $this->logger->info('set email to ' . $newsletterEmail->getEmail());
                    if (!$form->isValid()) {
                        throw new GenericNewsletterException('Please check the format of the e-mail address.');
                    }
                    $this->logger->debug('is valid');

                    // Make sure to redirect to the second step, if step1 is already done
//                    if ($session->get(static::FORM_SUBMIT_STEP_1_NAME) || $session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
//                        $this->logger->debug('forward from register() to registerToken()');
//                        return $this->forward(static::class . '::registerToken');
//                    }

                    try {
                        $this->newsletterService->registerEmail($newsletterEmail);
                    } catch (EmailExistsException $exception) {
                        // ToDo: if the email is still unconfirmed, then resend the email and forward to.
//                        $this->newsletterService->resendEmail($newsletterEmail);
                        $this->logger->error($exception->getMessage());
                    }
                    $session->set(static::FORM_SUBMIT_STEP_1_NAME, true);

                    return $this->forward(static::class . '::registerToken');
                }
            }

        } catch (GenericNewsletterException $exception) {
            $this->logger->error($exception->getMessage());
            $errorMessage = $exception->getMessage();
        } catch (EmailAlreadyConfirmedException $exception) {
            $this->logger->error($exception->getMessage());
            $errorMessage = 'The email is already registered and confirmed :)';
        } catch (ORMException|OptimisticLockException|NonUniqueResultException|TransportExceptionInterface|\Exception $exception) {
            $errorMessage = 'A technical problem has occured. Please try again later.';
            $this->logger->error($exception->getMessage());
        }

        return $this->render('elements/newsletter/newsletter_email.html.twig', [
            'form' => $form->createView(),
            'errorMessage' => $errorMessage ?? null
        ]);
    }

    /**
     * @Route(path="/newsletter_register_token", name="newsletter_register_token", methods={"POST"})
     * @param SessionInterface $session
     * @param Request $request
     * @return Response
     */
    public function registerToken(SessionInterface $session, Request $request)
    {
        if (!$session->get(static::FORM_SUBMIT_STEP_1_NAME)) {
            $this->logger->debug('forward from registerToken() to register()');
            return $this->forward('App\Controller\NewsletterController::register');
        }

        if ($session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
            $this->logger->debug('forward from registerToken() to registerSuccess()');
            return $this->forward('App\Controller\NewsletterController::registerSuccess');
        }

        $form = $this->createForm(NewsletterTokenType::class, new NewsletterToken());
        try {
            /** @var NewsletterToken $token */
            $token = $form->getData();

            if ($request->isXmlHttpRequest()) {
                $this->logger->debug('is xhr');
                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    $this->logger->debug('is submitted');
                    if (!$form->isValid()) {
                        throw new GenericNewsletterException('Please check the format of the token.');
                    }

                    $this->logger->debug('is valid');

                    $email = $session->get(static::FORM_EMAIL);

                    $this->newsletterService->confirmToken($email, $token->getToken());

                    $session->set(static::FORM_SUBMIT_STEP_2_NAME, true);
                    $this->logger->debug('forward to sucess');
                    return $this->forward('App\Controller\NewsletterController::registerSuccess');
                }
            }

        } catch (InvalidTokenException $exception) {
            $errorMessage = $exception->getMessage();
        } catch (\Exception $exception) {
            // ToDo: return a general error here later.
            $errorMessage = $exception->getMessage();
        }

        return $this->render('elements/newsletter/newsletter_token.html.twig', [
            'form' => $form->createView(),
            'errorMessage' => $errorMessage ?? null,
            'email' => $session->get(static::FORM_EMAIL) ?? null
        ]);
    }

    /**
     * @Route(path="/newsletter_register_success", name="newsletter_register_success", methods={"POST"})
     */
    public function registerSuccess()
    {
        return $this->render('elements/newsletter/newsletter_success.html.twig');
    }
}
