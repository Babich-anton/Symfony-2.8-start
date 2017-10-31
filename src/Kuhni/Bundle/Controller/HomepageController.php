<?php

namespace Kuhni\Bundle\Controller;

use Doctrine\ORM\EntityRepository;
use Kuhni\Bundle\Entity\CallBack;
use Kuhni\Bundle\Entity\CostProject;
use Kuhni\Bundle\Entity\DesignProjectShag;
use Kuhni\Bundle\Entity\freeDesignProject;
use Kuhni\Bundle\Entity\RequestCall;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Session\Session;
use Vich\UploaderBundle\Form\Type\VichImageType;

class HomepageController extends Controller
{
    private $session;
    public function __construct()
    {
        $this->session = new Session();
        $this->session->set('likeKuhniProduct', 0);
    }

    public function indexAction()
    {
        //Создаем построитель запросов Doctrine
        $result = $this->getDoctrine()->getManager()->getRepository('KuhniBundle:Catalog')
            ->createQueryBuilder('n')
            ->select('n')
            ->orderBy('n.id')
            ->getQuery()
            ->getResult();

        if (!empty($result)){
            foreach ($result as $item) {
                $image[] = 'upload/catalog/' . $item->getImageName();
            }
        }

        if (!empty($image)){
            return $this->render('homepage/index.html.twig', array(
                'catalog' => $result,
                'image' => $image,
                'formRequestCallModal' => $this->getRequestCallForm(),
                'formRequestCall' => $this->getRequestCallForm(),
                'formCostProject' => $this->getCostProject(),
                'formFreeDesignShag' => $this->getFreeDesignShagForm(),
                'formFreeProject' => $this->getFreeProjectForm(),
                'form' => $this->getCallBackForm(),

            ));
        }
    }

    private function getFreeDesignShagForm()
    {
        $FreeDesignShag = new DesignProjectShag();

        $formFreeDesignShag = $this->createFormBuilder($FreeDesignShag)
            ->add('name', TextType::class, array('attr' => [
                'placeholder' => 'ВАШЕ ИМЯ *',
                'data-validation-required-message' => 'Укажите ваше Имя.',
                'class' => 'form-control'],
                'label' => false
            ))
            ->add('phone', NumberType::class, array(
                'attr' => [
                    'id' => '123',
                    'data-validation-required-message' => 'Укажите ваш телефон для связи.',
                    'class' => 'form-control',
                    'type' => 'tel',
                ],
                'label' => false,
            ))
            ->add('email', EmailType::class, array(
                'attr' => [
                    'placeholder' => 'Ваш EMAIL *',
                    'class' => 'form-control'
                ],
                'label' => false,
            ))
            ->add('idSalon', EntityType::class, array(
                'class' => 'ApplicationSonataUserBundle:User',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('u');
                    return
                        $qb->where(
                            $qb->expr()->notLike('u.username', ':name')
                        )
                            ->orderBy('u.title', 'ASC')
                            ->setParameter('name', 'admin');
                },
                'attr' => [
                    'data-validation-required-message' => 'Укажите ближайший салон.',
                    'class' => 'form-control',
                ],
                'choice_label' => function ($idSalon) {
                    $address = '';
                    if (!empty($idSalon->getMetro())){
                        $address .= $idSalon->getMetro() . ' | ';
                    }else{
                        $address .= $idSalon->getGorod() . ' | ';
                    }
                    if (!empty($idSalon->getTc())){
                        $address .= $idSalon->getTc() . " ";
                    }else{
                        $address .= "Белорусские кухни ";
                    }
                    $address .= $idSalon->getAddress();
                    return $address;
                },
                'choice_attr' => function($idSalon) {

                    if ($idSalon->getGorod() == 'Москва'){
                        $class = 'metro';
                    }else{
                        $class = '';
                    }

                    return array('class' => $class);
                },
                'label' => false,
            ))
            ->getForm()->createView();

