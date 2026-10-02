use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Ops\DashboardController as OpsDashboardController;
use App\Http\Controllers\Ops\ReportController as OpsReportController;
use App\Http\Controllers\ShipmentController;

/*
 |--------------------------------------------------------------------------
 | Web Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register web routes for your application. These
 | routes are loaded by the ServiceProvider within a group which
 | contains the "web" middleware group. Now go build something amazing!
 */

// Public routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Customer Portal routes
Route::prefix('/portal')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('portal.dashboard');
    Route::get('/track', [PortalDashboardController::class, 'track'])->name('portal.track');
    Route::get('/history', [PortalDashboardController::class, 'history'])->name('portal.history');
    Route::get('/search', [PortalDashboardController::class, 'searchForm'])->name('portal.search.form');
    Route::get('/shipments/search', [ShipmentController::class, 'search'])->name('portal.shipments.search');
    Route::get('/reports', [PortalDashboardController::class, 'reports'])->name('portal.reports');
});

// Operations Console routes
Route::prefix('/ops')->group(function () {
    Route::get('/dashboard', [OpsDashboardController::class, 'index'])->name('ops.dashboard');
    Route::get('/investigation/{tracking_number}', [OpsDashboardController::class, 'investigation'])->name('ops.investigation');
    Route::get('/reports', [OpsReportController::class, 'queue'])->name('ops.reports');
    Route::get('/reports/create', [OpsReportController::class, 'createForm'])->name('ops.reports.create');
    Route::post('/reports', [OpsReportController::class, 'storeFilter'])->name('ops.reports.store');
    Route::get('/reports/generate', [OpsReportController::class, 'generate'])->name('ops.reports.generate');
});

// Root redirect
Route::get('/', function () {
    return redirect('/login');
})->name('home');