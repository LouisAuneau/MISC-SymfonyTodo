<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class TodoController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request Request object automatically passed when action is called.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        // If user is logged, we redirect him in his task list.
        if($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            return $this->redirect($this->generateUrl("todo_task_list"));
        }

        // Rendering home view.
        return $this->render('AppBundle::index.html.twig');
    }

    /**
     * @Route("/liste-des-taches", name="homepage")
     * @param Request $request Request object automatically passed when action is called.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function taskListAction(Request $request){
        // If user is not logged, we redirect him to the homepage.
        if(!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            return $this->redirect($this->generateUrl("todo_home"));
        }

        return $this->render('AppBundle::task_list.html.twig');
    }

}
