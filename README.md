# Lecture 05: Repository

Now, we will use the Repository. Take a look at the architecture overview

![Alt text](/images/image.png)

With the repository, we can write custom query to perform variety of CRUD operations.

For example, we will create a search bar to search for books with price ranges.

Let's start by creating a function in the repository to find books with a price greater than the input price.

Then, we'll add a search bar to the `index.html.twig` view and create a controller to handle the search query using the `findBooksWithPriceGreaterThan()` function.

## Create the Function in the Repository

Open the `BookRepository.php` file located in the `src/Repository` directory.

Add the following function to the repository to find books with a price greater than the input price:

```php
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    // ...

    public function findBooksWithPriceGreaterThan($price): array
    {
        // This function is equivalent to the following SQL query
        // SELECT *
        // FROM books b
        // WHERE b.price > :price

        return $this->createQueryBuilder('b')
            ->andWhere('b.price > :price')
            ->setParameter('price', $price)
            ->getQuery()
            ->getResult();
    }
}
```

## Create a New Action for Handling the Search Query

Add the following code to the `BooksController.php` file:

```php
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BooksController extends AbstractController
{
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

```

## Add a Search Bar to the `index.html.twig` View

Open the `templates/books/index.html.twig` file and add a search bar above the book list table:

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Book List</h1>

    <form action="{{ path('search_books') }}" method="get">
        <label for="price">Search books with price greater than:</label>
        <input type="number" name="price" id="price" required>
        <button type="submit">Search</button>
    </form>

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

Now, you can see the search bar

![Alt text](/images/image-1.png)

Now, use the search bar to search for books with price greater than 15.

![Alt text](/images/image-2.png)

## Adding Category model.

Now, we will expend the application. We will create the Category model with the following design.

![Alt text](/images/image-3.png)

As you can see, `one` Category will belong to `many` books. So that's `one-to-many` relationship.

Now, we need to generate the Category entity with command line

```bash
php bin/console make:entity
```

![Alt text](/images/image-4.png)

![Alt text](/images/image-5.png)

![Alt text](/images/image-6.png)

Now, we need to perform the migration and update the database with following command line

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

![Alt text](/images/image-7.png)

Now, open the `phpMyAdmin`, we can see the new `Category` table and the new design

![Alt text](/images/image-8.png)

## Create SQL query to insert samples Category to the database

To insert sample Category data into your database using SQL, you can execute an SQL query like this:

```sql
INSERT INTO category (name)
VALUES
    ('Electronics'),
    ('Clothing'),
    ('Books'),
    ('Furniture');
```

![Alt text](/images/image-9.png)

## Modify the FormBuilder to include the Category

Open the `src/Form/BookType.php` file.

You'll need to add a field for selecting the Category. Assuming you have a relation between Book and Category called category, you can use the `EntityType` field type for this purpose.

```php
use App\Entity\Category; // Import the Category entity
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
// ...

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name', // Display category names in the dropdown
                'placeholder' => 'Select a category', // Optional: Add a placeholder
            ]);
    }
}
```

Open the form template for Book (located at `templates/book/add.html.twig`) and add the category field to the form.

```twig
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Add a New Book</h1>
    {{ form_start(form) }}
    {{ form_row(form.name) }}
    {{ form_row(form.price) }}
    {{ form_row(form.category) }} {# Add this line to display the category dropdown #}
    <button type="submit">Add Book</button>
    {{ form_end(form) }}
{% endblock %}
```

Now, navigate to `http://127.0.0.1:8000/books/add`, you can see the Category field

![Alt text](/images/image-10.png)

## Update the BooksController and the index view to display all books with the category name

Open the `BooksController.php` file.

In the `index` action, fetch books with their associated categories using a custom query. You'll need to join the Category entity to the Book entity.