        return $formFreeDesignShag;
    }

    private function getFreeProjectForm()
    {
        $freeProject = new freeDesignProject();

        $formFreeProject = $this->createFormBuilder($freeProject)
            ->add('name', TextType::class, array('attr' => [
                'placeholder' => 'ВАШЕ ИМЯ *',
                'class' => 'form-control'],
                'label' => false
            ))
            ->add('phone', NumberType::class, array(
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'tel',
                ],
                'label' => false,
            ))
            ->add('email', EmailType::class, array(
                'attr' => [
                    'placeholder' => 'Ваш EMAIL',
                    'class' => 'form-control',
                    'required' => false,
                ],
                'label' => false,
            ))
            ->add('message', TextType::class, array(
                'attr' => [
                    'placeholder' => 'ВАШЕ СООБЩЕНИЕ *',
                    'class' => 'form-control',
                ],
                'label' => false,
            ))
            ->add('imageFile', VichImageType::class, array(
                'required'      => false,
                'allow_delete'  => true,
                'download_link' => false,
                'label'         => false,
            ))
            ->add('idSalon', EntityType::class, array(
                'class' => 'ApplicationSonataUserBundle:User',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('u');
                    return
                        $qb->where(
                            $qb->expr()->notLike('u.username', ':name')
                        )
                            ->orderBy('u.title', 'ASC')
                            ->setParameter('name', 'admin');
                },
                'attr' => [
                    'data-validation-required-message' => 'Укажите ближайший салон.',
                    'class' => 'form-control',
                ],
                'choice_label' => function ($idSalon) {
                    $address = '';
                    if (!empty($idSalon->getMetro())){
                        $address .= $idSalon->getMetro() . ' | ';
                    }else{
                        $address .= $idSalon->getGorod() . ' | ';
                    }
                    if (!empty($idSalon->getTc())){
                        $address .= $idSalon->getTc() . " ";
                    }else{
                        $address .= "Белорусские кухни ";
                    }
                    $address .= $idSalon->getAddress();
                    return $address;
                },
                'choice_attr' => function($idSalon) {

                    if ($idSalon->getGorod() == 'Москва'){
                        $class = 'metro';
                    }else{
                        $class = '';
                    }

                    return array('class' => $class);
                },
                'label' => false,
            ))
            ->getForm()->createView();

        return $formFreeProject;
    }

    private function getRequestCallForm()
    {
        $requestcall = new RequestCall();
        $formRequestCall = $this->createFormBuilder($requestcall)
            ->add('name', TextType::class, array('attr' => [
                'placeholder' => 'ВАШЕ ИМЯ *',
                'data-validation-required-message' => 'Укажите ваше Имя.',
                'class' => 'form-control'],
                'label' => false
            ))
            ->add('phone', NumberType::class, array(
                'attr' => [
                    'data-validation-required-message' => 'Укажите ваш телефон для связи.',
                    'class' => 'form-control',
                    'type' => 'tel',
                ],
                'label' => false,
            ))
            ->add('idSalon', EntityType::class, array(
                'class' => 'ApplicationSonataUserBundle:User',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('u');
                    return
                        $qb->where(
                            $qb->expr()->notLike('u.username', ':name')
                        )
                            ->orderBy('u.title', 'ASC')
                            ->setParameter('name', 'admin');
                },
                'attr' => [
                    'data-validation-required-message' => 'Укажите ближайший салон.',
                    'class' => 'form-control',
                ],
                'choice_label' => function ($idSalon) {
                    $address = '';
                    if (!empty($idSalon->getMetro())){
                        $address .= $idSalon->getMetro() . ' | ';
                    }else{
                        $address .= $idSalon->getGorod() . ' | ';
                    }
                    if (!empty($idSalon->getTc())){
                        $address .= $idSalon->getTc() . " ";
                    }else{
                        $address .= "Белорусские кухни ";
                    }
                    $address .= $idSalon->getAddress();
                    return $address;
                },
                'choice_attr' => function($idSalon) {

                    if ($idSalon->getGorod() == 'Москва'){
                        $class = 'metro';
                    }else{
                        $class = '';
                    }

                    return array('class' => $class);
                },
                'label' => false,
            ))
            ->getForm()->createView();

        return $formRequestCall;
    }

    private function getCallBackForm()
    {
        $callback = new CallBack();

        $form = $this->createFormBuilder($callback)
            ->add('name', TextType::class, array('attr' => ['placeholder' => 'ВАШЕ ИМЯ *', 'class' => 'form-control'], 'label' => false))
            ->add('email', EmailType::class, array('label' => false, 'attr' => ['placeholder' => 'Ваш EMAIL *', 'class' => 'form-control']))
            ->add('phone', NumberType::class, array(
                'attr' => [
                    'placeholder' => 'ВАШ ТЕЛЕФОН *',
                    'class' => 'form-control',
                    'type' => 'tel',
                ],
                'label' => false,
            ))
            ->add('message', TextareaType::class, array(
                'label' => false,
                'attr' => [
                    'placeholder' => 'Ваше Сообщение *',
                    'class' => 'form-control'
                ]
            ))
            ->add('idSalon', EntityType::class, array(
                'class' => 'ApplicationSonataUserBundle:User',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('u');
                    return
                        $qb->where(
                            $qb->expr()->notLike('u.username', ':name')
                        )
                            ->orderBy('u.title', 'ASC')
                            ->setParameter('name', 'admin');
                },
                'attr' => [
                    'data-validation-required-message' => 'Укажите ближайший салон.',
                    'class' => 'form-control',
                ],
                'choice_label' => function ($idSalon) {
                    $address = '';
                    if (!empty($idSalon->getMetro())){
                        $address .= $idSalon->getMetro() . ' | ';
                    }else{
                        $address .= $idSalon->getGorod() . ' | ';
                    }
                    if (!empty($idSalon->getTc())){
                        $address .= $idSalon->getTc() . " ";
                    }else{
                        $address .= "Белорусские кухни ";
                    }
                    $address .= $idSalon->getAddress();
                    return $address;
                },
                'choice_attr' => function($idSalon) {

                    if ($idSalon->getGorod() == 'Москва'){
                        $class = 'metro';
                    }else{
                        $class = '';
                    }

                    return array('class' => $class);
                },
                'label' => false,
            ))
            ->getForm()->createView();

        return $form;
    }

    private function getCostProject()
    {
        $costProject = new CostProject();

        $formCostProject = $this->createFormBuilder($costProject)
            ->add('name', TextType::class, array('attr' => [
                'placeholder' => 'ВАШЕ ИМЯ *',
                'class' => 'form-control'],
                'label' => false
            ))
            ->add('phone', NumberType::class, array(
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'tel',
                ],
                'label' => false,
            ))
            ->add('email', EmailType::class, array(
                'attr' => [
                    'placeholder' => 'Ваш EMAIL',
                    'class' => 'form-control',
                    'required' => false,
                ],
                'label' => false,
            ))
            ->add('message', TextType::class, array(
                'attr' => [
                    'placeholder' => 'ВАШЕ СООБЩЕНИЕ *',
                    'class' => 'form-control',
                ],
                'label' => false,
            ))
            ->add('idSalon', EntityType::class, array(
                'class' => 'ApplicationSonataUserBundle:User',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('u');
                    return
                        $qb->where(
                            $qb->expr()->notLike('u.username', ':name')
                        )
                            ->orderBy('u.title', 'ASC')
                            ->setParameter('name', 'admin');
                },
                'attr' => [
                    'data-validation-required-message' => 'Укажите ближайший салон.',
                    'class' => 'form-control',
                ],
                'choice_label' => function ($idSalon) {
                    $address = '';
                    if (!empty($idSalon->getMetro())){
                        $address .= $idSalon->getMetro() . ' | ';
                    }else{
                        $address .= $idSalon->getGorod() . ' | ';
                    }
                    if (!empty($idSalon->getTc())){
                        $address .= $idSalon->getTc() . " ";
                    }else{
                        $address .= "Белорусские кухни ";
                    }
                    $address .= $idSalon->getAddress();
                    return $address;
                },
                'choice_attr' => function($idSalon) {

                    if ($idSalon->getGorod() == 'Москва'){
                        $class = 'metro';
                    }else{
                        $class = '';
                    }

                    return array('class' => $class);
                },
                'label' => false,
            ))
            ->add('imageFile', VichImageType::class, array(
                'required'      => false,
                'allow_delete'  => true,
                'download_link' => false,
                'label'         => false,
            ))
            ->getForm()->createView();

        return $formCostProject;
    }
}