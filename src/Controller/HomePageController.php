<?php

namespace App\Controller;

use App\Service\YoutubeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    #[Route('/', name: 'home_page')]
    public function index(Request $Request, YoutubeService $YoutubeService): RedirectResponse|Response
    {
        parse_str($Request->getQueryString(), $queryParams);
        if ($YoutubeService->checkSessionData()) {
            $YoutubeService->getChannels();
            return $this->render(
                'homepage/home.html.twig',
                ['channels' => $YoutubeService->getChannels()]
            );
        } elseif (isset($queryParams['code'])) {
            $YoutubeService->setGoogleAuthData($queryParams['code']);
            return $this->redirectToRoute('home_page');
        } else {
            return $this->redirect($YoutubeService->authorizeGoogleAccount());
        }
    }
}
