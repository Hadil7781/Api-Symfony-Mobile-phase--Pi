<?php

namespace MobileApiBundle\Controller;

use EvenementBundle\Entity\Evenement;

use EvenementBundle\Entity\Publication;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class EventController extends Controller
{
    public function listAction()
    {
        $evenements = $this->getDoctrine()->getRepository(Evenement::class)->findAll();
        $res = array();

        foreach ($evenements as $evenement) {
             $res[] = array(
                'id' => $evenement->getId(),
                'titre' => $evenement->getTitle(),
                'nombreMinparticipants'=> $evenement->getNombreMinparticipants(),
                'nombreMaxparticipants'=> $evenement->getNombreMaxparticipants(),
                'description'=>  $evenement->getDescription(),
                'dateDebut' => ( $evenement->getDateDebut()->format('Y-m-d ')) ,
                'dateFin' => ( $evenement->getDateFin()->format('Y-m-d ')),
                'prix' => $evenement->getPrix(),
                'localisation' => $evenement->getLocalisation(),
                'isPublic' => (bool)$evenement->isPublic(),
                'categories' => $evenement->getCategories(),
                'imagePath'=>  $evenement->getImagepath(),


            );

        }
        return new JsonResponse($res);

    }



         /*
        * @Method("POST")
        */
    public function addAction(Request $request)
    {
        $evenement = new Evenement();
        $evenement->setTitle($request->get('title'));
        $evenement->setDescription($request->get('description'));
        $evenement->setLocalisation($request->get('localisation'));
        $evenement->setEtablissement($request->get('etablissement'));
        $evenement->setCategories($request->get('categories'));
        try {
            $evenement->setDateDebut(new \DateTime($request->get('dateDebut')));
            $evenement->setDateFin(new \DateTime($request->get('dateFin')));

        } catch (\Exception $e) {
        }
        $evenement->setNombreMinparticipants($request->get('nombreMinparticipants'));
        $evenement->setIsPublic($request->get('isPublic'));
        $evenement->setPrix($request->get('prix'));
        $evenement->setIsPayed($request->get('isPayed'));
        $evenement->setRating($request->get('rating'));
        $evenement->setNbActuel($request->get('nbActuel'));
        $evenement->setNombreMaxparticipants($request->get('NombreMaxparticipants'));

        if ($request->files->get("file") != null) {
            $file = $request->files->get("file") ;
            $fileName = $file->getClientOriginalName();
            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('evenemnts_image_directory'),
                $fileName
            );
            $evenement->setImagepath($fileName);
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($evenement);
            $em->flush();
             return new JsonResponse('success');
        } catch (\Exception $e) {
            return new JsonResponse('failed' . $e->getMessage());
        }

    }

    /*
       * @Method("POST")
       */
    public function updateAction( Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $evenement = $em->getRepository(Evenement::class)->find($request->get("id"));
        $publications = $em->getRepository(Publication::class)->findBy([
            'evenement' => $evenement
        ]);

        if ($publications != null ){
            return new JsonResponse(array('info' => 'Ecannot update evnet '));

        }
        $evenement->setTitle($request->get('title'));
        $evenement->setDescription($request->get('description'));
        $evenement->setLocalisation($request->get('localisation'));
        $evenement->setEtablissement($request->get('etablissement'));
        $evenement->setCategories($request->get('categories'));
        $evenement->setDateDebut(new \DateTime($request->get('dateDebut')));
        $evenement->setDateFin(new \DateTime($request->get('dateFin')));
        $evenement->setNombreMinparticipants($request->get('nombreMinparticipants'));
        $evenement->setIsPublic($request->get('isPublic'));
        $evenement->setPrix($request->get('prix'));
        $evenement->setIsPayed($request->get('isPayed'));
        $evenement->setRating($request->get('rating'));
        $evenement->setNbActuel($request->get('nbActuel'));
        $evenement->setNombreMaxparticipants($request->get('NombreMaxparticipants'));
        if ($request->files->get("file") != null) {
            $file = $request->files->get("file");
            $fileName = $file->getClientOriginalName();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('evenemnts_image_directory'),
                $fileName
            );
            $evenement->setImagepath($fileName);

        }
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($evenement);
            $em->flush();
            $manager = $this->get('assets.packages');

            $data = array(
                'id' => $evenement->getId(),
               ' titre' => $evenement->getTitle(),
                'nombreMinparticipants'=> $evenement->getNombreMinparticipants(),
                'nombreMaxparticipants'=> $evenement->getNombreMaxparticipants(),
                'dateDebut' => "" . strtotime( $evenement->getDateDebut()->format('Y-m-d H:i:s')) ."",
                'dateFin' => "" . strtotime( $evenement->getDateFin()->format('Y-m-d H:i:s')) ."",
                'prix' => $evenement->getPrix(),
                'localisation' => $evenement->getLocalisation(),
                'isPublic' => (bool)$evenement->isPublic(),
                'categories' => $evenement->getCategories(),
                'imagePath'=>    $manager->getUrl('uploads/images/events/'.$evenement->getImagepath()),



            );
            return new JsonResponse(array('info' => 'success','data' => $data ));
        } catch (\Exception $e) {
            return new JsonResponse(array('info' => 'error'));
        }
    }
    public function getEventsJsonAction()
    {
        $evenements = $this->getDoctrine()->getRepository(Evenement::class)->findAll();

        $resultat = array();

        foreach ($evenements as $event) {
            $today = new \DateTime();
            $res = array(
                'id' => $event->getId(),
                'start' => $event->getDateDebut()->format('Y-m-d'),
                'end' => $event->getDateFin()->format('Y-m-d'),
                'title' => $event->getTitle(),
                'color'=> $event->getDateFin() > $today ? '#0000FF' : '#FF0000'
            );
            $resultat[] = $res;
        }

        return new JsonResponse(($resultat));

    }
    public function searchAction(Request $request)
    {
        $q = $request->get('query');
        $evenements = $this->getDoctrine()->getRepository(Evenement::class)->search($q);
        $res = array();
        foreach ($evenements as $evenement) {
            $item = array();
            $item['id'] = $evenement->getId();
            $item['titre'] = $evenement->getTitle();
            $item['dateFin'] = $evenement->getDateFin()->format('Y-m-d H:i:s');
            $item['isPublic'] = (bool)$evenement->isPublic();
            $item['categories'] = $evenement->getCategories();

            $res[] = $item;
        }
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($res);
        return new JsonResponse($formatted);

    }
    public function supprimerAction (Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $evenement  = $em->getRepository(Evenement::class)->find($request->get("id"));
        if(!$evenement instanceof Evenement){
            return new JsonResponse(array('info' => 'Not found '));

        }
        $publications = $em->getRepository(Publication::class)->findBy([
            'evenement' => $evenement
        ]);

        if ($publications != null ){
            return new JsonResponse(array('info' => 'Ecannot delete evnet '));

        }


        $em->remove($evenement);
        $em->flush();
        return new JsonResponse(array('info' => 'success '));


    }
    public function ShowEventByCatAction(Request $request)
    {
        $evenements=$this->getDoctrine()->getRepository(Evenement::class)->getEventByCategory($request->get('categorie'));
        $res = array();
        $manager = $this->get('assets.packages');

        foreach ($evenements as $evenement) {
            $res[] = array(
                'id' => $evenement->getId(),
                'titre' => $evenement->getTitle(),
                'nombreMinparticipants'=> $evenement->getNombreMinparticipants(),
                'nombreMaxparticipants'=> $evenement->getNombreMaxparticipants(),
                'dateDebut' => "" . strtotime( $evenement->getDateDebut()->format('Y-m-d H:i:s')) ."",
                'dateFin' => "" . strtotime( $evenement->getDateFin()->format('Y-m-d H:i:s')) ."",
                'prix' => $evenement->getPrix(),
                'localisation' => $evenement->getLocalisation(),
                'isPublic' => (bool)$evenement->isPublic(),
                'categories' => $evenement->getCategories(),
                'imagePath'=>    $manager->getUrl('uploads/images/events/'.$evenement->getImagepath()),


            );

        }
        return new JsonResponse(array('data' => $res));

    }
    public function ShowOneEventAction(Request $request)
    {
        $evenement=$this->getDoctrine()->getRepository(Evenement::class)->find($request->get('id'));
        $res = array();
        $res = array(
                'id' => $evenement->getId(),
                'titre' => $evenement->getTitle(),
                'nombreMinparticipants'=> $evenement->getNombreMinparticipants(),
                'nombreMaxparticipants'=> $evenement->getNombreMaxparticipants(),
                'dateDebut' => $evenement->getDateDebut()->format('Y-m-d H:i:s'),
                'dateFin' =>  $evenement->getDateFin()->format('Y-m-d H:i:s'),
                'prix' => $evenement->getPrix(),
                'localisation' => $evenement->getLocalisation(),
                'isPublic' => (bool)$evenement->isPublic(),
                'categories' => $evenement->getCategories(),
                'imagePath'=>    $evenement->getImagePath(),


            );

        return new JsonResponse( $res);

    }
    public function imageAction(Request $request) {
        $publicResourcesFolderPath = $this->get('kernel')->getRootDir() . '/../web/uploads/images/events/';
          $image = urldecode($request->get('imagePath'));

        return new BinaryFileResponse($publicResourcesFolderPath.$image);
    }


}
