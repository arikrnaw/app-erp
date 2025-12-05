<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Welcome - ERP System">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="min-h-screen bg-gradient-to-br from-white via-gray-50 to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-black">
        <!-- Header Navigation -->
        <header
            class="w-full border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm sticky top-0 z-50">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">ERP System</span>
                </div>

                <div class="flex items-center space-x-4">
                    <Link v-if="$page.props.auth?.user" :href="route('dashboard')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                    Dashboard
                    </Link>
                    <Link v-else :href="route('login')"
                        class="px-6 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    Masuk
                    </Link>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="space-y-8 animate-fade-in">
                    <div class="space-y-4">
                        <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
                            Selamat Datang di
                            <span class="text-red-600 dark:text-red-500">ERP System</span>
                        </h1>
                        <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                            Solusi terintegrasi untuk mengelola bisnis Anda dengan lebih efisien.
                            Kelola keuangan, inventori, dan operasional dalam satu platform.
                        </p>
                    </div>

                    <!-- Features List -->
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mt-1">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Manajemen Keuangan</h3>
                                <p class="text-gray-600 dark:text-gray-400">Lacak pendapatan, pengeluaran, dan laporan
                                    keuangan secara real-time</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mt-1">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Inventori & Stok</h3>
                                <p class="text-gray-600 dark:text-gray-400">Kelola produk, stok, dan persediaan dengan
                                    sistem yang terintegrasi</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mt-1">
                                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">Laporan & Analitik</h3>
                                <p class="text-gray-600 dark:text-gray-400">Dapatkan insight bisnis dengan dashboard dan
                                    laporan yang komprehensif</p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <Link :href="route('login')"
                            class="px-8 py-4 bg-red-600 text-white text-lg font-semibold rounded-lg hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                        Mulai Sekarang
                        </Link>
                        <a href="#features"
                            class="px-8 py-4 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-lg font-semibold rounded-lg border-2 border-gray-300 dark:border-gray-700 hover:border-red-600 dark:hover:border-red-500 transition-all duration-200 text-center">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>

                <!-- Right Visual -->
                <div class="relative lg:block hidden">
                    <div class="relative">
                        <!-- Decorative Background -->
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-red-600/5 rounded-3xl transform rotate-6">
                        </div>
                        <div
                            class="absolute inset-0 bg-gradient-to-tr from-red-600/10 to-transparent rounded-3xl transform -rotate-6">
                        </div>

                        <!-- Main Card -->
                        <div
                            class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 border border-gray-200 dark:border-gray-700">
                            <!-- Dashboard Preview -->
                            <div class="space-y-6">
                                <!-- Header -->
                                <div
                                    class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Dashboard</span>
                                </div>

                                <!-- Stats Grid -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div
                                        class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-100 dark:border-red-900/30">
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">Rp 2.5M</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pendapatan</div>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">1,234</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Produk</div>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">567</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pelanggan</div>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white">89</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Transaksi</div>
                                    </div>
                                </div>

                                <!-- Chart Placeholder -->
                                <div
                                    class="h-32 bg-gradient-to-t from-red-500/20 to-transparent rounded-lg flex items-end justify-around p-4 border border-gray-200 dark:border-gray-700">
                                    <div class="w-8 bg-red-500 rounded-t h-16"></div>
                                    <div class="w-8 bg-red-400 rounded-t h-20"></div>
                                    <div class="w-8 bg-red-500 rounded-t h-12"></div>
                                    <div class="w-8 bg-red-400 rounded-t h-24"></div>
                                    <div class="w-8 bg-red-500 rounded-t h-14"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div id="features" class="mt-24 lg:mt-32">
                <div class="text-center mb-12">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Fitur Unggulan
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Semua yang Anda butuhkan untuk mengelola bisnis dalam satu platform
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-red-500 dark:hover:border-red-500 transition-all duration-200 hover:shadow-lg">
                        <div
                            class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Akuntansi</h3>
                        <p class="text-gray-600 dark:text-gray-400">Sistem akuntansi lengkap dengan jurnal, buku besar,
                            dan laporan keuangan</p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-red-500 dark:hover:border-red-500 transition-all duration-200 hover:shadow-lg">
                        <div
                            class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Inventori</h3>
                        <p class="text-gray-600 dark:text-gray-400">Kelola stok, produk, dan persediaan dengan sistem
                            yang terintegrasi</p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-red-500 dark:hover:border-red-500 transition-all duration-200 hover:shadow-lg">
                        <div
                            class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Laporan</h3>
                        <p class="text-gray-600 dark:text-gray-400">Dashboard dan laporan analitik untuk pengambilan
                            keputusan yang tepat</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-20 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center text-gray-600 dark:text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} ERP System. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}
</style>
