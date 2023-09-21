# Lecture 04: Models and Views

Now, we are going to learn Models and Views by building a BookStore application.

The application can help to:

- Create a new book
- Get all books
- Update the book by id
- Delete the book by id

Below is the table design of the Books table

![Alt text](/images/image.png)

Small recap about MVC model

![Alt text](images/image-1.png)

## Initialization the Project

First of all, we need to initialize the Project

```bash
symfony new --webapp webg301-lecture-04-model-view
```

Then, go the the project folder and start the Visual Studio Code

```bash
cd webg301-lecture-04-model-view
code .
```

## Models

Now, we are going to create the `Book` Model (or Entity) by using the command line and follow the steps below

```bash
php bin/console make:entity
```

![Alt text](images/image-2.png)

Voila, you can see the `Book.php` entity is created with the `BookRepository.php`

![Alt text](images/image-3.png)

## Connect to Database

After we created the Model (Entity), we will connect to the database.

First of all, make sure you enable XAMPP

![Alt text](images/image-4.png)

Then, you can navigate to the URL `http://localhost/phpmyadmin/` to see the `phpMyAdmin` to manage the database

![Alt text](images/image-5.png)

Next, we need to config the `DATABASE_URL` in the `.env` file

```bash
# .env
DATABASE_URL=mysql://root:@127.0.0.1:3306/book_store?serverVersion=mariadb-10.4.11
```

Then, we can use command line to create the database

```bash
php bin/console doctrine:database:create
```

![Alt text](images/image-6.png)

In the `phpMyAdmin`, you can see the database `book_store` is created.

![Alt text](images/image-7.png)

## Create the table with migration

Now, the database is created. We need to create the table from the Model.

To do so, we need to create a migration first.

```bash
php bin/console make:migration
```

![Alt text](images/image-8.png)

You can see the folder `migrations` is created with the migration file inside it.

![Alt text](images/image-9.png)

Now, use the command below to start create the table

```bash
php bin/console doctrine:migrations:migrate
```

![Alt text](images/image-10.png)

Now, the table `book` is created in the `phpMyAdmin`.

![Alt text](images/image-11.png)

## Create the `BookController`

Now, it's time to create the controller. Here the command to create it.

```bash
php bin/console make:controller BooksController
```

Then, open the `BooksController.php` to modify the source. The source code will help to get all books in the database

```php
// /src/Controller/BooksController.php
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
}
```

Now, modify the view to display all the books from database.

To do that, modify the file `/src/templates/books/index.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Book List</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
        </tr>
        {% for book in books %}
            <tr>
                <td>{{ book.id }}</td>
                <td>{{ book.name }}</td>
                <td>{{ book.price }}</td>
            </tr>
        {% endfor %}
    </table>
{% endblock %}
```

Now, start the project to see the result

```bash
symfony serve
```

You can navigate to the URL `http://127.0.0.1:8000/books` to see all the books.

At first, it is empty because there is nothing in the table. You need to insert some data into the table.

Use the SQL script below to insert data into the table in `phpMyAdmin`

```sql
USE book_store;

-- Insert dummy data into the book table
INSERT INTO book (name, price) VALUES
    ('Book 1', 20),
    ('Book 2', 30),
    ('Book 3', 25),
    ('Book 4', 15),
    ('Book 5', 40);
```

![Alt text](images/image-13.png)

Now, refresh the website to see all books

![Alt text](images/image-14.png)

## Create a new book with Form Builder

Now, we will create a new book.

To do that, we need to create a form. In Symfony, we will use form builder.

Open your terminal and run the following command to generate a form type:

```bash
php bin/console make:form BookType
```

![Alt text](images/image-15.png)

Follow the prompts and generate the form type. This will create a file named `BookType.php` in the `src/Form` directory.

### Create the action in the Controller for adding new book

In the `BookController.php`, add a new action named `addBook()`:

```php
use App\Form\BookType;
use Symfony\Component\HttpFoundation\Request;

// ...

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

    return $this->render('book/add.html.twig', [
        'form' => $form->createView(),
    ]);
}
```

