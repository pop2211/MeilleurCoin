<?php

namespace SiteBundle\Controller;

use DateTime;
use SiteBundle\Entity\Ad;
use SiteBundle\Entity\Picture;
use SiteBundle\Form\AdSearchType;
use SiteBundle\Form\AdType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
            $em = $this->getDoctrine()->getManager();

            $ad= new Ad();
            $form = $this->createForm(AdType::class, $ad);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $ad->setDateCreated(new DateTime());
                $ad->setUser($this->getUser());

                $all_picture=[];
                $i=1;
                foreach($ad->getPictures() as $file)
                {
                    $fileName = $this->generateUniqueFileName().'_'. $i . '.' . $file->guessExtension();
                    $file->move(
                        $this->getParameter('image_directory'), $fileName
                    );
                    $picture = new Picture();
                    $picture->setFile($fileName);
                    $picture->setAd($ad);
                    array_push($all_picture,$picture);
                    $i++;
                }
                $ad->setPictures($all_picture);

                $em->persist($ad);
                $em->flush();

                $this->addFlash(
                    'notice',
                    'Annonce enregistrée'
                );

                return $this->redirect($request->getUri());
            }
            return $this->render("@Site\Ad\add.html.twig", array(
                'form'=> $form->createView(),
                'title'=>isset($ad)?'Test de formulaire pour'.$ad->getTitle() : 'Test de formulaire',
            ));
    }

    public function listAction(Request $request)
    {
        $adRepo = $this->getDoctrine()->getManager()->getRepository(Ad::class);
        $all_ad = $adRepo->findAll();

        $ad = new ad();
        $form = $this->createForm(AdSearchType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $all_ad = $adRepo->getAdByParam(array('ad.title' => $ad->getTitle(), 'cat.id' => $ad->getCategory()));
        }

        $args = array(
            'all_ad' => $all_ad,
            'form'=>$form->createView()
        );
        return $this->render('@Site/Ad/list.html.twig', $args);
    }
    
    public function detailAction(Ad $ad){
        return $this->render('@Site/Ad/detailAd.html.twig', array('ad' => $ad));
    }
   
    public function myadsAction()
    {
            $user  =  $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $myAads = $em->getRepository(Ad::class)->findBy(array('user' => $user));

            $args = array('myads' => $myAads);
            return $this->render('@Site/Ad/myads.html.twig', $args);
    }

    public function favorisAction()
    {
        $user  =  $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $myFav = $em->getRepository(Ad::class)->getFav($user);

        $args = array('myfavoris' => $myFav);
        return $this->render('@Site/Ad/favorisAd.html.twig', $args);
    }

    public function addfavorisAction(Request $request, Ad $id)
    {
        $em = $this->getDoctrine()->getManager();
        $ad = $em->getRepository(Ad::class)->find($id);
        $ad->setFavoris(array($this->getUser()));
        $em->flush();

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }
    
    public function delfavorisAction(Request $request,  $id)
    {
        $em = $this->getDoctrine()->getManager();
        $ad = $em->getRepository(Ad::class)->find($id);
        $ad->getFavoris()->removeElement($this->getUser());
        $em->flush();

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }
    
    
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

}
