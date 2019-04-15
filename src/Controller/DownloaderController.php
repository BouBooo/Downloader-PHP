<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DownloaderController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home() {
        return $this->render('home.html.twig');
    }



    /**
     * @Route("/downloader", name="downloader")
     */
    public function index(Request $request)
    {   
        $YoutubeForm = $this->createFormBuilder()
                            ->add('youtube_link')
                            ->add('search', SubmitType::class)
                            ->getForm();

        $YoutubeForm->handleRequest($request);

        if($YoutubeForm->isSubmitted() && $YoutubeForm->isValid()) {

            $url = $YoutubeForm['youtube_link']->getData();
            $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
            $match;
    
            // Check valid Youtube url
            if(preg_match($regex_pattern, $url, $match)) {
                parse_str( parse_url($url, PHP_URL_QUERY ), $my_array_of_vars );
                $api_key = 'AIzaSyCUCIPiVy6t0KigYdr9LgwkK55kWuUywxQ';

                if(!empty($my_array_of_vars['list']))
                {
                    $video = '<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list='.$my_array_of_vars['list'].'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                    $download_link = '<span class="alert alert-danger"> Youtube playlist download don\'t work yet </span><br>';
                }
                // Youtube track
                else if(empty($my_array_of_vars['list']) && !empty($my_array_of_vars['v']))
                {               
                    $video = $my_array_of_vars['v'];

                    return $this->render('downloader/youtube.html.twig', [
                        'video' => $video,
                        'result' => $url,
                        'api_key' => $api_key
                    ]);

                }
                else
                {
                    // Get Channel Id only from Ytb URL
                    $parts = parse_url($url);
                    $path_parts= explode('/', $parts['path']);
                    $channelId = $path_parts[2];
                    $maxResults = 30;

                    $videoList = json_decode(file_get_contents('https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId='.$channelId.'&maxResults='.$maxResults.'&key='.$api_key.''));

                    $listVideoChannel = true;
                }   
            }
            else {
                return $this->render('downloader/youtube.html.twig', [
                    'result' => 'Invalid url',
                    'api_key' => 'Not delivered yet'
                ]);
            }
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