The image below explains the code above

![Alt text](image.png)

### Create the View for Adding a New Book

Create a new Twig template named `add.html.twig` inside the `templates/books` directory:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Add a New Book</h1>
    {{ form_start(form) }}
    {{ form_row(form.name) }}
    {{ form_row(form.price) }}
    <button type="submit">Add Book</button>
    {{ form_end(form) }}
{% endblock %}
```

Now, navigate to `http://127.0.0.1:8000/books/add` to see the form.

![Alt text](images/image-16.png)

## Get book details.

Let's create an action to display the details of a specific book, update the book list view to include a link to view details, and then create the view to display the book details.

### Create the Action for Book Details

In the `BookController.php`, add a new action named `viewBook()`

```php
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
```

### Update the Book List View

Open the `templates/books/index.html.twig` file and update it to include a link for each book to view its details:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Book List</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        {% for book in books %}
            <tr>
                <td>{{ book.id }}</td>
                <td>{{ book.name }}</td>
                <td>{{ book.price }}</td>
                <td><a href="{{ path('view_book', {'id': book.id}) }}">View Details</a></td>
            </tr>
        {% endfor %}
    </table>
{% endblock %}
```

Now, we can see the link `View Details`

![Alt text](images/image-17.png)

### Create the View for Book Details

Create a new Twig template named `view.html.twig` inside the `templates/books` directory:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Book Details</h1>
    <p><strong>Name:</strong> {{ book.name }}</p>
    <p><strong>Price:</strong> {{ book.price }}</p>
    <a href="{{ path('book_list') }}">Back to Book List</a>
{% endblock %}
```

Now, click the `View Details`, we can see the details page

![Alt text](images/image-18.png)

## Delete a book

### Create the Action for Deleting a Book

In the `BookController.php`, add a new action named `deleteBook()`:

```php
use App\Repository\BookRepository;

// ...

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
```

## Update the Book list view

Open the `templates/books/index.html.twig` file and update it to include a delete button for each book:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Book List</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        {% for book in books %}
            <tr>
                <td>{{ book.id }}</td>
                <td>{{ book.name }}</td>
                <td>{{ book.price }}</td>
                <td>
                    <a href="{{ path('view_book', {'id': book.id}) }}">View Details</a>
                    <a href="{{ path('delete_book', {'id': book.id}) }}" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                </td>
            </tr>
        {% endfor %}
    </table>
{% endblock %}
```

Now, we can use the `Delete` button to delete a book

![Alt text](images/image-19.png)

## Update the book

Let's create an action to update a book and update the book list view to include an `Edit` button for each book.

### Create the Action for Updating a Book

In the `BookController.php`, add a new action named `editBook()`:

```php
use Symfony\Component\Routing\Annotation\Route;

// ...

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
```

The image below explains the business of updating a book

![Alt text](image-1.png)

### Update the Book List View

Open the `templates/books/index.html.twig` file and update it to include an `Edit` button for each book:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Book List</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        {% for book in books %}
            <tr>
                <td>{{ book.id }}</td>
                <td>{{ book.name }}</td>
                <td>{{ book.price }}</td>
                <td>
                    <a href="{{ path('view_book', {'id': book.id}) }}">View Details</a>
                    <a href="{{ path('edit_book', {'id': book.id}) }}">Edit</a>
                    <a href="{{ path('delete_book', {'id': book.id}) }}" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                </td>
            </tr>
        {% endfor %}
    </table>
{% endblock %}

```

Now, you can see the `Edit` button

![Alt text](images/image-20.png)

### Create the View for Editing a Book

Create a new Twig template named `edit.html.twig` inside the `templates/books` directory:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Edit Book</h1>
    {{ form_start(form) }}
    {{ form_row(form.name) }}
    {{ form_row(form.price) }}
    <button type="submit">Update Book</button>
    {{ form_end(form) }}
    <a href="{{ path('book_list') }}">Back to Book List</a>
{% endblock %}
```

Now, we can have the form to update the book

![Alt text](images/image-21.png)

![Alt text](images/image-22.png)
