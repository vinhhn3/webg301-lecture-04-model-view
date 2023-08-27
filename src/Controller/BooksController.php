<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksController extends AbstractController
{
    /**
     * @Route("/books", name="book_list")
     */
    public function index(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll();

        return $this->render('books/index.html.twig', [
            'books' => $books,
        ]);
    }

    /**
     * @Route("/books/{id}", name="view_book")
     */
    public function viewBook($id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        return $this->render('books/view.html.twig', [
            'book' => $book,
        ]);
    }

    /**
     * @Route("/books/{id}/delete", name="delete_book")
     */
    public function deleteBook($id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_list');
    }

    /**
     * @Route("/books/add", name="add_book")
     */
    public function addBook(Request $request): Response
    {
        $newBook = new Book();

        $form = $this->createForm(BookType::class, $newBook);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newBook);
            $entityManager->flush();

            return $this->redirectToRoute('book_list');
        }

        return $this->render('books/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/books/{id}/edit", name="edit_book")
     */
    public function editBook($id, BookRepository $bookRepository, Request $request): Response
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('book_list');
        }

        return $this->render('books/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/search/books", name="search_books")
     */
    public function searchBooks(Request $request, BookRepository $bookRepository): Response
    {
        $price = $request->query->get('price', 0);

        $books = $bookRepository->findBooksWithPriceGreaterThan($price);

        return $this->render('books/index.html.twig', [
            'books' => $books,
        ]);
    }
}
