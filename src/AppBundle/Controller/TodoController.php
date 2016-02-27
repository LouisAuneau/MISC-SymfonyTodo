<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;

class TodoController extends Controller
{
    /**
     * @Route("/", name="todo_home")
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
     * @Route("/tache/list", name="todo_task_list")
     * @param Request $request Request object automatically passed when action is called.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function taskListAction(Request $request){
        // Retrieving Entity Manager.
        $em = $this->getDoctrine()->getManager();

        // Retrieving user and getting his tasks that aren't done.
        $user = $this->getUser();
        $onGoingTasks = $em->getRepository('AppBundle:Task')->getOnGoingTasks($user);
        $missedTasks = $em->getRepository('AppBundle:Task')->getMissedTasks($user);

        // Displaying all tasks.
        $viewParams = ["onGoingTasks" => $onGoingTasks, "missedTasks" => $missedTasks];
        return $this->render('AppBundle::task_list.html.twig', $viewParams);
    }


    /**
     * @Route("/tache/ajout", name="todo_add_task")
     * @param Request $request Request object automatically passed when action is called.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addTaskAction(Request $request){
        // Generate 10 years from actual year to put in year field of the form.
        $currentYear = (new \DateTime())->format('Y');
        $currentDate = new \DateTime();
        $years = range($currentYear, $currentYear + 10);

        // Generating the form
        $task = new Task();
        $task->setUser($this->getUser());
        $formBuilder = $this->get("form.factory")->createBuilder("form", $task);
        $formBuilder
            ->add("title", "text")
            ->add("endDate", "date",
                [
                    "years" => $years,
                    "data" => $currentDate
                ])
            ->add("description", "textarea")
            ->add("save", "submit")
        ;

        // Getting build form
        $form = $formBuilder->getForm();

        // Handling request
        $form->handleRequest($request);
        if($form->isValid()){
            // Retrieving Entity Manager.
            $em = $this->getDoctrine()->getManager();

            // Hydrate object
            $em->persist($task);
            $em->flush();

            // Returning to task list.
            return $this->redirect($this->generateUrl("todo_task_list"));
        }

        $formView = $form->createView();
        $viewParams = ["form" => $formView];
        return $this->render("AppBundle::add_task.html.twig", $viewParams);
    }


    /**
     * @Route("/tache/{id}", name="todo_see_task")
     * @param Request $request Request object automatically passed when action is called.
     * @param $id Id of the task that is displayed. If it doesn't exist, the page is redirected.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function viewTaskAction(Request $request, $id){
        // Retrieving Entity Manager and current task
        $em = $this->getDoctrine()->getManager();
        $currentTask = $em->getRepository("AppBundle:Task")->find($id);

        // Displaying task and managing errors
        if(is_null($currentTask))
            return $this->redirect($this->generateUrl("todo_task_list"));

        $viewParams = ["task" => $currentTask];
        return $this->render("AppBundle::task.html.twig", $viewParams);
    }


    /**
     * @Route("/tache/{id}/fait", name="todo_done_task")
     * @param Request $request Request object automatically passed when action is called.
     * @param $id Id of the task that will be done. If it doesn't exist, the page is redirected.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function doneTaskAction(Request $request, $id){
        // Retrieving Entity Manager and asked task
        $em = $this->getDoctrine()->getManager();
        $currentTask = $em->getRepository("AppBundle:Task")->find($id);

        // Displaying task and managing errors
        if(is_null($currentTask))
            return $this->redirect($this->generateUrl("todo_task_list"));

        // Set task to done
        $currentTask->setDone(true);
        $em->flush();

        return $this->redirect($this->generateUrl("todo_task_list"));
    }


    /**
     * @Route("/tache/{id}/supprimer", name="todo_remove_task")
     * @param Request $request Request object automatically passed when action is called.
     * @param $id Id of the task that will be deleted. If it doesn't exist, the page is redirected.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function  removeTaskAction(Request $request, $id){
        // Retrieving Entity Manager and asked task.
        $em = $this->getDoctrine()->getManager();
        $currentTask = $em->getRepository("AppBundle:Task")->find($id);

        // Managing errors if task doesn't exist.
        if(is_null($currentTask))
            return $this->redirect($this->generateUrl("todo_task_list"));

        // Delete the task.
        $em->remove($currentTask);
        $em->flush();

        return $this->redirect($this->generateUrl("todo_task_list"));
    }
}
