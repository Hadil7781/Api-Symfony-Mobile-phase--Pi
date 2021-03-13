<?php

namespace MobileApiBundle\Controller;

use AppBundle\Entity\User;
use EvenementBundle\Entity\Evenement;
use EvenementBundle\Entity\Publication;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PublicationController extends Controller
{
    // my publication
    public function allAction(Request $request)
    {
        $idUser = $request->get('userId');
        $user = $this->getDoctrine()->getRepository(User::class)->find($idUser);
        $publications = $this->getDoctrine()->getRepository(Publication::class)->findBy([
            'User' => $user
        ]);
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($publications);
        return new JsonResponse($formatted);
    }

    /*
     * @Method("POST")
     */
    public function addPublicationAction(Request $request)
    {

         $em = $this->getDoctrine()->getManager();
        $id_user = $request->get("iduser");
        $user = $this->getDoctrine()->getRepository(User::class)->find($id_user);
        $publication = new Publication();
        $publication->setTitre($request->get('titre'));
        $publication->setContenu($request->get('contenu'));
        $publication->setDescription($request->get('description'));
        $publication->setCategorie($request->get('categorie'));
        $publication->setCreatedadd(new \DateTime());
        $publication->setIsValid(0);
        $publication->setIsBlocked(0);
        $publication->setUser($user);


        if ($request->files->get("file") != null) {
            $file = $request->files->get("file");
            $fileName = $file->getClientOriginalName();
            switch ($publication->getContenu()) {
                case 'image':
                    $dir = $this->getParameter('publication_image_directory');
                    break;
                case 'video':
                    $dir = $this->getParameter('publication_video_directory');
                    break;
            }

            // moves the file to the directory where brochures are stored
            $file->move(
                $dir,
                $fileName
            );
            $publication->setFile($fileName);

        }


        try {
            $em->persist($publication);
            $em->flush();
            return new JsonResponse("success");

        } catch (\Exception $ex) {
            return new JsonResponse("fail");
        }
    }
    /*
         * @Method("POST")
         */
    public function updatePublicationAction($id,Request $request)
    {

        $em=$this->getDoctrine()->getManager();
        $publication = $em->getRepository(Publication::class)->find($id);
         $id_user = $request->get("iduser");
        $user = $this->getDoctrine()->getRepository(User::class)->find($id_user);
        $publication->setTitre($request->get('titre'));
        $publication->setContenu($request->get('contenu'));
        $publication->setDescription($request->get('description'));
        $publication->setCategorie($request->get('categorie'));
        $publication->setCreatedadd(new \DateTime());
        $publication->setIsValid(0);
        $publication->setIsBlocked(0);
        $publication->setUser($user);


        if ($request->files->get("file") != null) {
            $file = $request->files->get("file");
            $fileName = $file->getClientOriginalName();
            switch ($publication->getContenu()) {
                case 'image':
                    $dir = $this->getParameter('publication_image_directory');
                    break;
                case 'video':
                    $dir = $this->getParameter('publication_video_directory');
                    break;
            }

            // moves the file to the directory where brochures are stored
            $file->move(
                $dir,
                $fileName
            );
            $publication->setFile($fileName);

        }


        try {
            $em->persist($publication);
            $em->flush();
            return new JsonResponse("success");

        } catch (\Exception $ex) {
            return new JsonResponse("fail");
        }
    }

    public function supprimerAction (Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $publication = $em->getRepository(Publication::class)->find($request->get("id"));
        if (!$publication instanceof Publication) {
            return new JsonResponse(array('info' => 'Not found '));
        }
            $em->remove($publication);
            $em->flush();
            return new JsonResponse(array('info' => 'success '));


        }


       public function participerAction(Request $request){

        $idPublication = $request->get('idPublication');

        $idEvenement = $request->get('IdEvenement');


        $publication = $this->getDoctrine()->getRepository(Publication::class)->find($idPublication);
        $evenement = $this->getDoctrine()->getRepository(Evenement::class)->find($idEvenement);

        $publication->setEvenement($evenement);
        $evenement->setIsPublic(true);
           $em = $this->getDoctrine()->getManager();

           $em->persist($evenement);
           $em->persist($publication);
           $em->flush();

           return new JsonResponse(array('info' => 'success '));


       }



    }
