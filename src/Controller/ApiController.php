<?php

namespace App\Controller;

use Doctrine\DBAL\Schema\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    /*************************************/
    /*                                   */
    /*             ROUTES                */
    /*                                   */
    /*************************************/

    /**
     * @Route("/api", name="api")
     */
    public function index(Request $request)
    {
        $key = $this->getApiKey($request);
        $apiKeyIsValid = $this->checkApiKey($key);

        if($apiKeyIsValid == true)  {
            return new JsonResponse([
                'success' => "true",
                'message' => "Welcome to Mass Music API",
                'actions' => [
                    'youtube_link' => "http://127.0.0.1:8000/api/youtube?api_key=?&url=?",
                    'soundcloud_link' => "http://127.0.0.1:8000/api/soundcloud?api_key=?&url=?"
                ]
            ]);
        }
        else    {
            

        return new JsonResponse([
            'success' => "false",
            'message' => "Please enter valid API key"
        ]);
        }
    }


    /**
     * @Route("/api/youtube", name="youtube")
     */
    public function youtubeLink(Request $request)
    {
        $key = $this->getApiKey($request);
        $apiKeyIsValid = $this->checkApiKey($key);

        $url = $this->getYoutubeLink($request);
        $validLink = $this->checkYoutubeLink($url);
        $obj = $this->getYoutubeObject($obj);

        if($apiKeyIsValid) {

            if($validLink)  {
                return new JsonResponse([
                    'success' => "true",
                    'kind' => "Youtube URL",
                    'link' => $url,
                    'actions' => [
                        'stream' => "yes",
                        'download' => "yes"
                    ],
                    'object' => $obj
                ]);
            }
            else    {
                return new JsonResponse([
                    'success' => "false",
                    'message' => "Invalid Youtube url"
                ]);
            }
        }
        else    {
            
            return new JsonResponse([
                'success' => "false",
                'message' => "Please enter valid API key"
            ]);
            }
            
    }


    /**
     * @Route("/api/soundcloud", name="soundcloud")
     */
     public function soundcloudLink(Request $request)
     {
         $key = $this->getApiKey($request);
         $apiKeyIsValid = $this->checkApiKey($key);
 
         $soundcloudClient = $this->getSoundcloudApiKey();
         $url = $this->getSoundcloudLink($request);
         $validLink = $this->checkSoundcloudLink($url);
         $obj = $this->getSoundcloudObject($url);
         $tracksInfos = $this->listSoundcloudTracks($obj);
         //$array = $this->getTracksArray($tracks);
 
         if($apiKeyIsValid) {
 
             if($validLink)  {
                 return new JsonResponse([
                     'success' => "true",
                     'about' => "Soundcloud URL",
                     'link' => $url,
                     'kind' => $obj->kind,  
                    'Tracks' => $tracksInfos
                 ]);
             }
             else    {
                 return new JsonResponse([
                     'success' => "false",
                     'message' => "Invalid Soundcloud url"
                 ]);
             }
         }
         else    {
             
             return new JsonResponse([
                 'success' => "false",
                 'message' => "Please enter valid API key"
             ]);
             }
             
     }



    /*************************************/
    /*                                   */
    /*             YOUTUBE               */
    /*                                   */
    /*************************************/


    public function getYoutubeLink(Request $request) {
        return $request->query->get('url');
    }

    public function checkYoutubeLink($url) {
        $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";
        $match;

        if(preg_match($regex_pattern, $url, $match)) {
            parse_str(parse_url($url, PHP_URL_QUERY ), $my_array_of_vars );

            return $url;
        } 
        else {
            return false;
        }
    }

    public function getYoutubeObject($url) {
        
        parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
        $api_key = 'AIzaSyCUCIPiVy6t0KigYdr9LgwkK55kWuUywxQ';
        return $my_array_of_vars ?? false;
    }




    /*************************************/
    /*                                   */
    /*             SOUNDCLOUD            */
    /*                                   */
    /*************************************/

    public function getSoundcloudApiKey() {
        $client_id = 'f4094fb8beec3feadb35909471ac9bf5';
        return $client_id;
    }
    public function getSoundcloudLink(Request $request) {
        return $request->query->get('url');
    }

    public function checkSoundcloudLink($url) {
        $regex_pattern = "/^https?:\/\/soundcloud\.com\/\S+\/\S+$/i";
        $match;

        if(preg_match($regex_pattern, $url, $match)) {
            parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars );

            return $url;
        } 
        else {
            return false;
        }
    }


    public function getSoundcloudObject($url) {
        $client = '22e8f71d7ca75e156d6b2f0e0a5172b3';
        $url_api='https://api.soundcloud.com/resolve.json?url='.$url.'&client_id='.$client;
        $json = file_get_contents($url_api);
        $obj=json_decode($json);
        return $obj ?? false;
    }

    public function listSoundcloudTracks($obj) {
        if($obj->kind == 'playlist') {
            $index = [];
            $tracks = [];

            foreach($obj->tracks as $track) {
                
                array_push($tracks, [
                    'title' => $track->title,
                    'id' => $track->id,
                ]);

            }
            return $tracks;
        }
        else {
            return $obj;
        }           
    }


    public function getTracksArray($tracks) {
        $tab = [];
        foreach($tracks as $track) {
            $stream_url = $track->stream_url;
            return array_push($tab, $stream_url);
        }
    }





    /*************************************/
    /*                                   */
    /*             API KEY               */
    /*                                   */
    /*************************************/

    public function getApiKey(Request $request)
    {        
        return $request->query->get('api_key');
    }

    public function checkApiKey($key)
    {        
        return ($key == "myapikey");
    }


}
