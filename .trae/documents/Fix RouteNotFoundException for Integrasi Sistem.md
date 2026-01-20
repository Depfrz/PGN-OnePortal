I have identified the cause of the `RouteNotFoundException`. The route name for "Integrasi Sistem" was updated to `integrasi-sistem.index` in `web.php`, but the dashboard layout file (`dashboard.blade.php`) is still using the old name `integrasi-sistem`.

I will perform the following steps to fix this:

1.  **Update `resources/views/layouts/dashboard.blade.php`**:
    *   Change the navigation link for "Integrasi Sistem" from `route('integrasi-sistem')` to `route('integrasi-sistem.index')`.
    *   Update the active state check for the sidebar menu to use `request()->routeIs('integrasi-sistem*')` so that the menu remains highlighted when you are on sub-pages like "Tambah Modul" (`/integrasi-sistem/tambah`).

This will resolve the error and ensure the sidebar navigation works correctly for all "Integrasi Sistem" pages.