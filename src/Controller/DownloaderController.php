<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DownloaderController extends AbstractController
{
    /**
     * @Route("/", name="downloader")
     */
    public function index(Request $request)
    {   
        $YoutubeForm = $this->createFormBuilder()
                            ->add('youtube_link')
                            ->add('search', SubmitType::class)
                            ->getForm();

        $YoutubeForm->handleRequest($request);


        $SoundcloudForm = $this->createFormBuilder()
                                ->add('soundcloud_link')
                                ->add('search', SubmitType::class)
                                ->getForm();

        $SoundcloudForm->handleRequest($request);
                
        return $this->render('downloader/index.html.twig', [
            'formYoutube' => $YoutubeForm->createView(),
            'formSoundcloud' => $SoundcloudForm->createView()
        ]);
    }
}
