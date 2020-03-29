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

use App\Form\Newsletter\NewsletterEmail;
use App\Newsletter\FormType\NewsletterEmailType;
use App\Newsletter\FormType\NewsletterTokenType;
use App\Newsletter\Model\NewsletterToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NewsletterController
 * @package App\Controller
 */
class NewsletterController extends AbstractController
{
    // ToDo: Clean this class and maybe also outsource some things.

    public const FORM_SUBMIT_STEP_1_NAME = 'newsletter_step_1_done';
    public const FORM_SUBMIT_STEP_2_NAME = 'newsletter_step_2_done';

    /**
     * @Route(path="/newsletter_register", name="newsletter_register", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @return string|Response
     */
    public function register(Request $request, SessionInterface $session)
    {
        // Make sure to redirect to the second step, if step1 is already done
        if ($session->get(static::FORM_SUBMIT_STEP_1_NAME) || $session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
            return $this->forward('App\Controller\NewsletterController::registerToken');
        }

        $form = $this->createForm(NewsletterEmailType::class, new NewsletterEmail());

        if ($request->isXmlHttpRequest()) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $session->set(static::FORM_SUBMIT_STEP_1_NAME, true);

                    // ToDo: handle $form->getData()
                    //  send email and store information in session to verify the token sent.
                    //  also the token must be stored in the db with the hashed email address and maybe a salt to ensure spam prevention and safety
                    //  each token is only valid for 24h

                    return $this->forward('App\Controller\NewsletterController::registerToken');
                } else {
                    return $this->render('newsletter.html.twig', [
                        'form' => $form->createView(),
                        'error' => true
                    ]);
                }
            }


        }

        return $this->render('newsletter.html.twig', [
            'form' => $form->createView(),
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
            return $this->forward('App\Controller\NewsletterController::register');
        }

        if ($session->get(static::FORM_SUBMIT_STEP_2_NAME)) {
            return $this->forward('App\Controller\NewsletterController::registerSuccess');
        }

        $form = $this->createForm(NewsletterTokenType::class, new NewsletterToken());

        if ($request->isXmlHttpRequest()) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {

                    $session->set(static::FORM_SUBMIT_STEP_2_NAME, true);

                    // ToDo: handle $form->getData()
                    //  verify the token and add a date, when the account has been registered.
                    //  then forward to below, if the token is wrong, display an error message

                    return $this->forward('App\Controller\NewsletterController::registerSuccess');
                } else {
                    return $this->render('newsletter_token.html.twig', [
                        'form' => $form->createView(),
                        'error' => true
                    ]);
                }
            }
        }

        return $this->render('newsletter_token.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(path="/newsletter_register_success", name="newsletter_register_success", methods={"POST"})
     */
    public function registerSuccess()
    {
        return $this->render('newsletter_success.html.twig');
    }
}
