<?php

namespace App\Filament\User\Widgets;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;
use KoalaFacade\FilamentAlertBox\Widgets\AlertBoxWidget;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class AlertInfo extends AlertBoxWidget
{
    protected ?string $productName = null;
    protected ?int $transactionId = null; // Add a property to store the transaction ID

    public function getColumnSpan(): int|string|array
    {
        return 2;
    }

    public string $type = 'warning';

    public Closure|string|Htmlable|null $label = 'Informasi pembayaran';

    public string|Closure|Htmlable|null $helperText = null;

    public function mount(): void
    {
        $transaction = $this->getLatestTransaction();
        if ($transaction) {
            $this->productName = $transaction->product->nama;
            $this->transactionId = $transaction->id; // Store the transaction ID
        }
    }

    protected function getLatestTransaction(): ?Transaction
    {
        return Transaction::where('user_id', Auth::user()->id)
            ->where('status', 0)
            ->latest()
            ->first();
    }

    public function getHelperText(): string|Htmlable|null
    {
        if (!$this->productName || !$this->transactionId) {
            return 'Anda tidak memiliki tunggakan pembayaran apapun'; // Ensure no helper text is returned if conditions are not met
        }

        return $this->helperText ?? $this->generateHelperTextWithButton();
    }

    protected function generateHelperTextWithButton(): Htmlable
    {
        $text = 'Anda belum melakukan pembayaran untuk produk ' . $this->productName . '.';
        $buttonUrl = $this->transactionId ? "/user/list-payments/{$this->transactionId}/edit" : '#';
        $buttonHtml = '<br><a href="' . $buttonUrl . '" style="display: block; margin-top: 10px; border: 1px solid #ffff; padding: 8px 16px; width: fit-content; border-radius: 0.375rem; color: #ffff; font-size: 14px;">Pay Now</a>';

        return new HtmlString($text . $buttonHtml);
    }

    public function getLabel(): string
    {
        $label = $this->evaluate($this->label) ?? (string) Str::of($this->getName())
            ->beforeLast('.')
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();

        return $this->shouldTranslateLabel ? __($label) : $label;
    }
}
