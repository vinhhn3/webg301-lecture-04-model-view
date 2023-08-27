# Lecture 04: Models and Views

Now, we are going to learn Models and Views by building a BookStore application.

The application can help to:

- Create a new book
- Get all books
- Update the book by id
- Delete the book by id

Below is the table design of the Books table

![Alt text](image.png)

Small recap about MVC model

![Alt text](image-1.png)

## Models

Now, we are going to create the `Book` Model (or Entity) by using the command line and follow the steps below

```bash
php bin/console make:entity
```

![Alt text](image-2.png)

Voila, you can see the `Book.php` entity is created with the `BookRepository.php`

![Alt text](image-3.png)

## Connect to Database

After we created the Model (Entity), we will connect to the database.

First of all, make sure you enable XAMPP

![Alt text](image-4.png)

Then, you can navigate to the URL `http://localhost/phpmyadmin/` to see the `phpMyAdmin` to manage the database

![Alt text](image-5.png)

Next, we need to config the `DATABASE_URL` in the `.env` file

```bash
# .env
DATABASE_URL=mysql://root:@127.0.0.1:3306/book_store?serverVersion=mariadb-10.4.11
```

Then, we can use command line to create the database

```bash
php bin/console doctrine:database:create
```

![Alt text](image-6.png)

In the `phpMyAdmin`, you can see the database `book_store` is created.

![Alt text](image-7.png)

## Create the table with migration

Now, the database is created. We need to create the table from the Model.

To do so, we need to create a migration first.

```bash
php bin/console make:migration
```

![Alt text](image-8.png)

You can see the folder `migrations` is created with the migration file inside it.

![Alt text](image-9.png)

Now, use the command below to start create the table

```bash
php bin/console doctrine:migrations:migrate
```

![Alt text](image-10.png)

Now, the table `book` is created in the `phpMyAdmin`.

![Alt text](image-11.png)

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

![Alt text](image-13.png)

Now, refresh the website to see all books

![Alt text](image-14.png)
