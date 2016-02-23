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
     * @Route("/liste-des-taches", name="homepage")
     * @param Request $request Request object automatically passed when action is called.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function taskListAction(Request $request){
        // If user is not logged, we redirect him to the homepage.
        if(!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return $this->redirect($this->generateUrl("todo_home"));

        // Retrieving Entity Manager
        $em = $this->getDoctrine()->getManager();

        // Creating a task
        $task = new Task();
        $task->setTitle("Tâche 1");
        $task->setEndDate(new \DateTime("now"));
        $task->setDescription("Il s'agit de la toute première tâche !");

        /*$em->persist($task);
        $em->flush();*/

        return $this->render('AppBundle::task_list.html.twig');
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
