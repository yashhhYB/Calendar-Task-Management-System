# PHP Calendar & Task Management System

A responsive calendar and task management application built with PHP, SQLite, and Tailwind CSS.

## Features
- **Calendar View**: View tasks on a monthly calendar.
- **Task Management**: Add, edit, delete, and mark tasks as complete.
- **Filtering**: Filter by priority, category, status, and date range.
- **Search**: Text-based search for tasks.
- **Responsive UI**: Built with Tailwind CSS for mobile and desktop support.
- **CSV Import/Export**: Easily backup and restore your tasks.
- **Interactive Experience**: Uses AJAX for seamless, page-refresh-free updates.

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

The database (`database.sqlite`) will be automatically created in the root directory upon the first database interaction if it doesn't exist, using the schema defined in `schema.sql`.

## Project Structure
- `index.php`: Main application entry point (HTML structure).
- `script.js`: Frontend logic (DOM manipulation, event handling, API calls).
- `api.php`: Consolidated backend logic (Database connection, API endpoints).
- `tasks.json`: Fallback storage if SQLite is not available.
- `schema.sql`: Database schema definition.

## Technologies
- **Backend**: PHP (Vanilla)
- **Database**: SQLite (with JSON fallback)
- **Frontend**: HTML5, Tailwind CSS (CDN), JavaScript (AJAX)
