use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the ServiceProvider within a group which
| contains the "web" middleware group. Now go build something amazing!
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/shipments/search', [App\Http\Controllers\ShipmentController::class, 'search'])
    ->name('shipments.search');

Route::get('/shipments', [App\Http\Controllers\ShipmentController::class, 'index'])
    ->name('shipments.index');