```php
/**
 * @Route("/books", name="book_list")
 */
public function index(BookRepository $bookRepository): Response
{
    $books = $bookRepository->findAllWithCategory(); // <- change this line

    return $this->render('books/index.html.twig', [
        'books' => $books,
    ]);
}
```

Create a custom repository function in your `BookRepository` to fetch books with their associated categories.

```php
class BookRepository extends ServiceEntityRepository
{
    // ...

    public function findAllWithCategory()
    {
        // This equivalent to the following SQL query
        // SELECT b.*, c.*
        // FROM book b
        // LEFT JOIN category c ON b.category_id = c.id

        return $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }
}
```

Open the `templates/book/index.html.twig` file.

Modify the table header to include a "Category" column:

```twig
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Price</th>
    <th>Category</th> <!-- Add this line -->
    <th>Actions</th>
</tr>
```

Update the table rows to display the category name for each book:

```twig
{% for book in books %}
    <tr>
        <td>{{ book.id }}</td>
        <td>{{ book.name }}</td>
        <td>{{ book.price }}</td>
        <td>{{ book.category.name }}</td> <!-- Display the category name -->
        <td>
            <a href="{{ path('view_book', {'id': book.id}) }}">View Details</a>
            <a href="{{ path('edit_book', {'id': book.id}) }}">Edit</a>
            <a href="{{ path('delete_book', {'id': book.id}) }}" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
        </td>
    </tr>
{% endfor %}
```

Now, add the new book

![Alt text](/images/image-11.png)

You can see books displayed with the category name.

![Alt text](/images/image-12.png)

## Add a button to the index view to navigate to add view

Open the `templates/books/index.html.twig` file.

Add a new button or link at the top of the page, above the table of books, to navigate to the "Add Book" view. You can use the path function to generate the URL for the "Add Book" route.

```twig
{% extends 'base.html.twig' %}

{% block content %}
    <h1>Book List</h1>

    <a href="{{ path('add_book') }}" class="btn btn-primary">Add Book</a> <!-- Add this button -->

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Category</th>
            <th>Actions</th>
        </tr>
        {% for book in books %}
            <tr>
                <td>{{ book.id }}</td>
                <td>{{ book.name }}</td>
                <td>{{ book.price }}</td>
                <td>{{ book.category.name }}</td>
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

Now, you can see a link for adding a new book

![Alt text](/images/image-13.png)

## Create Order Entity

1. **Generate the Order Entity:**

   You can use the Symfony console to generate the Order entity with the necessary fields and associations. Open your terminal and navigate to your Symfony project directory, then run the following command:

   ```bash
   php bin/console make:entity Order
   ```

   This command will guide you through creating the Order entity interactively. Ensure you include the required fields for an order, such as order date, customer details, and any other relevant information.

   ![Alt text](/images/image-14.png)

2. **Define the Order-Book Relationship:**

   In your "Order" entity class (e.g., `Order.php`), define the relationship with the "Book" entity. Since an order can have multiple books, you should use a ManyToMany relationship. Update the class like this:

   ```php
   // src/Entity/Order.php

   use Doctrine\Common\Collections\ArrayCollection;
   use Doctrine\Common\Collections\Collection;

   /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

   /**
    * @ORM\ManyToMany(targetEntity="Book")
    * @ORM\JoinTable(
    *     name="order_books",
    *     joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
    *     inverseJoinColumns={@ORM\JoinColumn(name="book_id", referencedColumnName="id")}
    * )
    */
   private $books;

   public function __construct() {
       $this->books = new ArrayCollection();
   }

   /**
    * @return Collection|Book[]
    */
   public function getBooks(): Collection {
       return $this->books;
   }

   public function addBook(Book $book): self {
       if (!$this->books->contains($book)) {
           $this->books[] = $book;
       }

       return $this;
   }

   public function removeBook(Book $book): self {
       if ($this->books->contains($book)) {
           $this->books->removeElement($book);
       }

       return $this;
   }
   ```

   This code defines a ManyToMany relationship between "Order" and "Book" entities using an intermediate table named `order_books`. It also provides methods for adding and removing books from an order.

3. **Update the Database Schema:**

   After defining the "Order" entity and the relationship, apply the changes to the database schema using the Symfony console:

   ```bash
   php bin/console doctrine:migrations:diff
   php bin/console doctrine:migrations:migrate
   ```

   ![Alt text](/images/image-15.png)

4. **Use the Order Entity:**

   You can now use the "Order" entity in your Symfony application to create, read, update, and delete orders that can have multiple associated books.

Remember to adjust field names, validation rules, and other details according to your specific requirements. Once you've completed these steps, your Symfony application should support orders with multiple associated books using the "Order" entity.

## Create an Order

To create an order by adding one book, you'll need to create a controller and a form in Symfony 5.0. Here are the steps to achieve this:

**Step 1: Generate a Controller**

Run the following Symfony console command to generate a new controller:

```bash
php bin/console make:controller OrderController
```

This command will create a new controller class called `OrderController.php` in the `src/Controller` directory.

**Step 2: Create the Form Type**

Now, create a form type for the order. Run the following command:

```bash
php bin/console make:form OrderType
```

This will generate a form type class called `OrderType.php` in the `src/Form` directory.

**Step 3: Define the Form**

Open the `OrderType.php` file and define the form fields. You can include fields for the order date, customer details, and the book to add to the order. For simplicity, let's assume you have an "orderDate" field and a "book" field:

```php
// src/Form/OrderType.php
namespace App\Form;

