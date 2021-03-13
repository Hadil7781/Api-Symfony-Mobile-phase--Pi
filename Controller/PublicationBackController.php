<?php

namespace MobileApiBundle\Controller;

use AppBundle\Entity\User;
use EvenementBundle\Entity\Publication;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PublicationBackController extends Controller
{
    /*show pub valide*/
    public function afficheAction()
    {
        $publications=$this->getDoctrine()->getRepository(Publication::class)->findBy([
            'isValid' => true
        ]);
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($publications);
        return new JsonResponse($formatted);

    }

    // show all publications
    public function allAction(Request $request)
    {
         $publications = $this->getDoctrine()->getRepository(Publication::class)->findAll();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($publications);
        return new JsonResponse($formatted);
    }

    //show one pub
    public  function showonePublicationAction($id, Request $request ){
        $publication =$this->getDoctrine()->getRepository(Publication::class)->find($id);

        $res = array(
            'id' =>$publication->getId(),
            'user' =>$publication->getUser() ? $publication->getUser()->getUsername(): '' ,

        );

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($res);
        return new JsonResponse($formatted);
    }
    public function validateAction($id, Request $request)
    {
        $em    = $this->get('doctrine.orm.entity_manager');
        $pub =$this->getDoctrine()->getRepository(Publication::class)->find($id);

        if ($pub instanceof  Publication) {
            $pub->setIsValid(true);

            try {
                $em->persist($pub);
                $em->flush();
                return new JsonResponse("success");
            } catch (\Exception $ex) {
                return new JsonResponse("fail");
            }
        }else{
            return new JsonResponse("Not found");

        }
    }
   public function blockAction($id, Request $request)
   {
       $em = $this->get('doctrine.orm.entity_manager');
       $pub = $this->getDoctrine()->getRepository(Publication::class)->find($id);

       if ($pub instanceof  Publication) {
           $pub->setIsBlocked(true);

           try {
               $em->persist($pub);
               $em->flush();
               return new JsonResponse("success");
           } catch (\Exception $ex) {
               return new JsonResponse("fail");
           }
       }else{
           return new JsonResponse("Not found");

       }
   }




}
