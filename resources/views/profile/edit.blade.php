<x-dashboard-layout>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Profil</h1>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg dark:bg-gray-800 dark:shadow-none">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg dark:bg-gray-800 dark:shadow-none">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-dashboard-layout>
