# Movie Website Dashboard

This project is a **Movie/Series Management Dashboard** built with PHP, MySQL, Bootstrap 5, and DataTables. It allows admins to manage movies and series, including genres, seasons, episodes, and more, with a modern, responsive UI.

## Features

- **Add, Edit, Delete Movies/Series**
- **Genre Management** (multiple genres per movie/series)
- **Season & Episode Management** (for series)
- **Responsive Dashboard UI** (Bootstrap 5)
- **DataTables Integration** for searching, sorting, and pagination
- **Modal-based CRUD** for a smooth user experience
- **Cascading Deletes** (removing a movie/series also removes its seasons and episodes)
- **Secure Database Operations** (uses prepared statements or input sanitization)
- **Tooltips and Truncated Descriptions** for better UX

## Technologies Used

- PHP (Backend)
- MySQL (Database)
- Bootstrap 5 (Frontend)
- DataTables (Table UI)
- jQuery (Frontend scripting)

## Setup Instructions

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/movie-dashboard.git
   cd movie-dashboard
   ```

2. **Import the Database:**
   - Import the provided SQL file into your MySQL server.
   - Make sure your tables have the correct foreign key constraints with `ON DELETE CASCADE`.

3. **Configure Database Connection:**
   - Edit `includes/db-connection.php` and set your MySQL credentials.

4. **Run Locally:**
   - Place the project folder in your XAMPP/htdocs or your web server's root.
   - Start Apache and MySQL.
   - Visit `http://localhost/movie-dashboard/DASHBOARD-HTML/web-movies.php` in your browser.

## Folder Structure

```
/DASHBOARD-HTML
  ├── web-movies.php
  ├── web-movie-series.php
  └── ... (other dashboard pages)
  
/includes
  ├── db-connection.php
  └── dashboard-header-sidebar.php

/DASHBOARD-CSS
  └── dashboard.css
```

## Screenshots

![Dashboard Screenshot](screenshot.png)

## Credits

- [Bootstrap](https://getbootstrap.com/)
- [DataTables](https://datatables.net/)
- [FontAwesome](https://fontawesome.com/)

## License

This project is open source and available under the [MIT License](LICENSE).

---

**Enjoy managing your movie and series collection!**
