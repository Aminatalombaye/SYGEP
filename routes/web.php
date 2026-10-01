<?php

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.home');
    }
    return view('welcome');
})->name('welcome');

Route::get('/contact', 'ContactController@show')->name('contact');
Route::post('/contact', 'ContactController@store')->name('contact.store')->middleware('throttle:5,1');

Route::get('/q/{code}', 'QrController@scan')->name('qr.scan')->middleware('throttle:60,1');

Auth::routes();

Route::get('/home', function () {
    return redirect()->route('admin.home');
})->name('home');

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Agents
    Route::delete('agents/destroy', 'AgentController@massDestroy')->name('agents.massDestroy');
    Route::delete('agents/{agent}', 'AgentController@destroy')->name('agents.destroy'); 
    Route::resource('agents', 'AgentController');

    // Asset Category
    Route::delete('asset-categories/destroy', 'AssetCategoryController@massDestroy')->name('asset-categories.massDestroy');
    Route::resource('asset-categories', 'AssetCategoryController');
    
    // Service
    Route::delete('services/destroy', 'ServiceController@massDestroy')->name('services.massDestroy');
    Route::resource('services', 'ServiceController');

    // Asset Location
    Route::delete('asset-locations/destroy', 'AssetLocationController@massDestroy')->name('asset-locations.massDestroy');
    Route::resource('asset-locations', 'AssetLocationController');

    // Asset Status
    Route::delete('asset-statuses/destroy', 'AssetStatusController@massDestroy')->name('asset-statuses.massDestroy');
    Route::resource('asset-statuses', 'AssetStatusController');

    // Asset
    Route::delete('assets/destroy', 'AssetController@massDestroy')->name('assets.massDestroy');
    Route::post('assets/media', 'AssetController@storeMedia')->name('assets.storeMedia');
    Route::post('assets/ckmedia', 'AssetController@storeCKEditorImages')->name('assets.storeCKEditorImages');
    Route::resource('assets', 'AssetController');

    // Assets History
    Route::resource('assets-histories', 'AssetsHistoryController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Task Status
    Route::delete('task-statuses/destroy', 'TaskStatusController@massDestroy')->name('task-statuses.massDestroy');
    Route::resource('task-statuses', 'TaskStatusController');

    // Task Tag
    Route::delete('task-tags/destroy', 'TaskTagController@massDestroy')->name('task-tags.massDestroy');
    Route::resource('task-tags', 'TaskTagController');

    // Task
    Route::delete('tasks/destroy', 'TaskController@massDestroy')->name('tasks.massDestroy');
    Route::post('tasks/media', 'TaskController@storeMedia')->name('tasks.storeMedia');
    Route::post('tasks/ckmedia', 'TaskController@storeCKEditorImages')->name('tasks.storeCKEditorImages');
    Route::resource('tasks', 'TaskController');

    // Tasks Calendar
    Route::resource('tasks-calendars', 'TasksCalendarController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Assignment
    Route::delete('assignments/destroy', 'AssignmentController@massDestroy')->name('assignments.massDestroy');
    Route::get('assignments/{assignment}/restitution', 'AssignmentController@returnForm')->name('assignments.return');
    Route::post('assignments/{assignment}/restitution', 'AssignmentController@returnStore')->name('assignments.return.store');
    Route::get('assignments/{assignment}/bon', 'AssignmentController@bon')->name('assignments.print');
    Route::get('assets/{asset}/transfert', 'AssignmentController@transferForm')->name('assets.transfer');
    Route::get('scanner', 'QrController@scanner')->name('qr.scanner');
    Route::post('scanner', 'QrController@lookup')->name('qr.lookup');
    Route::get('assets/{asset}/qr.svg', 'QrController@svg')->name('assets.qr');
    Route::get('assets/{asset}/etiquette', 'QrController@label')->name('assets.label');
    Route::post('assets/{asset}/transfert', 'AssignmentController@transfer')->name('assets.transfer.store');
    Route::resource('assignments', 'AssignmentController');

    // Inventaire
    Route::delete('inventaires/destroy', 'InventaireController@massDestroy')->name('inventaires.massDestroy');
    Route::post('inventaires/{inventaire}/demarrer', 'InventaireController@start')->name('inventaires.start');
    Route::get('inventaires/{inventaire}/scanner', 'InventaireController@scan')->name('inventaires.scan');
    Route::post('inventaires/{inventaire}/controle', 'InventaireController@record')->name('inventaires.record');
    Route::delete('inventaires/{inventaire}/controle/{asset}', 'InventaireController@undo')->name('inventaires.undo');
    Route::post('inventaires/{inventaire}/cloturer', 'InventaireController@close')->name('inventaires.close');
    Route::get('inventaires/{inventaire}/proces-verbal', 'InventaireController@report')->name('inventaires.report');
    Route::resource('inventaires', 'InventaireController');

    // Fournisseur
    Route::delete('suppliers/destroy', 'SupplierController@massDestroy')->name('suppliers.massDestroy');
    Route::resource('suppliers', 'SupplierController');

    // Maintenance Requests
    Route::delete('maintenance-requests/destroy', 'MaintenanceRequestsController@massDestroy')->name('maintenance-requests.massDestroy');
    Route::post('maintenance-requests/{maintenance_request}/decision', 'MaintenanceRequestsController@decide')->name('maintenance-requests.decide');
    Route::post('maintenance-requests/{maintenance_request}/planifier', 'MaintenanceRequestsController@plan')->name('maintenance-requests.plan');
    Route::post('maintenance-requests/{maintenance_request}/demarrer', 'MaintenanceRequestsController@start')->name('maintenance-requests.start');
    Route::post('maintenance-requests/{maintenance_request}/cloturer', 'MaintenanceRequestsController@complete')->name('maintenance-requests.complete');
    Route::resource('maintenance-requests', 'MaintenanceRequestsController');

    // Maintenance préventive
    Route::post('maintenance-plans/{maintenance_plan}/generer', 'MaintenancePlanController@generate')->name('maintenance-plans.generate');
    Route::resource('maintenance-plans', 'MaintenancePlanController');

    // Stock des consommables
    Route::delete('stock-items/destroy', 'StockItemController@massDestroy')->name('stock-items.massDestroy');
    Route::resource('stock-items', 'StockItemController');
    Route::resource('stock-movements', 'StockMovementController', ['only' => ['index', 'create', 'store']]);

    // Rapports périodiques
    Route::get('rapports-periodiques', 'PeriodicReportController@index')->name('periodic-reports.index');
    Route::get('rapports-periodiques/edition', 'PeriodicReportController@show')->name('periodic-reports.show');

    // Infrastructure
    Route::delete('infrastructures/destroy', 'InfrastructureController@massDestroy')->name('infrastructures.massDestroy');
    Route::resource('infrastructures', 'InfrastructureController');

    // Project
    Route::delete('projects/destroy', 'ProjectController@massDestroy')->name('projects.massDestroy');
    Route::post('projects/{project}/jalons', 'ProjectController@storeMilestone')->name('projects.milestones.store');
    Route::post('projects/{project}/jalons/{milestone}/basculer', 'ProjectController@toggleMilestone')->name('projects.milestones.toggle');
    Route::delete('projects/{project}/jalons/{milestone}', 'ProjectController@destroyMilestone')->name('projects.milestones.destroy');
    Route::post('projects/{project}/intervenants', 'ProjectController@attachIntervenant')->name('projects.intervenants.attach');
    Route::delete('projects/{project}/intervenants/{intervenant}', 'ProjectController@detachIntervenant')->name('projects.intervenants.detach');
    Route::resource('projects', 'ProjectController');

    // Intervenants (entreprises, bureaux d'études, contrôle…)
    Route::delete('intervenants/destroy', 'IntervenantController@massDestroy')->name('intervenants.massDestroy');
    Route::resource('intervenants', 'IntervenantController');

    // Report
    Route::delete('reports/destroy', 'ReportController@massDestroy')->name('reports.massDestroy');
    Route::post('reports/media', 'ReportController@storeMedia')->name('reports.storeMedia');
    Route::post('reports/ckmedia', 'ReportController@storeCKEditorImages')->name('reports.storeCKEditorImages');
    Route::resource('reports', 'ReportController');

    // Chef Projet
    Route::delete('chef-projets/destroy', 'ChefProjetController@massDestroy')->name('chef-projets.massDestroy');
    Route::resource('chef-projets', 'ChefProjetController');

    // User Alerts
    Route::delete('user-alerts/destroy', 'UserAlertsController@massDestroy')->name('user-alerts.massDestroy');
    Route::get('user-alerts/read', 'UserAlertsController@read');
    Route::get('notifications', 'NotificationController@index')->name('notifications.index');
    Route::get('notifications/{userAlert}/ouvrir', 'NotificationController@open')->name('notifications.open');
    Route::post('notifications/tout-lu', 'NotificationController@markAllRead')->name('notifications.readAll');
    Route::resource('user-alerts', 'UserAlertsController', ['except' => ['edit', 'update']]);

    // Messages de contact
    Route::delete('contact-messages/destroy', 'ContactMessageController@massDestroy')->name('contact-messages.massDestroy');
    Route::resource('contact-messages', 'ContactMessageController', ['only' => ['index', 'show', 'destroy']]);

    // Bon
    Route::delete('bons/destroy', 'BonController@massDestroy')->name('bons.massDestroy');
    Route::resource('bons', 'BonController');

    // Global Search
    Route::get('global-search', 'GlobalSearchController@search')->name('globalSearch');
});

Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});