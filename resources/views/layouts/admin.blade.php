<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SYGEP – Espace de gestion</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/favicon/apple-touch-icon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-dt@1.13.11/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.11/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-buttons-dt@2.4.2/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-select-dt@1.7.0/css/select.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/eonasdan-bootstrap-datetimepicker@4.17.49/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@3.4.0/dist/css/coreui.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/css/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/sygep-admin.css') }}?v=1" rel="stylesheet" />

    @yield('styles')
</head>


<body class="c-app">
    @include('partials.menu')
    <div class="c-wrapper">
        <header class="c-header c-header-fixed sy-header">
            <button class="c-header-toggler c-class-toggler d-lg-none" type="button" data-target="#sidebar" data-class="c-sidebar-show" aria-label="Ouvrir le menu">
                <i class="bi bi-list"></i>
            </button>
            <button class="c-header-toggler c-class-toggler d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true" aria-label="Réduire le menu">
                <i class="bi bi-layout-sidebar"></i>
            </button>

            <div class="sy-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <select class="searchable-field form-control"></select>
            </div>

            <ul class="c-header-nav ml-auto sy-header-right">

                <li class="c-header-nav-item dropdown notifications-menu">
                    <a href="#" class="c-header-nav-link sy-icon-btn" data-toggle="dropdown" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        @php($alertsCount = \Auth::user()->userUserAlerts()->where('read', false)->count())
                        @if($alertsCount > 0)
                            <span class="sy-dot">{{ $alertsCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-right sy-dropdown sy-notifs">
                        <div class="sy-dropdown-title sy-notifs-head">
                            <span>Notifications</span>
                            @if($alertsCount > 0)
                                <form method="POST" action="{{ route('admin.notifications.readAll') }}">
                                    @csrf
                                    <button type="submit" class="sy-link-btn">Tout marquer comme lu</button>
                                </form>
                            @endif
                        </div>
                        @php($alerts = \Auth::user()->userUserAlerts()->withPivot('read')->orderByDesc('user_alerts.created_at')->limit(8)->get())
                        @forelse($alerts as $alert)
                            <a class="dropdown-item sy-notif {{ $alert->pivot->read ? '' : 'is-unread' }}"
                               href="{{ route('admin.notifications.open', $alert) }}"
                               @if($alert->alert_link && ! $alert->isInternal()) target="_blank" rel="noopener noreferrer" @endif>
                                <i class="bi {{ $alert->icon }}"></i>
                                <span>
                                    <span class="sy-notif-text">{{ $alert->alert_text }}</span>
                                    <span class="sy-notif-date">{{ $alert->created_at?->diffForHumans() }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="sy-dropdown-empty"><i class="bi bi-bell-slash"></i> Aucune notification.</div>
                        @endforelse
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.notifications.index') }}"><i class="bi bi-list-ul"></i> Voir toutes les notifications</a>
                        @can('contact_message_access')
                            @php($unreadContacts = \Illuminate\Support\Facades\Schema::hasTable('contact_messages') ? \App\Models\ContactMessage::whereNull('read_at')->count() : 0)
                            <a class="dropdown-item" href="{{ route('admin.contact-messages.index') }}">
                                <i class="bi bi-envelope-open"></i> Messages de contact
                                @if($unreadContacts)<span class="sy-count">{{ $unreadContacts }}</span>@endif
                            </a>
                        @endcan
                    </div>
                </li>

                <li class="c-header-nav-item dropdown">
                    @php($me = auth()->user())
                    <a href="#" class="c-header-nav-link sy-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sy-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($me->name, 0, 1)) }}</span>
                        <span class="sy-user-name d-md-down-none">{{ $me->name }}</span>
                        <i class="bi bi-chevron-down d-md-down-none"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right sy-dropdown">
                        <div class="sy-dropdown-title">
                            {{ $me->name }}
                            <small>{{ $me->email }}</small>
                            <small>{{ $me->roles->pluck('title')->implode(', ') }}{{ \App\Support\Perimetre::isLocal() ? ' · '.\App\Support\Perimetre::label() : '' }}</small>
                        </div>
                        <a class="dropdown-item" href="{{ route('admin.notifications.index') }}"><i class="bi bi-bell"></i> Mes notifications @if($alertsCount)<span class="sy-count">{{ $alertsCount }}</span>@endif</a>
                        <a class="dropdown-item" href="{{ route('welcome') }}"><i class="bi bi-house"></i> Site public</a>
                        @can('profile_password_edit')
                            <a class="dropdown-item" href="{{ route('profile.password.edit') }}"><i class="bi bi-person-circle"></i> Profil</a>
                        @endcan
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#logoutModal"><i class="bi bi-box-arrow-right"></i> {{ trans('global.logout') }}</a>
                    </div>
                </li>
            </ul>
        </header>

        <div class="c-body">
            <main class="c-main">


                <div class="container-fluid">
                    @if(session('message'))
                        <div class="row mb-2">
                            <div class="col-lg-12">
                                <div class="alert alert-success" role="alert">{{ session('message') }}</div>
                            </div>
                        </div>
                    @endif
                    @if($errors->count() > 0)
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')

                </div>


            </main>

            <footer class="sy-footer">© {{ date('Y') }} SYGEP</footer>
            
            <form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>

        </div>
    </div>
    <div class="modal fade sy-modal" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <span class="sy-modal-icon"><i class="bi bi-box-arrow-right"></i></span>
                    <h2 id="logoutModalTitle">Se déconnecter ?</h2>
                    <p>Vous devrez saisir à nouveau vos identifiants pour accéder à l'espace de gestion.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('logoutform').submit();"><i class="bi bi-box-arrow-right"></i> Se déconnecter</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@3.4.0/dist/js/coreui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.11/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs4@1.13.11/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.10/build/pdfmake.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdfmake@0.2.10/build/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-select@1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/eonasdan-bootstrap-datetimepicker@4.17.49/build/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
    <script src="{{ asset('js/sygep-charts.js') }}"></script>
    <script>
        $(function() {
  let copyButtonTrans = '{{ trans('global.datatables.copy') }}'
  let csvButtonTrans = '{{ trans('global.datatables.csv') }}'
  let excelButtonTrans = '{{ trans('global.datatables.excel') }}'
  let pdfButtonTrans = '{{ trans('global.datatables.pdf') }}'
  let printButtonTrans = '{{ trans('global.datatables.print') }}'
  let colvisButtonTrans = '{{ trans('global.datatables.colvis') }}'
  let selectAllButtonTrans = '{{ trans('global.select_all') }}'
  let selectNoneButtonTrans = '{{ trans('global.deselect_all') }}'

  $.extend(true, $.fn.dataTable.Buttons.defaults.dom.button, { className: 'btn' })
  $.extend(true, $.fn.dataTable.defaults, {
    language: {
      url: 'https://cdn.jsdelivr.net/npm/datatables.net-plugins@1.13.6/i18n/fr-FR.json'
    },
    columnDefs: [{
        orderable: false,
        className: 'select-checkbox',
        targets: 0
    }, {
        orderable: false,
        searchable: false,
        targets: -1
    }],
    select: {
      style:    'multi+shift',
      selector: 'td:first-child'
    },
    order: [],
    scrollX: true,
    pageLength: 100,
    dom: '<"dt-toolbar"<"dt-toolbar-left"B><"dt-toolbar-right"lf>>rt<"dt-footer"ip>',
    buttons: [
      {
        extend: 'selectAll',
        className: 'btn-default',
        text: '<i class="bi bi-check2-all"></i> ' + selectAllButtonTrans,
        action: function(e, dt) {
          e.preventDefault()
          dt.rows().deselect();
          dt.rows({ search: 'applied' }).select();
        }
      },
      {
        extend: 'selectNone',
        className: 'btn-default',
        text: '<i class="bi bi-x-lg"></i> ' + selectNoneButtonTrans
      },
      {
        extend: 'collection',
        className: 'btn-default',
        text: '<i class="bi bi-download"></i> Exporter',
        buttons: [
          { extend: 'copy',  text: '<i class="bi bi-clipboard"></i> ' + copyButtonTrans,  exportOptions: { columns: ':visible' } },
          { extend: 'csv',   text: '<i class="bi bi-filetype-csv"></i> ' + csvButtonTrans,   exportOptions: { columns: ':visible' } },
          { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> ' + excelButtonTrans, exportOptions: { columns: ':visible' } },
          { extend: 'pdf',   text: '<i class="bi bi-file-earmark-pdf"></i> ' + pdfButtonTrans,   exportOptions: { columns: ':visible' } },
          { extend: 'print', text: '<i class="bi bi-printer"></i> ' + printButtonTrans, exportOptions: { columns: ':visible' } }
        ]
      },
      {
        extend: 'colvis',
        className: 'btn-default',
        text: '<i class="bi bi-layout-three-columns"></i> Colonnes'
      }
    ]
  });

  $.fn.dataTable.ext.classes.sPageButton = '';

  $(document).on('init.dt', function (e, settings) {
    $(settings.nTableWrapper).find('.dt-buttons .btn-danger').each(function () {
      if (!$(this).find('.bi').length) { $(this).prepend('<i class="bi bi-trash3"></i> '); }
    });
  });
});

    </script>
    <script>
        $(document).ready(function() {
    $('.searchable-field').select2({
        minimumInputLength: 3,
        ajax: {
            url: '{{ route("admin.globalSearch") }}',
            dataType: 'json',
            type: 'GET',
            delay: 200,
            data: function (term) {
                return {
                    search: term
                };
            },
            results: function (data) {
                return {
                    data
                };
            }
        },
        escapeMarkup: function (markup) { return markup; },
        templateResult: formatItem,
        templateSelection: formatItemSelection,
        placeholder : '{{ trans('global.search') }}...',
        language: {
            inputTooShort: function(args) {
                var remainingChars = args.minimum - args.input.length;
                var translation = '{{ trans('global.search_input_too_short') }}';

                return translation.replace(':count', remainingChars);
            },
            errorLoading: function() {
                return '{{ trans('global.results_could_not_be_loaded') }}';
            },
            searching: function() {
                return '{{ trans('global.searching') }}';
            },
            noResults: function() {
                return '{{ trans('global.no_results') }}';
            },
        }

    });
    function formatItem (item) {
        if (item.loading) {
            return '{{ trans('global.searching') }}...';
        }
        var markup = "<div class='searchable-link' href='" + item.url + "'>";
        markup += "<div class='searchable-title'>" + item.model + "</div>";
        $.each(item.fields, function(key, field) {
            markup += "<div class='searchable-fields'>" + item.fields_formated[field] + " : " + item[field] + "</div>";
        });
        markup += "</div>";

        return markup;
    }

    function formatItemSelection (item) {
        if (!item.model) {
            return '{{ trans('global.search') }}...';
        }
        return item.model;
    }
    $(document).delegate('.searchable-link', 'click', function() {
        var url = $(this).attr('href');
        window.location = url;
    });
});

    </script>
    @yield('scripts')
</body>

</html>