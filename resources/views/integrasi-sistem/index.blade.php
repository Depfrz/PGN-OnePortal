<x-dashboard-layout>
    <div class="bg-white rounded-[10px] p-4 lg:p-8 min-h-[800px] flex flex-col">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <p class="text-[24px] font-bold text-black">
                Dashboard <span class="mx-2">→</span> Integrasi Sistem
            </p>
        </div>

        <!-- Page Title -->
        <h1 class="text-[24px] lg:text-[32px] font-bold text-black mb-6">Manajemen Modul Aplikasi</h1>

        <!-- Action Bar -->
        <div class="flex flex-col lg:flex-row items-center justify-between mb-8 gap-4">
            <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full lg:w-auto">
                <!-- Delete Module Button -->
                <button class="w-full sm:w-auto bg-[#cd0d10] text-white text-[18px] lg:text-[20px] font-bold px-8 py-3 rounded-[15px] hover:bg-red-700 transition-colors">
                    Hapus Modul
                </button>
                
                <!-- Add Module Button -->
                <a href="{{ route('integrasi-sistem.create') }}" class="w-full sm:w-auto bg-[#0dcd4d] text-white text-[18px] lg:text-[20px] font-bold px-8 py-3 rounded-[15px] hover:bg-green-600 transition-colors text-center">
                    Tambah Modul
                </a>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full lg:w-[420px]">
                <input type="text" 
                       placeholder="Cari Modul..." 
                       class="w-full bg-[#d9d9d9] text-black text-[18px] lg:text-[20px] font-bold placeholder-black px-6 py-3 rounded-[15px] border-none focus:ring-2 focus:ring-blue-500 pr-12">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-black">
                        <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Module List Container -->
        <div class="flex flex-col gap-6">
            <!-- Module Card 1: HCM SIP-PGN -->
            <div class="border border-black rounded-[15px] p-6 lg:p-8 flex flex-col lg:flex-row items-start gap-6 bg-white transition-shadow hover:shadow-lg">
                <!-- Module Image -->
                <div class="w-full lg:w-[300px] h-[200px] bg-gray-200 rounded-[5px] flex-shrink-0 bg-cover bg-center overflow-hidden border border-gray-300">
                    <!-- Placeholder image if asset is missing -->
                    <div class="w-full h-full flex items-center justify-center bg-[#d9d9d9]">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-500 opacity-50">
                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="flex-1">
                    <h3 class="text-[24px] font-bold text-black mb-2">HCM SIP-PGN</h3>
                    <p class="text-[16px] text-justify text-black leading-relaxed">
                        platform digital terintegrasi yang berfungsi sebagai pusat layanan mandiri (Employee Self-Service) bagi seluruh pekerja di lingkungan PT Pertamina Gas Negara Tbk. Sistem ini dirancang untuk mendigitalkan seluruh siklus administrasi kepegawaian, mulai dari pengelolaan data personal, manajemen waktu kerja, hingga remunerasi.
                        <br><br>
                        Melalui portal ini, setiap pegawai dapat mengakses informasi terkait hak dan kewajiban mereka secara real-time dan transparan.
                    </p>
                </div>

                <!-- Action -->
                <div class="lg:self-center flex flex-col items-center gap-2">
                    <a href="#" class="flex items-center gap-2 bg-[#30ff07] px-6 py-2 rounded-[10px] border border-transparent hover:border-black transition-all hover:shadow-md">
                        <span class="text-[16px] font-bold text-black">Visit site!</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 13V19C18 19.5304 17.7893 20.0391 17.4142 20.4142C17.0391 20.7893 16.5304 21 16 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V8C3 7.46957 3.21071 6.96086 3.58579 6.58579C3.96086 6.21071 4.46957 6 5 6H11" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 3H21V9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 14L21 3" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>

             <!-- Module Card 2: Another Example -->
             <div class="border border-black rounded-[15px] p-6 lg:p-8 flex flex-col lg:flex-row items-start gap-6 bg-white transition-shadow hover:shadow-lg">
                <!-- Module Image -->
                <div class="w-full lg:w-[300px] h-[200px] bg-gray-200 rounded-[5px] flex-shrink-0 bg-cover bg-center overflow-hidden border border-gray-300">
                    <div class="w-full h-full flex items-center justify-center bg-[#d9d9d9]">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-500 opacity-50">
                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="flex-1">
                    <h3 class="text-[24px] font-bold text-black mb-2">Project Management System</h3>
                    <p class="text-[16px] text-justify text-black leading-relaxed">
                        Sistem manajemen proyek untuk memantau progres, anggaran, dan sumber daya proyek secara efisien. Memungkinkan kolaborasi tim yang lebih baik dan pelaporan yang akurat.
                    </p>
                </div>

                <!-- Action -->
                <div class="lg:self-center flex flex-col items-center gap-2">
                    <a href="#" class="flex items-center gap-2 bg-[#30ff07] px-6 py-2 rounded-[10px] border border-transparent hover:border-black transition-all hover:shadow-md">
                        <span class="text-[16px] font-bold text-black">Visit site!</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 13V19C18 19.5304 17.7893 20.0391 17.4142 20.4142C17.0391 20.7893 16.5304 21 16 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V8C3 7.46957 3.21071 6.96086 3.58579 6.58579C3.96086 6.21071 4.46957 6 5 6H11" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 3H21V9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 14L21 3" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
