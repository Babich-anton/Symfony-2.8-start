<?php

namespace Kuhni\Bundle\Controller;

use Kuhni\Bundle\Entity\DesignProjectShag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DesignProjectShagController extends Controller
{
    public function indexAction(Request $request){
        //geoIP
        $ip = $_SERVER['REMOTE_ADDR'];
        $query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));
        if($query && $query['status'] == 'success') {
            $geo_info = $query['country'].', '.$query['city'].', '.$query['isp'].', '.$query['query'];
        } else { $geo_info = "Не удалось определить координаты посетителя"; }

        $form = $request->get('form');
        $name = htmlspecialchars($form['name']);
        $phone = htmlspecialchars($form['phone']);
        $email = htmlspecialchars($form['email']);
        $style = htmlspecialchars($form['style']);
        $config = htmlspecialchars($form['config']);

        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        $call = new DesignProjectShag();
        $call->setPhone($phone);
        $call->setEmail($email);
        $call->setName($name);
        $call->setConfig($config);
        $call->setStyle($style);
        $call->setUrl($_SERVER['HTTP_REFERER']);
        $call->setGeoIP($geo_info);
        $entityManager->persist($call);
        $entityManager->flush();

        $response = json_encode(array('success' => 'success'));
        return new Response($response);
    }
}