use App\Entity\Book;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('books', EntityType::class, [
            'class' => Book::class,
            'label' => 'Book',
            'multiple' => true, // Allow multiple book selections if needed
            'choice_label' => 'name', // Display book names in the dropdown
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}

```

**Step 4: Create the Controller Action**

Open the `OrderController.php` file and add an action to create an order. In this action, you'll handle the form submission and persist the order with one selected book.

```php
// src/Controller/OrderController.php

<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/order", name="app_order")
     */
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    /**
     * @Route("/order/create", name="create_order")
     */
    public function createOrder(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            $this->addFlash('success', 'Order created successfully');

            return $this->redirectToRoute('app_order'); // Replace with your order listing route
        }

        return $this->render('order/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

```

Replace `'your_order_listing_route'` with the route where you want to display a list of orders.

**Step 5: Create a Template**

Create a template file for the form (e.g., `create.html.twig`) in the `templates/order` directory. Customize the template as needed to render the form fields and handle the submission.

```twig
{% extends 'base.html.twig' %}

{% block title %}Create Order{% endblock %}

{% block body %}
    <h1>Create Order</h1>

    {{ form_start(form) }}
    {{ form_row(form.books) }}
    <button type="submit">Create Order</button>
    {{ form_end(form) }}
{% endblock %}
```

**Step 7: Access the Form**

You can now access the form to create an order by visiting the URL `/order/create` in your Symfony application. This form will allow you to select a book and specify an order date, and when submitted, it will create an order with one book associated with it.

Remember to adapt the code and customize it according to your specific needs, including error handling, validation, and any additional fields you may require in the order or book entities.

![Alt text](/images/image-16.png)

## Create index action of OrderController to show all orders

Open your `OrderController.php` file, and create an index action to retrieve and display all orders.

```php
    /**
     * @Route("/orders", name="app_order")
     */
    public function index(): Response
    {
        // Retrieve all orders from the database
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
```

Create a Twig template to render the list of orders. You can create an `index.html.twig` template in the templates/order directory. Customize the template to display the order details as needed:

```twig
{% extends 'base.html.twig' %}

{% block title %}List of Orders{% endblock %}

{% block body %}
    <h1>List of Orders</h1>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
            </tr>
        </thead>
        <tbody>
            {% for order in orders %}
                <tr>
                    <td>{{ order.id }}</td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
```

![Alt text](/images/image-18.png)
