<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Transaction;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use League\Flysystem\UnableToCheckFileExistence;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Notifications\Notification;

class OrderSubscription extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [];
    public Transaction $transaction;
    public Product $product;
    public $jumlah = 1;
    public $harga_jual;

    public function mount(Transaction $transaction): void
    {
        // Set transaction dan product
        $this->transaction = $transaction;
        $this->product = $transaction->product;

        // Pastikan product ada
        if ($this->product) {
            $this->harga_jual = $this->product->harga_jual;

            $this->form->fill([
                'nama' => $this->product->nama,
                'harga_jual' => $this->harga_jual,
                'jumlah' => $this->jumlah,
                'user_id' => Auth::user()->id,
                'jenis_transaksi' => 1,
                'status' => 0,
                'bukti_transaksi' => null
            ]);
        }
    }

    public function updatedJumlah($value)
    {
        $this->jumlah = $value;
        $this->updateHargaJual();
    }

    public function updateHargaJual()
    {
        $this->harga_jual = $this->product->harga_jual * $this->jumlah;
        $this->form->fill([
            'nama' => $this->product->nama,
            'harga_jual' => $this->harga_jual,
            'jumlah' => $this->jumlah,
            'user_id' => Auth::user()->id,
            'jenis_transaksi' => 1,
            'status' => 0,
            'bukti_transaksi' => null
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('nama')
                        ->label('Nama Produk')
                        ->extraInputAttributes(['readonly' => true])
                        ->required(),
                    TextInput::make('jumlah')
                        ->label('Jumlah')
                        ->default($this->jumlah)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state) => $this->updatedJumlah($state)),
                    TextInput::make('harga_jual')
                        ->label('Harga')
                        ->default($this->harga_jual)
                        ->reactive()
                        ->extraInputAttributes(['readonly' => true]),
                    FileUpload::make('bukti_transaksi')
                        ->image()
                        ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                            try {
                                if (!$file->exists()) {
                                    return null;
                                }
                            } catch (UnableToCheckFileExistence $exception) {
                                return null;
                            }

                            return Cloudinary::upload($file->getRealPath())->getSecurePath();
                        })
                        ->reactive(),
                ]),
                Hidden::make('user_id')
                    ->default(Auth::user()->id),
                Hidden::make('jenis_transaksi')
                    ->default(1),
                Hidden::make('status')
                    ->default(0),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        Transaction::create([
            'product_uuid' => $this->product->uuid,
            'user_id' => Auth::user()->id,
            'jenis_transaksi' => 1,
            'status' => 0,
            'jumlah' => $this->jumlah,
            'harga' => $this->harga_jual,
            'bukti_transaksi' => $this->data['bukti_transaksi'] ?? null,
        ]);

        Notification::make()
            ->title('Order berhasil')
            ->success()
            ->send();

        redirect(route('filament.user.resources.transactions.index'));
    }

    public function render(): View
    {
        return view('livewire.orderSubscription', [
            'data' => $this->data,
            'product' => $this->product,
        ]);
    }
}
