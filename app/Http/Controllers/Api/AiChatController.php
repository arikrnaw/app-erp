<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiChatController extends Controller
{
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private $apiKey;

    public function __construct()
    {
        // You can get a free API key from https://makersuite.google.com/app/apikey
        $this->apiKey = env('GEMINI_API_KEY', 'demo_key');
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'nullable|string',
        ]);

        try {
            $userMessage = $request->input('message');
            $context = $request->input('context', '');
            
            // Create a system prompt that helps with application usage
            $systemPrompt = $this->getSystemPrompt($context);
            
            // Prepare the conversation for the AI
            $conversation = $this->prepareConversation($userMessage, $systemPrompt);
            
            // Get AI response
            $aiResponse = $this->getAiResponse($conversation, $userMessage);
            
            // Process and enhance the response
            $enhancedResponse = $this->enhanceResponse($aiResponse, $userMessage);
            
            return response()->json([
                'success' => true,
                'message' => $enhancedResponse,
                'timestamp' => now(),
                'context' => $context
            ]);

        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Maaf, saya sedang mengalami gangguan teknis. Silakan coba lagi dalam beberapa saat.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function getSystemPrompt($context = ''): string
    {
        $basePrompt = "Anda adalah asisten AI yang membantu pengguna menggunakan aplikasi ERP/CRM. 
        Aplikasi ini memiliki modul-modul berikut:
        
        1. **Dashboard** - Ringkasan data bisnis
        2. **CRM** - Manajemen pelanggan, prospek, follow-up, dan tiket dukungan
        3. **Produk** - Manajemen produk dan kategori
        4. **Penjualan** - Order penjualan dan manajemen pelanggan
        5. **Pembelian** - Order pembelian, permintaan pembelian, dan penerimaan barang
        6. **Inventori** - Manajemen stok, gudang, dan lokasi
        7. **Keuangan** - Jurnal, buku besar, neraca saldo, dan laporan keuangan
        8. **Manufaktur** - BOM, rencana produksi, dan work order
        9. **HR** - Manajemen karyawan, cuti, dan penggajian
        10. **Proyek** - Manajemen proyek, tugas, dan tim
        11. **Laporan** - Analitik dan laporan bisnis
        12. **Pengaturan** - Profil, penampilan, dan RBAC
        
        Berikan jawaban yang jelas, singkat, dan bermanfaat dalam bahasa Indonesia. 
        Jika pengguna bertanya tentang fitur tertentu, jelaskan cara menggunakannya.
        Jika pengguna mengalami masalah, berikan solusi langkah demi langkah.";

        if ($context) {
            $basePrompt .= "\n\nKonteks saat ini: " . $context;
        }

        return $basePrompt;
    }

    private function prepareConversation($userMessage, $systemPrompt): array
    {
        return [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $systemPrompt . "\n\nPertanyaan pengguna: " . $userMessage
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getAiResponse($conversation, $userMessage): string
    {
        // Try to get from cache first
        $cacheKey = 'ai_chat_' . md5(json_encode($conversation));
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse) {
            Log::info('Using cached AI response');
            return $cachedResponse;
        }

        // Log API call attempt
        Log::info('Attempting Gemini API call', [
            'api_key' => substr($this->apiKey, 0, 10) . '...',
            'url' => $this->apiUrl,
            'user_message' => $userMessage
        ]);

        try {
            $response = Http::timeout(60)->withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $this->apiKey
            ])->post($this->apiUrl, $conversation);

            Log::info('Gemini API Response Status: ' . $response->status());

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Gemini API Response Data:', $data);
                
                // Extract text from Gemini response
                $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat memproses permintaan Anda saat ini.';
                
                // Clean up the response
                $aiResponse = $this->cleanAiResponse($aiResponse);
                
                Log::info('Cleaned AI Response: ' . $aiResponse);
                
                // Cache the response for 1 hour
                Cache::put($cacheKey, $aiResponse, 3600);
                
                return $aiResponse;
            }

            // Log the error for debugging
            Log::error('Gemini API Error: ' . $response->status() . ' - ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
        }

        Log::info('Using fallback response for: ' . $userMessage);
        // Fallback response if API fails
        return $this->getFallbackResponse($userMessage);
    }

    private function cleanAiResponse($response): string
    {
        // Clean up the response
        $response = trim($response);
        
        // Remove any system prompt remnants
        $response = preg_replace('/^.*?Bot:\s*/s', '', $response);
        $response = preg_replace('/^.*?Asisten:\s*/s', '', $response);
        
        // If response is too short or seems incomplete, return empty
        if (strlen($response) < 10) {
            return '';
        }
        
        return $response;
    }

    private function enhanceResponse($aiResponse, $userMessage): string
    {
        Log::info('Enhancing AI response: ' . $aiResponse);
        
        // If AI response is empty, use fallback
        if (empty($aiResponse)) {
            Log::info('AI response is empty, using fallback');
            return $this->getFallbackResponse($userMessage);
        }
        
        // Clean up the response
        $response = trim($aiResponse);
        
        // Remove any system prompt remnants
        $response = preg_replace('/^.*?Bot:\s*/s', '', $response);
        $response = preg_replace('/^.*?Asisten:\s*/s', '', $response);
        
        Log::info('Cleaned response: ' . $response);
        
        // If response is still too short (less than 5 characters), use fallback
        if (strlen($response) < 5) {
            Log::info('Response too short, using fallback');
            return $this->getFallbackResponse($userMessage);
        }
        
        return $response;
    }

    private function getFallbackResponse($userMessage): string
    {
        $message = strtolower($userMessage);
        
        // Specific question patterns and their responses
        $specificResponses = [
            'bagaimana cara menggunakan dashboard' => 'Untuk menggunakan Dashboard, ikuti langkah berikut:\n\n1. Klik menu "Dashboard" di sidebar kiri\n2. Dashboard akan menampilkan ringkasan data bisnis Anda\n3. Anda dapat melihat:\n   • Total penjualan hari ini\n   • Jumlah pelanggan aktif\n   • Order yang pending\n   • Grafik performa bisnis\n4. Klik pada card atau grafik untuk melihat detail lebih lanjut\n5. Gunakan filter tanggal untuk melihat data periode tertentu',
            
            'cara menambah produk baru' => 'Untuk menambah produk baru, ikuti langkah berikut:\n\n1. Klik menu "Products" di sidebar\n2. Klik tombol "Create" atau "Tambah Produk"\n3. Isi form dengan informasi produk:\n   • Nama produk\n   • SKU (kode produk)\n   • Kategori produk\n   • Harga beli dan jual\n   • Stok awal\n   • Deskripsi produk\n4. Klik "Save" untuk menyimpan\n5. Produk akan muncul di daftar produk',
            
            'cara membuat order penjualan' => 'Untuk membuat order penjualan, ikuti langkah berikut:\n\n1. Klik menu "Sales Orders" di sidebar\n2. Klik tombol "Create" atau "Tambah Order"\n3. Pilih pelanggan dari dropdown\n4. Tambahkan produk yang akan dijual:\n   • Pilih produk dari daftar\n   • Masukkan quantity\n   • Harga akan terisi otomatis\n5. Review total order\n6. Pilih status order (draft/confirmed)\n7. Klik "Save" untuk menyimpan order',
            
            'cara mengelola pelanggan' => 'Untuk mengelola pelanggan, ikuti langkah berikut:\n\n1. Klik menu "CRM" → "Customers" di sidebar\n2. Untuk menambah pelanggan baru:\n   • Klik "Create" atau "Tambah Pelanggan"\n   • Isi data lengkap pelanggan\n   • Simpan data\n3. Untuk mengedit pelanggan:\n   • Klik pada nama pelanggan\n   • Klik tombol "Edit"\n   • Update informasi yang diperlukan\n4. Untuk melihat riwayat pelanggan:\n   • Buka detail pelanggan\n   • Lihat tab "History" atau "Orders"',
            
            'cara melihat laporan keuangan' => 'Untuk melihat laporan keuangan, ikuti langkah berikut:\n\n1. Klik menu "Finance" di sidebar\n2. Pilih jenis laporan yang diinginkan:\n   • "General Ledger" - untuk buku besar\n   • "Trial Balance" - untuk neraca saldo\n   • "Financial Reports" - untuk laporan keuangan\n3. Atur periode laporan (tanggal awal dan akhir)\n4. Klik "Generate" atau "View"\n5. Laporan akan ditampilkan dan dapat di-export ke PDF/Excel',
            
            'cara mengelola inventori' => 'Untuk mengelola inventori, ikuti langkah berikut:\n\n1. Klik menu "Inventory" di sidebar\n2. Anda dapat mengakses:\n   • "Stock Management" - untuk melihat stok produk\n   • "Warehouses" - untuk mengelola gudang\n   • "Stock Movements" - untuk melihat pergerakan stok\n3. Untuk menambah stok:\n   • Buat "Goods Receipt" dari menu Purchasing\n   • Atau gunakan "Stock Adjustment"\n4. Untuk mengurangi stok:\n   • Buat "Sales Order" atau "Stock Adjustment"',
            
            'cara mengatur pengguna dan hak akses' => 'Untuk mengatur pengguna dan hak akses, ikuti langkah berikut:\n\n1. Klik menu "Settings" → "Roles & Permissions" di sidebar\n2. Untuk menambah pengguna baru:\n   • Klik "Users" → "Create"\n   • Isi data pengguna\n   • Assign role yang sesuai\n3. Untuk mengatur role:\n   • Klik "Roles" → "Create"\n   • Beri nama dan deskripsi role\n   • Pilih permissions yang diperlukan\n4. Untuk mengatur permissions:\n   • Klik "Permissions"\n   • Aktifkan/nonaktifkan permission sesuai kebutuhan'
        ];
        
        // Check for specific questions first
        foreach ($specificResponses as $pattern => $response) {
            if (strpos($message, $pattern) !== false) {
                return $response;
            }
        }
        
        // Fallback to keyword-based responses
        $keywordResponses = [
            'dashboard' => 'Dashboard menampilkan ringkasan data bisnis Anda. Klik menu "Dashboard" di sidebar untuk melihat total penjualan, pelanggan, dan aktivitas terbaru.',
            'produk' => 'Untuk mengelola produk, buka menu "Products" di sidebar. Anda dapat menambah, mengedit, dan mengelola semua produk di sini.',
            'penjualan' => 'Modul Penjualan memungkinkan Anda membuat dan mengelola order penjualan. Akses melalui menu "Sales Orders" di sidebar.',
            'pelanggan' => 'Modul CRM membantu Anda mengelola pelanggan dan prospek. Buka menu "CRM" → "Customers" untuk mengelola data pelanggan.',
            'laporan' => 'Untuk melihat laporan, buka menu "Reports" di sidebar. Anda dapat melihat berbagai laporan bisnis dan analitik.',
            'keuangan' => 'Modul Keuangan mencakup jurnal, buku besar, dan laporan keuangan. Akses melalui menu "Finance" di sidebar.',
            'inventori' => 'Manajemen inventori memungkinkan Anda melacak stok dan mengelola gudang. Buka menu "Inventory" untuk mengakses fitur ini.',
            'pengaturan' => 'Pengaturan aplikasi dapat diakses melalui menu "Settings" di sidebar. Anda dapat mengubah profil, penampilan, dan pengaturan RBAC.',
            'bantuan' => 'Saya siap membantu Anda menggunakan aplikasi ini. Silakan ajukan pertanyaan spesifik tentang fitur yang ingin Anda ketahui.',
            'help' => 'Saya siap membantu Anda menggunakan aplikasi ini. Silakan ajukan pertanyaan spesifik tentang fitur yang ingin Anda ketahui.'
        ];

        foreach ($keywordResponses as $keyword => $response) {
            if (strpos($message, $keyword) !== false) {
                return $response;
            }
        }

        return 'Terima kasih atas pertanyaan Anda. Saya adalah asisten AI yang siap membantu Anda menggunakan aplikasi ERP/CRM ini. Silakan ajukan pertanyaan spesifik tentang fitur yang ingin Anda ketahui, seperti cara menggunakan Dashboard, CRM, Produk, atau modul lainnya.';
    }

    public function getChatHistory(Request $request): JsonResponse
    {
        // In a real application, you would store chat history in database
        // For now, we'll return empty array
        return response()->json([
            'success' => true,
            'history' => []
        ]);
    }

    public function clearChatHistory(Request $request): JsonResponse
    {
        // In a real application, you would clear chat history from database
        return response()->json([
            'success' => true,
            'message' => 'Riwayat chat berhasil dihapus'
        ]);
    }
}
