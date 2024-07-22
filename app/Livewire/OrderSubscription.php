<?php
namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use League\Flysystem\UnableToCheckFileExistence;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class OrderSubscription extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [];
    public Product $product;
    public $jumlah = 1;
    public $harga_jual;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->harga_jual = $this->product->harga_jual;

        $this->form->fill([
            'nama' => $this->product->nama,
            'harga_jual' => $this->harga_jual,
            'jumlah' => $this->jumlah,
            'user_id' => Auth::user()->id,
            'jenis_transaksi' => 1,
            'status' => 0,
            'bukti_transaksi' => ''
        ]);
    }

    public function updatedJumlah($value)
    {
        $this->jumlah = $value;
        $this->updateHargaJual();
    }

    public function updateHargaJual()
    {
        // Perbarui hanya harga_jual tanpa mengisi ulang seluruh form
        $this->harga_jual = $this->product->harga_jual * $this->jumlah;
        $this->form->fill([
            'harga_jual' => $this->harga_jual,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([ // Mengatur grid dengan 2 kolom
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
                TextInput::make('user_id')
                    ->hidden()
                    ->default(Auth::user()->id),
                TextInput::make('jenis_transaksi')
                    ->hidden()
                    ->default(1),
                TextInput::make('status')
                    ->hidden()
                    ->default(0),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        dd($this->form->getState());
    }

    public function render(): View
    {
        return view('livewire.orderSubscription');
    }
}
