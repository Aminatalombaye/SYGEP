<div id="sidebar" class="c-sidebar c-sidebar-fixed c-sidebar-lg-show">

    <div class="c-sidebar-brand sy-brand">
        <a class="c-sidebar-brand-full" href="{{ route("admin.home") }}" aria-label="SYGEP – tableau de bord">
            <img src="{{ asset('img/logo.png') }}" alt="SYGEP" class="sy-brand-logo">
        </a>
        <a class="c-sidebar-brand-minimized" href="{{ route("admin.home") }}" aria-label="SYGEP">
            <img src="{{ asset('img/favicon/favicon-32.png') }}" alt="SYGEP" class="sy-brand-mark">
        </a>
    </div>

    <ul class="c-sidebar-nav">
        
        <li class="c-sidebar-nav-item">
            <a href="{{ route("admin.home") }}" class="c-sidebar-nav-link {{ request()->routeIs('admin.home') ? 'c-active' : '' }}">
                <i class="bi bi-speedometer2 c-sidebar-nav-icon">

                </i>
                {{ trans('global.dashboard') }}
            </a>
        </li>
        @can('user_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/permissions*") ? "c-show" : "" }} {{ request()->is("admin/roles*") ? "c-show" : "" }} {{ request()->is("admin/users*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="bi bi-person-gear c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.userManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('permission_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.permissions.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/permissions") || request()->is("admin/permissions/*") ? "c-active" : "" }}">
                                <i class="bi bi-shield-lock c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.permission.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('role_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.roles.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/roles") || request()->is("admin/roles/*") ? "c-active" : "" }}">
                                <i class="bi bi-person-badge c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.role.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('user_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.users.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/users") || request()->is("admin/users/*") ? "c-active" : "" }}">
                                <i class="bi bi-people c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.user.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('asset_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/asset-categories*") ? "c-show" : "" }} {{ request()->is("admin/asset-locations*") ? "c-show" : "" }} {{ request()->is("admin/asset-statuses*") ? "c-show" : "" }} {{ request()->is("admin/assets*") ? "c-show" : "" }} {{ request()->is("admin/assets-histories*") ? "c-show" : "" }} {{ request()->is("admin/assignments*") ? "c-show" : "" }} {{ request()->is("admin/inventaires*") ? "c-show" : "" }} {{ request()->is("admin/suppliers*") ? "c-show" : "" }} {{ request()->is("admin/bons*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="bi bi-box-seam c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.assetManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
    
                @can('agent_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.agents.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/agents") || request()->is("admin/agents/*") ? "c-active" : "" }}">
                                <i class="bi bi-person-vcard c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.agent.title') }}
                            </a>
                        </li>
                    @endcan    
                @can('asset_category_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.asset-categories.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/asset-categories") || request()->is("admin/asset-categories/*") ? "c-active" : "" }}">
                                <i class="bi bi-tags c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.assetCategory.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('service_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.services.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/services") || request()->is("admin/services/*") ? "c-active" : "" }}">
                                <i class="bi bi-diagram-3 c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.service.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('asset_location_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.asset-locations.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/asset-locations") || request()->is("admin/asset-locations/*") ? "c-active" : "" }}">
                                <i class="bi bi-geo-alt c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.assetLocation.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('asset_status_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.asset-statuses.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/asset-statuses") || request()->is("admin/asset-statuses/*") ? "c-active" : "" }}">
                                <i class="bi bi-toggles c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.assetStatus.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('asset_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.assets.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/assets") || request()->is("admin/assets/*") ? "c-active" : "" }}">
                                <i class="bi bi-pc-display c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.asset.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('asset_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.qr.scanner") }}" class="c-sidebar-nav-link {{ request()->routeIs('admin.qr.*') ? "c-active" : "" }}">
                                <i class="bi bi-qr-code-scan c-sidebar-nav-icon"></i>
                                Scanner un QR code
                            </a>
                        </li>
                    @endcan
                    @can('assets_history_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.assets-histories.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/assets-histories") || request()->is("admin/assets-histories/*") ? "c-active" : "" }}">
                                <i class="bi bi-clock-history c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.assetsHistory.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('assignment_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.assignments.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/assignments") || request()->is("admin/assignments/*") ? "c-active" : "" }}">
                                <i class="bi bi-person-check c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.assignment.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('inventaire_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.inventaires.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/inventaires") || request()->is("admin/inventaires/*") ? "c-active" : "" }}">
                                <i class="bi bi-clipboard-check c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.inventaire.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('supplier_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.suppliers.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/suppliers") || request()->is("admin/suppliers/*") ? "c-active" : "" }}">
                                <i class="bi bi-truck c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.supplier.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('bon_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.bons.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/bons") || request()->is("admin/bons/*") ? "c-active" : "" }}">
                                <i class="bi bi-receipt c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.bon.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('stock_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/stock-*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="bi bi-box2 c-sidebar-nav-icon"></i>
                    {{ trans('cruds.stockManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('stock_item_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.stock-items.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/stock-items") || request()->is("admin/stock-items/*") ? "c-active" : "" }}">
                                <i class="bi bi-boxes c-sidebar-nav-icon"></i>
                                {{ trans('cruds.stockItem.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('stock_movement_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.stock-movements.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/stock-movements") || request()->is("admin/stock-movements/*") ? "c-active" : "" }}">
                                <i class="bi bi-arrow-left-right c-sidebar-nav-icon"></i>
                                {{ trans('cruds.stockMovement.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('infrastructure_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/infrastructures*") ? "c-show" : "" }} {{ request()->is("admin/projects*") ? "c-show" : "" }} {{ request()->is("admin/reports*") ? "c-show" : "" }} {{ request()->is("admin/chef-projets*") ? "c-show" : "" }} {{ request()->is("admin/intervenants*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="bi bi-buildings c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.infrastructureManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('infrastructure_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.infrastructures.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/infrastructures") || request()->is("admin/infrastructures/*") ? "c-active" : "" }}">
                                <i class="bi bi-building c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.infrastructure.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('project_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.projects.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/projects") || request()->is("admin/projects/*") ? "c-active" : "" }}">
                                <i class="bi bi-kanban c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.project.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('intervenant_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.intervenants.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/intervenants") || request()->is("admin/intervenants/*") ? "c-active" : "" }}">
                                <i class="bi bi-people c-sidebar-nav-icon"></i>
                                {{ trans('cruds.intervenant.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('report_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.reports.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/reports") || request()->is("admin/reports/*") ? "c-active" : "" }}">
                                <i class="bi bi-file-earmark-bar-graph c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.report.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('chef_projet_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.chef-projets.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/chef-projets") || request()->is("admin/chef-projets/*") ? "c-active" : "" }}">
                                <i class="bi bi-person-workspace c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.chefProjet.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('task_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/task-statuses*") ? "c-show" : "" }} {{ request()->is("admin/task-tags*") ? "c-show" : "" }} {{ request()->is("admin/tasks*") ? "c-show" : "" }} {{ request()->is("admin/tasks-calendars*") ? "c-show" : "" }} {{ request()->is("admin/maintenance-requests*") ? "c-show" : "" }} {{ request()->is("admin/maintenance-plans*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="bi bi-list-check c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.taskManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('task_status_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.task-statuses.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/task-statuses") || request()->is("admin/task-statuses/*") ? "c-active" : "" }}">
                                <i class="bi bi-flag c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.taskStatus.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('task_tag_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.task-tags.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/task-tags") || request()->is("admin/task-tags/*") ? "c-active" : "" }}">
                                <i class="bi bi-tag c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.taskTag.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('task_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.tasks.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/tasks") || request()->is("admin/tasks/*") ? "c-active" : "" }}">
                                <i class="bi bi-check2-square c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.task.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('tasks_calendar_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.tasks-calendars.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/tasks-calendars") || request()->is("admin/tasks-calendars/*") ? "c-active" : "" }}">
                                <i class="bi bi-calendar3 c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.tasksCalendar.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('maintenance_request_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.maintenance-requests.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/maintenance-requests") || request()->is("admin/maintenance-requests/*") ? "c-active" : "" }}">
                                <i class="bi bi-tools c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.maintenanceRequest.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('maintenance_plan_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.maintenance-plans.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/maintenance-plans") || request()->is("admin/maintenance-plans/*") ? "c-active" : "" }}">
                                <i class="bi bi-arrow-repeat c-sidebar-nav-icon"></i>
                                {{ trans('cruds.maintenancePlan.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('periodic_report_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.periodic-reports.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/rapports-periodiques*") ? "c-active" : "" }}">
                    <i class="bi bi-file-earmark-bar-graph c-sidebar-nav-icon"></i>
                    {{ trans('cruds.periodicReport.title') }}
                </a>
            </li>
        @endcan
        @can('user_alert_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.user-alerts.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/user-alerts") || request()->is("admin/user-alerts/*") ? "c-active" : "" }}">
                    <i class="bi bi-bell c-sidebar-nav-icon">

                    </i>
                    Envoi de notifications
                </a>
            </li>
        @endcan
        @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
            @can('profile_password_edit')
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link {{ request()->is('profile/password') || request()->is('profile/password/*') ? 'c-active' : '' }}" href="{{ route('profile.password.edit') }}">
                        <i class="bi bi-key c-sidebar-nav-icon">
                        </i>
                        {{ trans('global.change_password') }}
                    </a>
                </li>
            @endcan
        @endif
        <li class="c-sidebar-nav-item">
            <a href="#" class="c-sidebar-nav-link" data-toggle="modal" data-target="#logoutModal">
                <i class="bi bi-box-arrow-right c-sidebar-nav-icon">

                </i>
                {{ trans('global.logout') }}
            </a>
        </li>
    </ul>

</div>