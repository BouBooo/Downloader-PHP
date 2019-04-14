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

        if($YoutubeForm->isSubmitted() && $YoutubeForm->isValid()) {

            return $this->render('downloader/youtube.html.twig', [
                'result' => $YoutubeForm['youtube_link']->getData()
            ]);
            //return $this->redirectToRoute('downloader/youtube.html.twig');
        }


        $SoundcloudForm = $this->createFormBuilder()
                                ->add('soundcloud_link')
                                ->add('search', SubmitType::class)
                                ->getForm();

        $SoundcloudForm->handleRequest($request);

        if($SoundcloudForm->isSubmitted() && $SoundcloudForm->isValid()) {

            return $this->render('downloader/soundcloud.html.twig', [
                'result' => $SoundcloudForm['soundcloud_link']->getData()
            ]);
        }
                
        return $this->render('downloader/index.html.twig', [
            'formYoutube' => $YoutubeForm->createView(),
            'formSoundcloud' => $SoundcloudForm->createView()
        ]);
    }
}
