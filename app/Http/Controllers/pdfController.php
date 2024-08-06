<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\Rekening;

class PdfController extends Controller
{
    public function generatedPDF(Request $request, $id)
    {
        $transaction = Transaction::where('id', $id)->with('product')->first();

        $data = [
            'title' => 'Invoice',
            'transaction' => $transaction,
            'user' => Auth::user()->name,
            'rekening' => Rekening::where('is_active', 1)->first(),
        ];

        $pdf = Pdf::loadView('myPDF', $data);

        return $pdf->download('pdfexample.pdf');
    }
}

