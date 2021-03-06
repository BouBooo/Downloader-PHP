<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Track;
use App\Form\SaveType;
use App\Form\SaveTrackType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @Route("/tracks", name="my_tracks")
     */
    public function getTracks(Security $security) {   
        $user = $security->getUser();    
        $tracks = $user->getTracks();
     
        return $this->render('downloader/tracks.html.twig', [
            'user' => $user,
            'tracks' => $tracks
        ]);
    }

    public function saveTrack(Request $request)
    {   
        return $this->render('downloader/youtube.html.twig', [
            'formSave' => $SaveForm->createView()
        ]);
    }


    /**
     * @Route("/downloader", name="downloader")
     */
    public function index(Request $request, ObjectManager $manager, Security $security)
    {   
        $YoutubeForm = $this->createFormBuilder()
                            ->add('youtube_link')
                            ->add('start', SubmitType::class)
                            ->getForm();

        $YoutubeForm->handleRequest($request);

        if($YoutubeForm->isSubmitted() && $YoutubeForm->isValid()) {

            $url = $YoutubeForm['youtube_link']->getData();
            $_SESSION['url'] = $url;
            $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
            $match;
            
            // Check valid Youtube url
            if(preg_match($regex_pattern, $url, $match)) {
                parse_str( parse_url($url, PHP_URL_QUERY ), $my_array_of_vars );
                $api_key = 'AIzaSyCUCIPiVy6t0KigYdr9LgwkK55kWuUywxQ';

                // Youtube playlist
                if(!empty($my_array_of_vars['list']))
                {
                    $list = $my_array_of_vars['list'];
                    $download_link = '<span class="alert alert-danger"> Youtube playlist download don\'t work yet </span><br>';

                    $track = new Track();
                    $form = $this->createForm(SaveTrackType::class,$track);

                    $form->handleRequest($request);

                    $url = $_SESSION['url']; 
                    $userId = $this->getUser();
                    $platform = "Youtube";

                    $track->setUrl($url)
                            ->setUserId($userId)
                            ->setPlatform($platform);

                    $manager->persist($track);
                    $manager->flush();

                    return $this->render('downloader/youtube.html.twig', [
                        'list' => $list,
                        'result' => $url,
                        'api_key' => $api_key
                    ]);
                }
                // Youtube track
                else if(empty($my_array_of_vars['list']) && !empty($my_array_of_vars['v']))
                {               
                    $video = $my_array_of_vars['v'];

                    $track = new Track();
                    $form = $this->createForm(SaveTrackType::class,$track);

                    $form->handleRequest($request);

                    $url = $_SESSION['url']; 
                    $userId = $this->getUser();
                    $platform = "Youtube";

                    $track->setUrl($url)
                            ->setUserId($userId)
                            ->setPlatform($platform);

                    $manager->persist($track);
                    $manager->flush();

                    return $this->render('downloader/youtube.html.twig', [
                        'video' => $video,
                        'result' => $url,
                        'api_key' => $api_key
                    ]);

                }
                // Youtube channel
                else
                {
                    // Get Channel Id only from Ytb URL
                    $parts = parse_url($url);
                    $path_parts= explode('/', $parts['path']);
                    $channelId = $path_parts[2];
                    $maxResults = 20;

                    $videoList = json_decode(file_get_contents('https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId='.$channelId.'&maxResults='.$maxResults.'&key='.$api_key.''));

                    $listVideoChannel = true;

                    return $this->render('downloader/youtube.html.twig', [
                        //'video' => $video,
                        'result' => $url,
                        'api_key' => $api_key,
                        //'item' => $item,
                        'videoList' => $videoList->items,
                    ]);
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
                                ->add('start', SubmitType::class)
                                ->getForm();

        $SoundcloudForm->handleRequest($request);

        if($SoundcloudForm->isSubmitted() && $SoundcloudForm->isValid()) {
            
            $url = $SoundcloudForm['soundcloud_link']->getData();
            $_SESSION['url'] = $SoundcloudForm['soundcloud_link']->getData();
            $api_key = '22e8f71d7ca75e156d6b2f0e0a5172b3';
            $url_api='https://api.soundcloud.com/resolve.json?url='.$url.'&client_id='.$api_key;
            try {
                $json = file_get_contents($url_api);
                $obj=json_decode($json);
            } 
            catch (ErrorException $e) {
                exit($e->getMessage());
            }

            if($obj)
            {                
                // Soundcloud Playlist / Album
                if($obj->kind == 'playlist')
                {
                    $index = 0;
                    $track = new Track();
                    $form = $this->createForm(SaveTrackType::class,$track);

                    $form->handleRequest($request);

                    $url = $_SESSION['url']; 
                    $userId = $this->getUser();
                    $platform = "Soundcloud";

                    $track->setUrl($url)
                            ->setUserId($userId)
                            ->setPlatform($platform);

                    $manager->persist($track);
                    $manager->flush(); 

                    return $this->render('downloader/soundcloud.html.twig', [
                        'url' => $url,
                        'object' => $obj,
                        'count' => $index,
                        'client_id' => $api_key
                    ]);
                }
                // Soundcloud Track
                else if($obj->kind == 'track')
                {
                    $track = new Track();
                    $form = $this->createForm(SaveTrackType::class,$track);

                    $form->handleRequest($request);

                    $url = $_SESSION['url']; 
                    $userId = $this->getUser();
                    $platform = "Soundcloud";

                    $track->setUrl($url)
                            ->setUserId($userId)
                            ->setPlatform($platform);

                    $manager->persist($track);
                    $manager->flush();

                    return $this->render('downloader/soundcloud.html.twig', [
                        'url' => $url,
                        'object' => $obj,
                        'stream' => $obj->stream_url.'?client_id='.$api_key,
                        'download' => $obj->download_url.'?client_id='.$api_key,
                        'form' => $form->createView()
                    ]);
                }
            }
            else
            {
                return $this->redirectToRoute('home');
            }

            return $this->render('downloader/soundcloud.html.twig', [
                'url' => $url
            ]);
        }
                
        return $this->render('downloader/index.html.twig', [
            'formYoutube' => $YoutubeForm->createView(),
            'formSoundcloud' => $SoundcloudForm->createView()
        ]);
    }
}
