# PHP Calendar & Task Management System

A responsive calendar and task management application built with PHP, SQLite, and Tailwind CSS.

## Features
- **Calendar View**: View tasks on a monthly calendar.
- **Task Management**: Add, edit, delete, and mark tasks as complete.
- **Filtering**: Filter by priority, category, status, and date range.
- **Search**: Text-based search for tasks.
- **Responsive UI**: Built with Tailwind CSS for mobile and desktop support.

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- PHP PDO Extension (usually enabled by default)
- PHP SQLite Extension (usually enabled by default)

### Installation
1. Clone the repository or download the source code.
2. Navigate to the project directory:
   ```bash
   cd calendar-system
   ```
3. Start the built-in PHP server:
   ```bash
   php -S localhost:8000
   ```
4. Open your browser and visit `http://localhost:8000`.

The database (`database.sqlite`) will be automatically created in the `includes` directory upon the first database interaction if it doesn't exist, using the schema defined in `schema.sql`.

## Project Structure
- `api.PHP`: Backend API endpoints for AJAX requests.
- `/assets`: CSS and JavaScript files.
- `/includes`: PHP helper classes and database connection.
- `index.php`: Main application entry point.
- `schema.sql`: Database schema definition.

## Technologies
- **Backend**: PHP (Vanilla)
- **Database**: SQLite
- **Frontend**: HTML5, Tailwind CSS (CDN), JavaScript (Vanilla)
