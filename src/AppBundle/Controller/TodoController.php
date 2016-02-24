<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/tache/list", name="homepage")
     * @param Request $request Request object automatically passed when action is called.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function taskListAction(Request $request){
        // Retrieving Entity Manager
        $em = $this->getDoctrine()->getManager();

        // Retrieving user and getting his tasks
        $user = $this->getUser();
        $tasks = $user->getTasks();

        $viewParams = ["tasks" => $tasks];
        return $this->render('AppBundle::task_list.html.twig', $viewParams);
    }

    /**
     * @param Request $request Request object automatically passed when action is called.
     * @param $id Id of the task that is displayed. If it doesn't exist, the page is redirected.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function viewTaskAction(Request $request, $id){
        // If user is not logged, we redirect him to the homepage.
        if(!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return $this->redirect($this->generateUrl("todo_home"));

        // Retrieving Entity Manager and current task
        $em = $this->getDoctrine()->getManager();
        $currentTask = $em->getRepository("AppBundle:Task")->find($id);

        // Displaying task and managing errors
        if(is_null($currentTask))
            return $this->redirect($this->generateUrl("todo_task_list"));

        $viewParams = ["task" => $currentTask];
        return $this->render("AppBundle::task.html.twig", $viewParams);
    }
}
