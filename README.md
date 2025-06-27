#Collaboration and Management System

A local, open-source collaboration and project management system inspired by Trello. Built for teams that need a private, self-hosted alternative to commercial tools.
My diploma project.

![2025-06-26_00-13-36](https://github.com/user-attachments/assets/8cd79f4d-0f72-4c0c-9ee6-94c1c422835c)

![2025-06-26_00-07-21](https://github.com/user-attachments/assets/1bed02bc-bbfd-4c2a-b37b-29eb677e9459)

![2025-06-26_00-15-45](https://github.com/user-attachments/assets/533e70a3-3078-4973-8e32-b45bcaefe4b4)

## Features
Project Management
        Create, edit, filter, sort, and delete projects.
        Assign owners and control access via role-based permissions.
        Progress bars and status badges (open, in_progress, paused, completed, closed).
        Stats dashboard: Active, completed, and total projects.
Task Management
        Add, edit, and organize tasks within projects.
        Task filtering, status tracking, and completion progress.
        Task comments and activity logs.
User and Roles
        User authentication and authorization.
        Abilities-based role system (e.g., project.create, project.manage).
Filtering and Sorting
        Search projects by name or description.
        Filter by status, owner, and date ranges (quick filters for last week/month).
        Sort by creation date, tasks count, etc.
Views
        Grid and table views for projects.
        Detailed project pages with owner info, progress, and task breakdown.
Admin Dashboard
        (If enabled) Admin-only routes for system management.
Webhooks (with HMAC)
        For easier third-party integrations

Technology Stack

Frontend: Vue 3, Inertia.js, Tailwind CSS
Backend: Laravel (PHP)
Database: MySQL/PostgreSQL (via Laravel Eloquent)
Other: Role-based Permissions, Webhooks for model events

Getting Started
Prerequisites

- PHP 8.1+
- Composer
- Node.js and npm
- MySQL or PostgreSQL

Installation

Clone the repository:
```sh
git clone https://github.com/deltamolfar/collaboration-and-management-system.git
cd collaboration-and-management-system
```

Install backend dependencies:
sh

composer install

Install frontend dependencies:
```sh
npm install
```
Copy and configure environment file:
```sh
cp .env.example .env
# Edit .env to set up database and mail credentials
```
Generate application key:
```sh
php artisan key:generate
```
Run migrations:
```sh
php artisan migrate
```
Build frontend assets:
```sh
npm run build
```
Start the development server:
```sh
    php artisan serve
```

Usage
- Register or log in.
- Create a new project, set its status, and assign a team.
- Add and manage tasks within your projects.
- Use filters and sorting to quickly find what you need.
- Switch between grid and table views for different workflows.

Customization
- Extend roles and permissions in the user model.
- Add new project or task statuses in GlobalSetting.
- Customize frontend styling via Tailwind.

This project is licensed under the GPLv3 License.
