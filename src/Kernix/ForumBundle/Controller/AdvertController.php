<?php

namespace Kernix\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Kernix\ForumBundle\Entity\Advert;
use Kernix\ForumBundle\Entity\Image;
use Kernix\ForumBundle\Entity\Message;
use Kernix\ForumBundle\Entity\Skill;
use Kernix\ForumBundle\Entity\Category;

class AdvertController extends Controller
{
    public function indexAction()
    {
      $listAdverts = array(
      array(
        'title'   => 'Recherche développpeur Symfony2',
        'id'      => 1,
        'author'  => 'Alexandre',
        'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
        'date'    => new \Datetime()),
      array(
        'title'   => 'Mission de webmaster',
        'id'      => 2,
        'author'  => 'Hugo',
        'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
        'date'    => new \Datetime()),
      array(
        'title'   => 'Offre de stage webdesigner',
        'id'      => 3,
        'author'  => 'Mathieu',
        'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
        'date'    => new \Datetime())
    );

    // Et modifiez le 2nd argument pour injecter notre liste
    return $this->get('templating')->renderResponse('KernixForumBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts
    ));
    }

    public function viewAction($id)
    {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em
      ->getRepository('KernixForumBundle:Advert')
      ->find($id)
    ;

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On récupère la liste des candidatures de cette annonce
    $listMessages = $em
      ->getRepository('KernixForumBundle:Message')
      ->findBy(array('advert' => $advert))
    ;

    return $this->render('KernixForumBundle:Advert:view.html.twig', array(
      'advert'           => $advert,
      'listMessages' => $listMessages
    ));
  }
    
  public function addAction(Request $request)
  {
    // Création de l'entité
    $advert = new Advert();
    $advert->setTitle('Recherche développeur Symfony2.');
    $advert->setAuthor('Alexandre');
    $advert->setContent("Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…");
    // On peut ne pas définir ni la date ni la publication,
    // car ces attributs sont définis automatiquement dans le constructeur

    // Création d'une première candidature
    $message1 = new Message();
    $message1->setAuthor('Marine');
    $message1->setContent("J'ai toutes les qualités requises.");

    // Création d'une deuxième candidature par exemple
    $message2 = new Message();
    $message2->setAuthor('Pierre');
    $message2->setContent("Je suis très motivé.");

    // On lie les candidatures à l'annonce
    $message1->setAdvert($advert);
    $message2->setAdvert($advert);


    $image= new Image();
    $image->setUrl('http://www.ac-grenoble.fr/ien.vienne1-2/spip/IMG/bmp_Image004.bmp');
    $image->setAlt('Meilleur topic !');

    $advert->setImage($image);

    // On récupère l'EntityManager
    $em = $this->getDoctrine()->getManager();

    // Étape 1 : On « persiste » l'entité
    $em->persist($advert);

    $em<>persist($message1);
    $em<>persist($message2);

    // Étape 2 : On « flush » tout ce qui a été persisté avant
    $em->flush();

    // Reste de la méthode qu'on avait déjà écrit
    if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
      return $this->redirect($this->generateUrl('kernix_forum_view', array('id' => $advert->getId())));
    }

    return $this->render('KernixForumBundle:Advert:add.html.twig');
  }

   public function deleteAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('KernixForumBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On boucle sur les catégories de l'annonce pour les supprimer
    foreach ($advert->getCategories() as $category) {
      $advert->removeCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // On déclenche la modification
    $em->flush();
  }

    public function menuAction()
  {
    // On fixe en dur une liste ici, bien entendu par la suite
    // on la récupérera depuis la BDD !
    $listAdverts = array(
      array('id' => 2, 'title' => 'Recherche développeur Symfony2'),
      array('id' => 5, 'title' => 'Mission de webmaster'),
      array('id' => 9, 'title' => 'Offre de stage webdesigner')
    );

    return $this->render('KernixForumBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe
      // les variables nécessaires au template !
      'listAdverts' => $listAdverts
    ));
  }

  public function editAction($id, Request $request)
  { 
$em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('KernixForumBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // La méthode findAll retourne toutes les catégories de la base de données
    $listCategories = $em->getRepository('KernixForumBundle:Category')->findAll();

    // On boucle sur les catégories pour les lier à l'annonce
    foreach ($listCategories as $category) {
      $advert->addCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // Étape 2 : On déclenche l'enregistrement
    $em->flush();

    $advert = array(
      'title'   => 'Recherche développpeur Symfony2',
      'id'      => $id,
      'author'  => 'Alexandre',
      'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
      'date'    => new \Datetime()
    );

    return $this->render('KernixForumBundle:Advert:edit.html.twig', array('advert' => $advert)
    );
  }

}