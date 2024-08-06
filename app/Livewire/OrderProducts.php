<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Notifications\Notification;

class OrderProducts extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [];
    public Product $product;
    public $jumlah = 0;
    public $harga_jual;

    public function mount(Product $product): void
    {
        $this->product = $product;

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
            'bukti_transaksi' => $this->data['bukti_transaksi'] ?? null
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
                        ->extraInputAttributes(['readonly' => true]),
                    FileUpload::make('bukti_transaksi')
                        ->image()
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): ?string {
                            if ($file->exists()) {
                                try {
                                    // Ensure we return a string path from Cloudinary
                                    return Cloudinary::upload($file->getRealPath())->getSecurePath();
                                } catch (\Exception $e) {
                                    // Handle upload exceptions
                                    return null;
                                }
                            }
                            return null;
                        })
                        ->reactive()
                        ->afterStateUpdated(fn($state) => $this->data['bukti_transaksi'] = $state),
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
        try {
            // Ensure bukti_transaksi is properly set
            $buktiTransaksi = is_array($this->data['bukti_transaksi']) ? null : $this->data['bukti_transaksi'];

            Transaction::create([
                'product_uuid' => $this->product->uuid,
                'user_id' => Auth::user()->id,
                'jenis_transaksi' => $this->data['jenis_transaksi'],
                'status' => $this->data['status'],
                'jumlah' => $this->data['jumlah'],
                'harga' => $this->data['harga_jual'],
                'bukti_transaksi' => $buktiTransaksi,
            ]);

            Notification::make()
                ->title('Order berhasil')
                ->success()
                ->send();

            redirect(route('filament.user.resources.products.index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Order gagal')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.order-products');
    }
}
