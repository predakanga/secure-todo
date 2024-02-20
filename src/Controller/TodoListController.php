<?php

namespace App\Controller;

use App\Entity\TodoItem;
use App\Form\TodoItemType;
use App\Repository\TodoItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TodoListController extends AbstractController
{
    #[Route('/', name: 'app_todo_list')]
    public function index(TodoItemRepository $itemRepository): Response
    {
        return $this->render('todo_list/index.html.twig', [
            'items' => $itemRepository->findAll(),
            'controller_name' => 'TodoListController',
        ]);
    }

    #[Route('/new', name: 'app_todo_new')]
    public function newItem(Request $request, EntityManagerInterface $entityManager): Response
    {
        $item = new TodoItem();
        $form = $this->createForm(TodoItemType::class, $item)
            ->add('submit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();

            if ($notes = $item->getNotes()) {
                $notes = escapeshellarg($notes);
            	$item->setNotes(shell_exec("cowsay {$notes}"));
            }

            $entityManager->persist($item);
            $entityManager->flush();

            $this->addFlash('result', 'Item added successfully');
            return $this->forward('App\\Controller\\TodoListController::viewItem', ['item' => $item->getId()]);
        }

        return $this->render('todo_list/new.html.twig', [
            'controller_name' => 'TodoListController',
            'form' => $form,
        ]);
    }

    #[Route('/view/{item}', name: 'app_todo_view')]
    public function viewItem(TodoItem $item): Response {
        return $this->render('todo_list/view.html.twig', [
            'controller_name' => 'TodoListController',
            'item' => $item,
        ]);
    }
}
