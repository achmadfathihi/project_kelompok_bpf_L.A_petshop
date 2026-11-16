<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    public function show(Payment $payment)
    {
        $user = auth()->user();

        $boleh = $user->isRole('kasir', 'owner') || $payment->order->customer_id === $user->id;

        abort_unless($boleh, 404);
        abort_unless(Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->response($payment->proof_path);
    }
}