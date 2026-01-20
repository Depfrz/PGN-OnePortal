I will implement the Role-Based Access Control (RBAC) system as per your specifications.

### Implementation Plan

1.  **Database & Audit System**:
    *   **Audit Log**: Create an `AuditLog` model and migration to record critical activities (who did what, when).
    *   **Role Seeder**: Update `RoleSeeder.php` to strictly enforce the 4-tier hierarchy (User < SuperUser < Supervisor < Admin) and reset permissions.
    *   **Module Seeder**: Create a `ModuleSeeder` to populate dummy modules (Finance, HR, etc.) for testing access limits.

2.  **Middleware & Configuration**:
    *   Register Spatie's middleware (`role`, `permission`) in `bootstrap/app.php`.
    *   Create a custom middleware `CheckModuleAccess` (optional, or handle in Controller) to validate specific module permissions.

3.  **Logic & Controllers (Refactoring)**:
    *   **IntegrasiSistemController**:
        *   `index()`: Logic to show **all modules** for Supervisor/Admin, but **only assigned modules** for User/SuperUser.
        *   `create()`: Restricted to Supervisor/Admin.
    *   **ManagementUserController**:
        *   Restricted strictly to **Admin** role.
    *   **Routes**: Update `routes/web.php` to use these controllers and apply `middleware`.

4.  **View Updates**:
    *   Make `integrasi-sistem.index` dynamic: It will display modules passed from the controller instead of static HTML, proving that the role filtering works.
    *   Hide "Tambah Modul" buttons for unauthorized roles.

5.  **Verification**:
    *   I will verify that:
        *   **User** sees only assigned modules.
        *   **SuperUser** sees assigned modules + can edit (if implemented).
        *   **Supervisor** sees ALL modules + "Tambah Modul" button.
        *   **Admin** can access User Management.

I will start by setting up the Audit Log and updating the Role Seeder.