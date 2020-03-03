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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     * @param Request $request
     * @return Response
     */
    public function homePageAction(Request $request): Response
    {
        return $this->render('pages/homepage.html.twig', [
            "locale" => $request->getLocale()
        ]);
    }
}
