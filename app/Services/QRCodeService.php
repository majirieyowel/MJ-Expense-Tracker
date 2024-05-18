<?php

namespace App\Services;

class QRCodeService
{
    protected $public_folder = 'qrcodes/';

    /**
     * Generate QR code
     * 
     * @param string $data
     * @param string $fileName
     * 
     * @return void
     */
    public function generate(string $data, string $fileName = 'spenda-qrcode.png'): void
    {

        try {

            require_once(base_path('/app/Libraries/phpqrcode/qrlib.php'));

    
            $filePath = public_path($this->public_folder)  . $fileName;

            \QRcode::png($data, $filePath, "h", 4, 2);
        } catch (\Throwable $th) {
            report($th);
        }
    }

    /**
     * Get public URL of qr image
     * 
     * @param string $fileName
     * 
     * @return string
     */
    public function getPublicUrl(string $fileName): string
    {
        return  config('app.url') . "/{$this->public_folder}/" . $fileName;
    }

    /**
     * Check if qrcode image exists in filesystem
     * 
     * @param string $fileName
     * 
     * @return string
     */
    public function exists(string $fileName): bool
    {
        $filePath = public_path('/' . $this->public_folder)  . $fileName;

        return file_exists($filePath);
    }

    public function delete(string $fileName): void
    {

        // require_once(base_path('/app/Libraries/phpqrcode/qrlib.php'));

        // $filePath = public_path('qrcodes/')  . $fileName;

        // \QRcode::png($data, $filePath, "h", 4, 2);
    }
}
