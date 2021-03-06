<?php

namespace Kuhni\Bundle\Controller;

use Kuhni\Bundle\Entity\FormPromo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromoController extends Controller
{
    public function indexAction(){
        return $this->render('promo/index.html.twig', array(
            'formPromo' => $this->getPromoForm(),
        ));
    }

    public function orderAction(Request $request){
        $ip = $_SERVER['REMOTE_ADDR'];
        $query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));
        if($query && $query['status'] == 'success') {
            $geo_info = $query['country'].', '.$query['city'].', '.$query['isp'].', '.$query['query'];
        } else { $geo_info = "Не удалось определить координаты посетителя"; }

        $form = $request->get('form');
        $name = htmlspecialchars($form['name']);
        $email = htmlspecialchars($form['email']);
        $phone = htmlspecialchars($form['phone']);
        $gorod = htmlspecialchars($form['gorod']);

        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $call = new FormPromo();

        $call->setGeoIP($geo_info);
        $call->setUrl((string) $_SERVER['HTTP_REFERER']);
        $call->setName($name);
        $call->setPhone($phone);
        $call->setEmail($email);
        $call->setGorod($gorod);
        $call->setDiscount('40%');
        $entityManager->persist($call);
        $entityManager->flush();

        return new Response(json_encode(array('success' => 'success')));
    }

    private function getPromoForm()
    {
        $promo = new FormPromo();

        $formPromo = $this->createFormBuilder($promo)
            ->add('name', TextType::class, array(
                'attr' => [
                    'placeholder' => 'ВАШЕ ИМЯ *',
                    'pattern' => '^[А-Яа-яЁё\s]{3,}',
                    'title' => 'Имя на Русском',
                    'class' => 'form-control',
                ],
                'label' => false
            ))
            ->add('phone', NumberType::class, array(
                'attr' => [
                    'placeholder' => 'ВАШ ТЕЛЕФОН *',
                    'class' => 'form-control',
                    'pattern' => '[\+][7]{1}[0-9]{3}[0-9]{3}[0-9]{2}[0-9]{2}',
                    'title' => 'Телефон в формате +71234567890',
                    'type' => 'tel',
                ],
                'label' => false,
            ))
            ->add('gorod', TextType::class, array(
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ВАШ ГОРОД *',
                    'pattern' => '^[А-Яа-яЁё\s]{3,}',
                    'title' => 'Имя на Русском',
                ],
                'label' => false,
            ))
            ->add('email', EmailType::class, array(
                'attr' => [
                    'placeholder' => 'ВАШ EMAIL',
                    'class' => 'form-control',
                ],
                'required' => false,
                'label' => false,
            ))
            ->getForm()->createView();

        return $formPromo;
    }
}