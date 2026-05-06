# ShelfSpace – Modern Digital Library System

The ShelfSpace project has been fully implemented based on your specifications. All requested features, strict route protections, and custom CSS design details are now complete. 

## Implementation Details

### Database & Security
- **[database.sql](file:///c:/MAMP/htdocs/library/database.sql)**: Complete MySQL schema including the `users`, `categories`, `books`, and `borrowings` tables with appropriate foreign keys (`ON DELETE CASCADE` and `RESTRICT`).
- **[config.php](file:///c:/MAMP/htdocs/library/config.php)**: Establishes a secure PDO connection with error modes properly configured.
- **Security Check**: `htmlspecialchars()` is used consistently throughout the application via a helper `sanitize()` function. PDO prepared statements are exclusively used to prevent SQL injection.

> [!IMPORTANT]
> To get started, you must import `database.sql` into your local `shelfspace` database via phpMyAdmin or the MySQL CLI.

### Authentication & Route Protection
- **[auth_check.php](file:///c:/MAMP/htdocs/library/partials/auth_check.php)**: This is the core of the route protection. It is required at the top of every protected page.
  - If a user isn't logged in, they are forcibly redirected to `login.php`.
  - It also includes a `require_admin()` function which blocks standard users from accessing admin routes.
- **[login.php](file:///c:/MAMP/htdocs/library/login.php)** & **[register.php](file:///c:/MAMP/htdocs/library/register.php)**: Built using `password_hash()` and `password_verify()`. On successful login, the session ID is securely regenerated (`session_regenerate_id()`) to prevent session fixation attacks.

### Modern UI/UX (Zero Bootstrap)
The design prioritizes a high-end, Netflix-style visual language.
- **[style.css](file:///c:/MAMP/htdocs/library/assets/style.css)**: Implements a dark mode default, vibrant gradient accents (`--accent-gradient`), glassmorphism on the sticky navbar, and a pure Flexbox/Grid responsive layout.
- **[animations.css](file:///c:/MAMP/htdocs/library/assets/animations.css)**: Provides a staggered card fade-in animation on page load. All buttons and cards include smooth `0.3s ease` hover effects, translating slightly upwards and increasing shadow depth.
- **[script.js](file:///c:/MAMP/htdocs/library/assets/script.js)**: Handles live, instantaneous search and category filtering for the book grid without requiring page reloads.

### Core Features

#### The Book Grid (`index.php`)
- Displays all books as richly designed cards.
- Status badges change color dynamically based on whether the book is "Available" (Green) or "Borrowed" (Red).
- Users can instantly borrow an available book right from the grid.

#### User Dashboard (`dashboard.php`)
- Shows a personalized list of currently borrowed books as cards.
- Allows users to easily click "Return Book" on any active borrowing.
- Displays a clean history table of all past returned books.

#### Admin Panel (`admin.php`, `add_book.php`, `categories.php`)
- The main **Admin Dashboard** displays high-level stats (total books, active users, currently borrowed books).
- **Categories** and **Books** can be managed easily through secure forms that ensure related constraints are respected (e.g., an admin cannot delete a category if books are still assigned to it).

## Verification Plan
1. **Initialize DB**: Run `database.sql` in your MAMP MySQL environment.
2. **Test Route Protection**: Try navigating to `http://localhost/library/index.php` directly without an active session—you will be redirected to the login screen.
3. **Admin Setup**: Register a new user account. Currently, the system defaults new registrations to the `user` role. You will need to manually change your first user's role to `'admin'` in the `users` table via your database manager to test the admin features.
4. **End-to-End Test**: Log in as an admin, create a category, add a book, return to the grid, search for it, and test borrowing/returning it.
