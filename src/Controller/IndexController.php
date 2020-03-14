<?php
/**
 * This software belongs to Dennis Barlowe (Aschaffenburg, Germany) and is copyrighted.
 *
 * Any unauthorized use of this software without having a valid license
 * violates the license agreement and will be prosecuted by the proper authorities.
 *
 * Creator: dbarlowe
 * Date: 03.03.20 - 21:07
 *
 * @link https://www.dennzo.com
 * @copyright 2020 Dennis Barlowe
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    const ALLOWED_LOCALES = ['en', 'de'];

    /**
     * @Route("/", name="root")
     * @param Request $request
     * @return RedirectResponse
     */
    public function root(Request $request)
    {
        if (in_array($request->getLocale(), self::ALLOWED_LOCALES)) {
            return $this->redirect('/' . $request->getLocale());
        }

        return $this->redirect('/en');
    }

    /**
     * @Route({
     *     "en": "/en/imprint",
     *     "de": "/de/impressum"
     * }, name="imprint", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function imprint(Request $request): Response
    {
        return $this->render('pages/imprint.html.twig', [
            "locale" => $request->getLocale(),
            "page" => "imprint"
        ]);
    }

    /**
     * @Route({
     *     "en": "/en/privacy_policy",
     *     "de": "/de/datenschutz"
     * }, name="privacy_policy", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function privacyPolicy(Request $request): Response
    {
        return $this->render('pages/privacy-policy.html.twig', [
            "locale" => $request->getLocale(),
            "page" => "privacy-policy"
        ]);
    }


    /**
     * @Route("/{_locale}", name="home_page", methods={"GET"})
     * @param Request $request
     * @param string $_locale
     * @return Response
     */
    public function homePageAction(Request $request, string $_locale): Response
    {
        if (!in_array($_locale, self::ALLOWED_LOCALES)) {
            return $this->redirect('/en');
        }

        return $this->render('pages/homepage.html.twig', [
            "locale" => $request->getLocale(),
            "page" => "homepage"
        ]);
    }
}
