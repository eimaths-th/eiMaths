<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'detail',
        'centre',
        'code',
        'unit',
        'amount',
        'created_by',
        'updated_by'
    ];

    //center name
    public function center()
    {
        return $this->belongsTo(Department::class, 'centre', 'id');
    }

    public function generateBarcode($code)
    {
        $barcode = new DNS1D();
        $barcodeData = $barcode->getBarcodeHTML($code, 'C39', 1, 50, 'black', false);

        return $barcodeData;
    }
    public function generateQrcode($code)
    {
        $qrcode = new DNS2D();
        $qrcodeData = $qrcode->getBarcodeHTML($code, 'QRCODE', 5, 5, 'black', false);

        return $qrcodeData;
    }
}
