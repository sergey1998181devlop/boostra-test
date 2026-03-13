<!DOCTYPE html>
<html lang="en">

    <head>
        <base href="{$config->root_url}/" />

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="">
        <!-- Favicon icon -->
        <link rel="icon" type="image/png" sizes="16x16" href="design/{$settings->theme|escape}/assets/images/favicon.png">
        <title>{$meta_title}</title>

        {if $canonical}
            <link rel="canonical" href="{$canonical}" />
        {/if}

        <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/css/site_variables.css?v=2" />

        <!-- Bootstrap Core CSS -->
        <link href="design/{$settings->theme|escape}/assets/plugins/bootstrap/css/bootstrap.min.css?v=1.03" rel="stylesheet">
        <!--alerts CSS -->
        <link href="design/{$settings->theme|escape}/assets/plugins/sweetalert2/dist/sweetalert2.min.css?v=1.01" rel="stylesheet">
        <!-- You can change the theme colors from here -->
        <link href="design/{$settings->theme|escape}/css/colors/megna-dark.css?v=1.02" id="theme" rel="stylesheet">
        <!-- Custom CSS -->
        {$smarty.capture.page_styles}

        <link href="design/{$settings->theme|escape}/css/style.css?v=1.108" rel="stylesheet">
        <link href="design/manager/css/jquery-ui.min.css" rel="stylesheet">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    
    <script type="text/javascript">
        var is_developer = {if $is_developer}1{else}0{/if};
        var FRONT_URL = '{$config->front_url}';
    </script>
        <script src="https://kit-eu.voximplant.com/static/widgets/softphone/js/app.js?_ga=2.65529450.554660211.1675072098-303030313.1673862647"></script>
        <script type="module" src="design/{$settings->theme|escape}/js/apps/voximplant/voximplant.js?v=1.031"></script>

    </head>
    <style>
        .notification-badge {
            background: #ff0000;
            color: #FFFFFF;
            padding: 8px;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            position: absolute;
            line-height: 11px;
            top: 0;
            right: 0;
            font-size: 0.8em;
        }

    </style>
    <body
            class="fix-header card-no-border fix-sidebar  logo-center {if $module=='OrderView'}order-page{/if}"
            {if !empty($site_id)}data-site_id="{$site_id|escape}"{/if}
            data-ticket-sound-config='{$settings->ticket_sound_settings|json_encode|escape}'
    >
        
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
        </div>
        <!-- ============================================================== -->
        <!-- Main wrapper - style you can find in pages.scss -->
        <!-- ============================================================== -->


        <div id="main-wrapper">
            <!-- ============================================================== -->
            <!-- Topbar header - style you can find in pages.scss -->
            <!-- ============================================================== -->
            <header class="topbar">
                <nav class="navbar top-navbar navbar-expand-md navbar-light ">
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-header">
                        <a class="navbar-brand"
                        {if !in_array('discharges_reports', $manager->permissions)}
                            href="/"
                        {/if}
                           >
                            <span>
                                <img data-site_required="boostra" loading="lazy"
                                     src="design/{$settings->theme|escape}/assets/img/boostra_text_logo.png" class="light-logo" alt="homepage" />
                                <img data-site_required="soyaplace" loading="lazy" style="transform: scale(1.5)"
                                     src="design/{$settings->theme|escape}/assets/img/soyaplace_text_logo.svg" class="light-logo" alt="homepage" />
                                <img data-site_required="neomani" loading="lazy" style="transform: scale(1.5)"
                                     src="design/{$settings->theme|escape}/assets/img/neomani_text_logo.svg" class="light-logo" alt="homepage" />
                                <img data-site_required="rubl" loading="lazy" style="transform: scale(1.5)"
                                     src="design/{$settings->theme|escape}/assets/img/rubl_text_logo.svg" class="light-logo" alt="homepage" />

                            </span> </a>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-collapse">
                        <input type="hidden" class="manager-id" value="{$manager->id}">
                        <!-- ============================================================== -->
                        <!-- toggle and nav items -->
                        <!-- ============================================================== -->
                        <ul class="navbar-nav mr-auto mt-md-0 ">
                            <!-- This is  -->
                            <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                            <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="icon-arrow-left-circle"></i></a> </li>
                            <!-- ============================================================== -->
                            <!-- Comment -->
                            <!-- ============================================================== -->
                            {*}
                            <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-message"></i>
                            <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                            </a>
                            <div class="dropdown-menu mailbox animated bounceInDown">
                            <ul>
                            <li>
                            <div class="drop-title">Notifications</div>
                            </li>
                            <li>
                            <div class="message-center">
                            <!-- Message -->
                            <a href="#">
                            <div class="btn btn-danger btn-circle"><i class="fa fa-link"></i></div>
                            <div class="mail-contnet">
                            <h5>Luanch Admin</h5> <span class="mail-desc">Just see the my new admin!</span> <span class="time">9:30 AM</span> </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                            <div class="btn btn-success btn-circle"><i class="ti-calendar"></i></div>
                            <div class="mail-contnet">
                            <h5>Event today</h5> <span class="mail-desc">Just a reminder that you have event</span> <span class="time">9:10 AM</span> </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                            <div class="btn btn-info btn-circle"><i class="ti-settings"></i></div>
                            <div class="mail-contnet">
                            <h5>Settings</h5> <span class="mail-desc">You can customize this template as you want</span> <span class="time">9:08 AM</span> </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                            <div class="btn btn-primary btn-circle"><i class="ti-user"></i></div>
                            <div class="mail-contnet">
                            <h5>Pavan kumar</h5> <span class="mail-desc">Just see the my admin!</span> <span class="time">9:02 AM</span> </div>
                            </a>
                            </div>
                            </li>
                            <li>
                            <a class="nav-link text-center" href="javascript:void(0);"> <strong>Check all notifications</strong> <i class="fa fa-angle-right"></i> </a>
                            </li>
                            </ul>
                            </div>
                            </li>
                            {*}
                            <!-- ============================================================== -->
                            <!-- End Comment -->
                            <!-- ============================================================== -->
                            <!-- ============================================================== -->
                            <!-- Messages -->
                            <!-- ============================================================== -->
                            {*}
                            <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-email"></i>
                            <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                            </a>
                            <div class="dropdown-menu mailbox animated bounceInDown" aria-labelledby="2">
                            <ul>
                            <li>
                            <div class="drop-title">You have 4 new messages</div>
                            </li>
                            <li>
                            <div class="message-center">
                            <!-- Message -->
                            <a href="#">
                            <div class="user-img"> <img src="design/{$settings->theme|escape}/assets/images/users/1.jpg" alt="user" class="img-circle"> <span class="profile-status online float-right"></span> </div>
                            <div class="mail-contnet">
                            <h5>Pavan kumar</h5> <span class="mail-desc">Just see the my admin!</span> <span class="time">9:30 AM</span> </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                            <div class="user-img"> <img src="design/{$settings->theme|escape}/assets/images/users/2.jpg" alt="user" class="img-circle"> <span class="profile-status busy float-right"></span> </div>
                            <div class="mail-contnet">
                            <h5>Sonu Nigam</h5> <span class="mail-desc">I've sung a song! See you at</span> <span class="time">9:10 AM</span> </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                            <div class="user-img"> <img src="design/{$settings->theme|escape}/assets/images/users/3.jpg" alt="user" class="img-circle"> <span class="profile-status away float-right"></span> </div>
                            <div class="mail-contnet">
                            <h5>Arijit Sinh</h5> <span class="mail-desc">I am a singer!</span> <span class="time">9:08 AM</span> </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                            <div class="user-img"> <img src="design/{$settings->theme|escape}/assets/images/users/4.jpg" alt="user" class="img-circle"> <span class="profile-status offline float-right"></span> </div>
                            <div class="mail-contnet">
                            <h5>Pavan kumar</h5> <span class="mail-desc">Just see the my admin!</span> <span class="time">9:02 AM</span> </div>
                            </a>
                            </div>
                            </li>
                            <li>
                            <a class="nav-link text-center" href="javascript:void(0);"> <strong>See all e-Mails</strong> <i class="fa fa-angle-right"></i> </a>
                            </li>
                            </ul>
                            </div>
                            </li>
                            {*}
                            <!-- ============================================================== -->
                            <!-- End Messages -->
                            <!-- ============================================================== -->
                            <!-- ============================================================== -->
                            <!-- Messages -->
                            <!-- ============================================================== -->
                            {*}
                            <li class="nav-item dropdown mega-dropdown"> <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="mdi mdi-view-grid"></i></a>
                            <div class="dropdown-menu animated bounceInDown">
                            <ul class="mega-dropdown-menu row">
                            <li class="col-lg-3 col-xlg-2 mb-4">
                            <h4 class="mb-3">CAROUSEL</h4>
                            <!-- CAROUSEL -->
                            <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner" role="listbox">
                            <div class="carousel-item active">
                            <div class="container"> <img class="d-block img-fluid" src="design/{$settings->theme|escape}/assets/images/big/img1.jpg" alt="First slide"></div>
                            </div>
                            <div class="carousel-item">
                            <div class="container"><img class="d-block img-fluid" src="design/{$settings->theme|escape}/assets/images/big/img2.jpg" alt="Second slide"></div>
                            </div>
                            <div class="carousel-item">
                            <div class="container"><img class="d-block img-fluid" src="design/{$settings->theme|escape}/assets/images/big/img3.jpg" alt="Third slide"></div>
                            </div>
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev"> <span class="carousel-control-prev-icon" aria-hidden="true"></span> <span class="sr-only">Previous</span> </a>
                            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next"> <span class="carousel-control-next-icon" aria-hidden="true"></span> <span class="sr-only">Next</span> </a>
                            </div>
                            <!-- End CAROUSEL -->
                            </li>
                            <li class="col-lg-3 mb-4">
                            <h4 class="mb-3">ACCORDION</h4>
                            <!-- Accordian -->
                            <div id="accordion" class="nav-accordion" role="tablist" aria-multiselectable="true">
                            <div class="card">
                            <div class="card-header" role="tab" id="headingOne">
                            <h5 class="mb-0">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Collapsible Group Item #1
                            </a>
                            </h5> </div>
                            <div id="collapseOne" class="collapse show" role="tabpanel" aria-labelledby="headingOne">
                            <div class="card-body"> Anim pariatur cliche reprehenderit, enim eiusmod high. </div>
                            </div>
                            </div>
                            <div class="card">
                            <div class="card-header" role="tab" id="headingTwo">
                            <h5 class="mb-0">
                            <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Collapsible Group Item #2
                            </a>
                            </h5> </div>
                            <div id="collapseTwo" class="collapse" role="tabpanel" aria-labelledby="headingTwo">
                            <div class="card-body"> Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. </div>
                            </div>
                            </div>
                            <div class="card">
                            <div class="card-header" role="tab" id="headingThree">
                            <h5 class="mb-0">
                            <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Collapsible Group Item #3
                            </a>
                            </h5> </div>
                            <div id="collapseThree" class="collapse" role="tabpanel" aria-labelledby="headingThree">
                            <div class="card-body"> Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. </div>
                            </div>
                            </div>
                            </div>
                            </li>
                            <li class="col-lg-3  mb-4">
                            <h4 class="mb-3">CONTACT US</h4>
                            <!-- Contact -->
                            <form>
                            <div class="form-group">
                            <input type="text" class="form-control" id="exampleInputname1" placeholder="Enter Name"> </div>
                            <div class="form-group">
                            <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email"> </div>
                            <div class="form-group">
                            <textarea class="form-control" id="exampleTextarea" rows="3" placeholder="Message"></textarea>
                            </div>
                            <button type="submit" class="btn btn-info">Submit</button>
                            </form>
                            </li>
                            <li class="col-lg-3 col-xlg-4 mb-4">
                            <h4 class="mb-3">List style</h4>
                            <!-- List style -->
                            <ul class="list-style-none">
                            <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> You can give link</a></li>
                            <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Give link</a></li>
                            <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Another Give link</a></li>
                            <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Forth link</a></li>
                            <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Another fifth link</a></li>
                            </ul>
                            </li>
                            </ul>
                            </div>
                            </li>
                            {*}
                            <!-- ============================================================== -->
                            <!-- End Messages -->
                            <!-- ============================================================== -->
                        </ul>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <ul class="navbar-nav my-lg-0">
                            {*}
                            <li class="nav-item hidden-sm-down">
                            <form class="app-search">
                            <input type="text" class="form-control" placeholder="Search for..."> <a class="srh-btn"><i class="ti-search"></i></a> </form>
                            </li>
                            {*}
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link nav-link-notification-test text-muted waves-effect waves-dark" style="line-height:1.1" href="">
                                    <i class="mdi mdi-backburger fa-2x"></i>
                                </a>
                                <a class="nav-link nav-link-notification text-muted waves-effect waves-dark" style="line-height:1.1" href="">
                                    <i class="far fa-bell fa-2x"></i>
                                    <div class="notification-badge" style="display: none" id="notification-badge">0</div>
                                </a>
                                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" style="line-height:1.1" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class=" far fa-user-circle fa-2x"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right animated flipInY">
                                    <ul class="dropdown-user">
                                        <li>
                                            <div class="dw-user-box">
                                                <div class="u-text">
                                                    <h4 class="pb-2">{$manager->name|escape}</h4>
                                                    <a href="manager/{$manager->id}" class="btn btn-rounded btn-info btn-sm">Профиль</a>
                                                    <a href="logout" class="btn btn-rounded btn-danger btn-sm">Выход</a></div>
                                            </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </nav>

                        </header>
                    </div>
                </nav>
            </header>
            <!-- ============================================================== -->
            <!-- End Topbar header -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Left Sidebar - style you can find in sidebar.scss  -->
            <!-- ============================================================== -->
            <aside class="left-sidebar">
                <!-- Sidebar scroll-->
                <div class="scroll-sidebar">
                    <!-- User profile -->
                    {*}
                    <div class="user-profile">
                    <!-- User profile image -->
                    <div class="profile-img"> <img src="design/{$settings->theme|escape}/assets/images/users/{$manager->avatar}" alt="{$manager->name|escape}" /> </div>
                    <!-- User profile text-->
                    <div class="profile-text"> <a href="#" class="dropdown-toggle link u-dropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">{$manager->name|escape} <span class="caret"></span></a>
                    <div class="dropdown-menu animated flipInY">
                    <a href="manager/{$manager->id}" class="dropdown-item"><i class="ti-user"></i> Профиль</a>
                    <a href="#" class="dropdown-item"><i class="ti-wallet"></i> My Balance</a>
                    <a href="#" class="dropdown-item"><i class="ti-email"></i> Inbox</a>
                    <div class="dropdown-divider"></div> <a href="#" class="dropdown-item"><i class="ti-settings"></i> Account Setting</a>
                    <div class="dropdown-divider"></div> <a href="logout" class="dropdown-item"><i class="fa fa-power-off"></i> Выход</a>
                    </div>
                    </div>
                    </div>
                    {*}
                    <!-- End User profile text-->
                    <!-- Sidebar navigation-->

                    <nav class="sidebar-nav">
                        <ul id="sidebarnav">
                            <!-- при добавлении новых li  скрыть для роли-discharge  !in_array('discharges_reports', $manager->permissions)-->

                            {if $is_developer}
                            <li class="text-danger text-center"><strong>developer mode</strong></li>
                            {/if}
                            
                            {if in_array('orders', $manager->permissions) ||  in_array('messages', $manager->permissions) ||  in_array('clients', $manager->permissions) || in_array('tickets', $manager->permissions) || in_array('my_tickets', $manager->permissions)}
                                <li class="nav-small-cap">Основные</li>
                            {/if}

                            {if in_array('orders', $manager->permissions)}
                                <li {if in_array($module, ['OrderView', 'OrdersView']) && !$my_orders}class="active"{/if}>
                                    <a class="" href="orders" aria-expanded="false"><i class="mdi mdi-animation"></i><span class="hide-menu">Заявки</span></a>
                                </li>
                            {/if}
                            {if in_array('my_orders', $manager->permissions)}
                                <li {if in_array($module, ['OrderView', 'OrdersView']) && $my_orders}class="active"{/if}>
                                    <a class="" href="my_orders" aria-expanded="false"><i class="mdi mdi-animation"></i><span class="hide-menu">Мои Заявки</span></a>
                                </li>
                            {/if}
                            {if in_array('messages', $manager->permissions)}
                                <li {if in_array($module, ['MessagesView'])}class="active"{/if}>
                                    <a class="" href="messages" aria-expanded="false"><i class="mdi mdi-email"></i><span class="hide-menu">Сообщения</span></a>
                                </li>
                            {/if}
                            {if in_array('clients', $manager->permissions)}
                                <li {if in_array($module, ['ClientView', 'ClientsView'])}class="active"{/if}>
                                    <a class="" href="clients" aria-expanded="false">
                                        <i class="mdi mdi-account-multiple-outline"></i>
                                        <span class="hide-menu">Клиенты</span>
                                    </a>
                                </li>
                            {/if}
                            {if in_array('vsev_debt', $manager->permissions)}
                                <li {if in_array($module, ['VsevDebtView'])}class="active"{/if}>
                                    <a class="" href="vsev_debt" aria-expanded="false">
                                        <i class="mdi mdi-bell-ring-outline"></i>
                                        <span class="hide-menu">Уведомления от ВсеВернем</span>
                                    </a>
                                </li>
                            {/if}
                            {if in_array('verificators', $manager->permissions)}
                                <li {if in_array($module, ['VerificatorsView'])}class="active"{/if}>
                                    <a class="" href="verificators" aria-expanded="false">
                                        <i class="mdi mdi-account-multiple-outline"></i>
                                        <span class="hide-menu">Верификаторы</span>
                                    </a>
                                </li>
                            {/if}
                            <!--
                            {if in_array('tickets', $manager->permissions) ||  in_array('my_tickets', $manager->permissions)}
                                <li {if in_array($module, ['TicketsView', 'TicketView'])}class="active"{/if}>
                                    <a class="" href="tickets" aria-expanded="false">
                                        <i class="mdi mdi-ticket-account"></i>
                                        <span class="hide-menu">Тикеты</span>
                                    </a>
                                </li>
                            {/if}
                            -->
                            {if !in_array('discharges_reports', $manager->permissions)}
                                <li {if in_array($module, ['AppealsView'])}class="active"{/if}>
                                    <a class="" href="appeals" aria-expanded="false"><i class="mdi mdi-email"></i><span class="hide-menu">Обращения</span></a>
                                </li>
                            {/if}


                            {*if in_array('individual_orders', $manager->permissions)}
                                <li class="nav-small-cap">Инд. рассмотрение</li>

                                <li {if in_array($module, ['IndividualOrderView', 'IndividualOrdersView']) && !$my_orders}class="active"{/if}>
                                    <a class="" href="individual_orders" aria-expanded="false"><i class="mdi mdi-animation"></i><span class="hide-menu">Заявки</span></a>
                                </li>
                            {/if*}

                            {if in_array('company_orders', $manager->permissions)}
                                <li {if in_array($module, ['CompanyOrdersView'])}class="active"{/if}>
                                    <a class="" href="company_order" aria-expanded="false"><i class="mdi mdi-animation"></i><span class="hide-menu">Заявки ИП и ООО</span></a>
                                </li>
                            {/if}
                            
                            {if in_array('cb_requests', $manager->permissions)}
                            <li class="nav-small-cap">Запросы КО</li>
                            <li {if in_array($module, ['CbRequestsView'])}class="active"{/if}>
                                <a class="has-arrow" href="cb-requests" aria-expanded="false"><i class="mdi mdi-bank"></i><span class="hide-menu">Запросы ЦБ</span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a href="/cb-requests">Листинг</a></li>
                                    <li><a href="/cb-requests/subjects">Темы запросов</a></li>
                                </ul>
                            </li>
                            {/if}

                            {if in_array('cc_tasks', $manager->permissions)}
                            <li class="nav-small-cap">Тикет-система</li>
                                        
                            <li {if in_array($module, ['TicketsView'])}class="active"{/if}>
                                <a class="has-arrow" href="tickets" aria-expanded="false"><i class="mdi mdi-closed-caption"></i><span class="hide-menu">Тикеты</span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a href="/tickets">Листинг</a></li>
                                    <li><a href="/tickets/subjects">Темы жалоб</a></li>
                                    <li><a href="/tickets/statistics">Статистика</a></li>
                                    <li><a href="/tickets/manager-statistics">Статистика по менеджерам</a></li>
                                    <li><a href="/tickets/settings">Настройки</a></li>
                                    <li><a href="/tickets/sms_logs">Журнал SMS сообщений</a></li>
                                </ul>
                            </li>
                            {/if}
                                        
                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['FaqView'])}class="active"{/if}>
                                    <a class="" href="/faq" aria-expanded="false"><i class="mdi mdi-help"></i><span class="hide-menu">FAQ</span></a>
                                </li>
                            {/if}

                            {if in_array('cc_tasks', $manager->permissions)}
                                <li class="nav-small-cap">Тех. поддержка</li>

                                <li>
                                    <a href="/technical-support/tickets"><i class="mdi mdi-book-open-page-variant"></i>Листинг</a>
                                </li>
                                <li>
                                    <a href="/technical-support/analytics/tickets"><i class="mdi mdi-file-chart"></i>Аналитика тикетов</a>
                                </li>
                                <li>
                                    <a href="/technical-support/analytics/operators"><i class="mdi mdi-poll-box"></i>Статистика операторов</a>
                                </li>
                                <li>
                                    <a href="/technical-support/sla/list"><i class="mdi mdi-settings"></i>Настройки SLA</a>
                                </li>
                            {/if}

                            {if in_array('cc_tasks', $manager->permissions)}
                                <li class="nav-small-cap">КЦ</li>
                            {/if}

                            <li {if $module == 'IncomingCallsBlacklistView'}class="active"{/if}>
                                <a href="incoming_calls_blacklist">
                                    <i class="mdi mdi-closed-caption"></i><span class="hide-menu">Черный список входящих</span>
                                </a>
                            </li>

                            <li {if $module == 'CbrLinkClicksReportView'}class="active"{/if}>
                                <a href="cbr_link_clicks_report">
                                    <i class="mdi mdi-closed-caption"></i><span class="hide-menu">Отчет по кликам на ссылку ЦБ</span>
                                </a>
                            </li>

                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['CCTasksView'])}class="active"{/if}>
                                    <a class="" href="cctasks" aria-expanded="false"><i class="mdi mdi-closed-caption"></i><span class="hide-menu">Мои задачи</span></a>
                                </li>
                            {/if}
                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['CCProlongationsView'])}class="active"{/if}>
                                    <a class="" href="ccprolongations" aria-expanded="false"><i class="mdi mdi-closed-caption"></i><span class="hide-menu">Продление нулевой день  </span></a>
                                </li>
                            {/if}
                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['ProlongationMinusDaysView'])}class="active"{/if}>
                                    <a class="" href="prolongation_minus_days" aria-expanded="false"><i class="mdi mdi-closed-caption"></i><span class="hide-menu">Продление минусовые дни  </span></a>
                                </li>
                            {/if}

                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['CCProlongationsPlusView'])}class="active"{/if}>
                                    <a class="" href="ccprolongations_plus" aria-expanded="false"><i class="mdi mdi-closed-caption"></i><span class="hide-menu"> Продление 1-2 день</span></a>
                                </li>
                            {/if}
                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['OrderView'])}class="active"{/if}>
                                    <a class="" href="not_received" aria-expanded="false"><i class="mdi mdi-closed-caption"></i><span class="hide-menu">Неполученные займы</span></a>
                                </li>
                            {/if}
                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['TasksOverdueView'])}class="active"{/if}>
                                    <a class="" href="tasks_overdue" aria-expanded="false">
                                        <i class="mdi mdi-closed-caption"></i>
                                        <span class="hide-menu">Задачи по просрочкам</span>
                                    </a>
                                </li>
                            {/if}
                            {if in_array('verificator_cc', $manager->permissions)}
                                <li {if in_array($module, ['VerificatorCcView'])}class="active"{/if}>
                                    <a class="" href="verificator_cc" aria-expanded="false">
                                        <i class="mdi mdi-closed-caption"></i>
                                        <span class="hide-menu">Заявки по рассылке</span>
                                    </a>
                                </li>
                            {/if}
                            {if in_array('cc_tasks', $manager->permissions)}
                                <li {if in_array($module, ['CloseTasksView'])}class="active"{/if}>
                                    <a class="" href="close_tasks" aria-expanded="false"><i class="mdi mdi-backburger"></i><span class="hide-menu">Возврат лояльных</span></a>
                                </li>
                            {/if}

                            {if in_array('managers', $manager->permissions)}
                                <li {if in_array($module, ['AutoRefundAdditionalServicesView'])}class="active"{/if}>
                                    <a class="" href="auto_refund_additional_services" aria-expanded="false"><i class="mdi mdi-backburger"></i><span class="hide-menu">Автовозвраты допов</span></a>
                                </li>
                            {/if}


                            {if in_array('managers', $manager->permissions) || $manager->role == 'partner_specialist'}
                                <li {if in_array($module, ['ArbitrationAgreementsGeneratorView'])}class="active"{/if}>
                                    <a class="" href="arbitration_agreements_generator" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span class="hide-menu">Создание арбитражных соглашений</span></a>
                                </li>
                            {/if}

                            <li {if in_array($module, ['TotalReportView', 'BankReportView', 'ReportMissingsView', 'OrderTimeReportView', 'TrafficReportView', 'LeadgidReportView', 'ShortReportView', 'CvReportView', 'LeadReportView', 'CallsReportView', 'AnalysisCallsReportView', 'AdditionalAnalysisCallsReportView', 'AIBotCallsReportView', 'VoxCallsReportView', 'UserFeedbacksReportView', 'MyTicketsReportView', 'CollectionSummaryReportView', 'UsedeskTicketAnalysisReportView', 'FinzenFunnelReportView'])}class="active"{/if}>
                                <a class="has-arrow" href="settings" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span class="hide-menu">Отчеты</span></a>
                                <ul aria-expanded="false" class="collapse">
                                    {if in_array('reports', $manager->permissions)}
                                        <li {if in_array($module, ['ExtraServicesClosureReportView'])}class="active"{/if}>
                                            <a class="" href="extra_services_closure_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по допам при закрытии</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ExtraServicesInformsReportView'])}class="active"{/if}>
                                            <a class="" href="extra_services_informs_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по SMS о допах</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['SmsPauseReportView'])}class="active"{/if}>
                                            <a class="" href="pause_sms_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по отключённым SMS с помощью Suvvy</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['DormantClientsReportView'])}class="active"{/if}>
                                            <a class="" href="dormant_clients_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по спящей базе клиентов</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['PaymentsClientsReportView'])}class="active"{/if}>
                                            <a class="" href="payments_clients_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по пролонгациям/погашениям клиентов</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ApprovedClientCallReport'])}class="active"{/if}>
                                            <a class="" href="approved_client_call_report" aria-expanded="false">
                                                <span class="hide-menu">Обзвон одобренных</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['CustomerActivityReportView'])}class="active"{/if}>
                                            <a class="" href="client_activity_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт о действиях клиента после звонка в КЦ</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['AnalysisCallsReportView'])}class="active"{/if}>
                                            <a class="" href="analysis_calls_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по анализу звонков ИИ</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['AdditionalAnalysisCallsReportView'])}class="active"{/if}>
                                            <a class="" href="additional_analysis_calls_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по анализу звонков ИИ с доп. услугами</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['AIBotCallsReportView'])}class="active"{/if}>
                                            <a class="" href="ai_bot_calls_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по звонкам ИИ</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['UserFeedbacksReportView'])}class="active"{/if}>
                                            <a class="" href="user_feedbacks_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по отзывам в ЛК</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['TicketsBRReportView'])}class="active"{/if}>
                                            <a class="" href="tickets_br_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт обращений клиентов для БР</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['MyTicketsOPRReportView'])}class="active"{/if}>
                                            <a class="" href="my_tickets_opr_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт обращений клиентов для ОПР</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['CollectionSummaryReportView'])}class="active"{/if}>
                                            <a class="" href="collection_summary_report" aria-expanded="false">
                                                <span class="hide-menu">Сводка по взысканию</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['UsedeskTicketAnalysisReportView'])}class="active"{/if}>
                                            <a class="" href="usedesk_ticket_analysis_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по анализу email переписки</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['UserRsPaymentReportView'])}class="active"{/if}>
                                            <a class="" href="payments_rs_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт оплат по р/с</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['BkiQuestionsView'])}class="active"{/if}>
                                            <a class="" href="bki_questions" aria-expanded="false">
                                                <span class="hide-menu">Бюро кредитных историй</span>
                                            </a>
                                        </li>
                                    {/if}

                                    <li {if in_array($module, ['VoxCallsReportView'])}class="active"{/if}>
                                        <a class="" href="vox_calls_report" aria-expanded="false">
                                            <span class="hide-menu">Статистика операторов Vox</span>
                                        </a>
                                    </li>
                                    <li {if in_array($module, ['FinzenFunnelReportView'])}class="active"{/if}>
                                        <a class="" href="finzen_funnel_report" aria-expanded="false">
                                            <span class="hide-menu">Воронка ШКД ФинДзен</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            {if in_array('reports', $manager->permissions)}
                            <li {if in_array($module, ['TotalReportView', 'BankReportView', 'ReportMissingsView', 'OrderTimeReportView', 'TrafficReportView', 'LeadgidReportView', 'ShortReportView', 'CvReportView', 'LeadReportView', 'CallsReportView', 'AnalysisCallsReportView', 'AdditionalAnalysisCallsReportView', 'UserFeedbacksReportView', 'MyTicketsReportView', 'CollectionSummaryReportView', 'UsedeskTicketAnalysisReportView'])}class="active"{/if}>
                                <a class="has-arrow" href="#" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span class="hide-menu">Массовое включение доп.услуг</span></a>
                                <ul aria-expanded="false" class="collapse">
                                    <li {if in_array($module, ['ServiceRecoveryRuleView'])}class="active"{/if}>
                                        <a class="" href="service-recovery/rules" aria-expanded="false">
                                            <span class="hide-menu">Правила</span>
                                        </a>
                                    </li>
                                    <li {if in_array($module, ['ServiceRecoveryReportView'])}class="active"{/if}>
                                        <a class="" href="service-recovery/reports" aria-expanded="false">
                                            <span class="hide-menu">Отчеты</span>
                                        </a>
                                    </li>
                                    <li {if in_array($module, ['ServiceRecoveryExclusionView'])}class="active"{/if}>
                                        <a class="" href="service-recovery/exclusions" aria-expanded="false">
                                            <span class="hide-menu">Управление исключениями</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            {/if}

                            {if in_array('cession', $manager->permissions)}
                                <li {if $module == 'CessionRequestsView'}class="active"{/if}>
                                    <a class="" href="cession_requests" aria-expanded="false">
                                        <i class="mdi mdi-file-document"></i>
                                        <span class="hide-menu">Цессия</span>
                                    </a>
                                </li>
                                <li {if $module == 'CessionSettingsView'}class="active"{/if}>
                                    <a class="" href="cession_settings" aria-expanded="false">
                                        <i class="mdi mdi-settings"></i>
                                        <span class="hide-menu">Настройки Цессии</span>
                                    </a>
                                </li>
                            {/if}

                            {if $manager->role != 'verificator_minus'}
                                {if in_array('managers', $manager->permissions) || in_array('logs', $manager->permissions) || in_array('settings', $manager->permissions)}
                                <li class="nav-small-cap">Управление</li>
                                {/if}
                            {/if}

                            {if in_array('managers', $manager->permissions)}
                                <li {if in_array($module, ['ManagerView', 'ManagersView'])}class="active"{/if}>
                                    <a class="" href="managers" aria-expanded="false">
                                        <i class="mdi mdi-incognito"></i>
                                        <span class="hide-menu">Менеджеры</span>
                                    </a>
                                </li>
                            {/if}
                            {if in_array('chief_verificator', $manager->permissions)}
                            <li {if $module == 'RegistrationRegionView'}class="active"{/if}>
                                <a href="registration_region">
                                    <i class="mdi mdi-incognito"></i>
                                    <span>Настройки регионов</span>
                                </a>
                            </li>
                            {/if}
                            {if in_array('logs', $manager->permissions) && $manager->role != 'verificator_minus'}
                                <li {if in_array($module, ['ChangelogsView'])}class="active"{/if}>
                                    <a class="" href="changelogs" aria-expanded="false"><i class="mdi mdi-book-open-page-variant"></i><span class="hide-menu">Логи</span></a>
                                </li>
                            {/if}
                            {if in_array('settings', $manager->permissions) || in_array('plan_settings', $manager->permissions) || $manager->login == 'Andreeva.EV' || in_array('threshold_settings', $manager->permissions)}
                                <li {if in_array($module, ['SettingsView', 'ApikeysView'])}class="active"{/if}>
                                    <a class="has-arrow" href="settings" aria-expanded="false"><i class="mdi mdi-settings"></i><span class="hide-menu">Настройки</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        {if in_array('settings', $manager->permissions)}
                                            <li {if $module == 'BlogSeoView'}class="active"{/if}><a href="blog_seo">Блог SЕО</a></li>
                                            <li {if $module == 'SiteSettingsView'}class="active"{/if}><a href="site_settings">Настройки сайта</a></li>
                                            <li {if $module == 'AutomationFailsView'}class="active"{/if}><a href="automation_fails">Автоматизация ошибок</a></li>
                                            <li {if $module == 'DocssView'}class="active"{/if}><a href="docs">Ред. документов</a></li>
                                            <li {if $module == 'SettingsView'}class="active"{/if}><a href="settings">Скоринги</a></li>
                                            <li {if $module == 'ApikeysView'}class="active"{/if}><a href="apikeys">Ключи для API</a></li>
                                            <li {if $module == 'IndividualSettingsView'}class="active"{/if}><a href="individual_settings">Инд. рассмотрение</a></li>
                                            <li {if $module == 'OrganizationsView'}class="active"{/if}><a href="organizations">Организации</a></li>
                                        {/if}
                                        <li class="active">
                                            <a href="fd_settings">Настройка тарифов для безопасных ПК</a>
                                        </li>
                                        <li class="active">
                                            <a href="fd_base_tariffs">Настройка базовых тарифов ФД (НК/ПК)</a>
                                        </li>
                                        <li class="active">
                                            <a href="vitamed_base_tariffs">Настройка базовых тарифов Витамед (НК/ПК)</a>
                                        </li>
                                        {if in_array('blacklist', $manager->permissions)}
                                            <li {if in_array($module, ['BlackListView'])}class="active"{/if}>
                                                <a href="blacklist" aria-expanded="false">Черный Список</a>
                                            </li>
                                        {/if}
                                        <li class="active">
                                            <a href="terrorist_list">Список террористов</a>
                                        </li>

                                        {if in_array('promocodes', $manager->permissions)}
                                            <li {if in_array($module, ['PromocodeView', 'PromocodesView'])}class="active"{/if}>
                                                <a href="promocodes" aria-expanded="false">Промокоды</a>
                                            </li>
                                        {/if}
                                        {if in_array('plan_settings', $manager->permissions)}
                                            <li {if $module == 'VerificatorPlanView'}class="active"{/if}><a href="verificator_plan">Планы сотрудников</a></li>
                                        {/if}
                                        {if in_array('threshold_settings', $manager->permissions)}
                                            <li {if $module == 'InsuranceThresholdSettingsView'}class="active"{/if}><a href="index.php?module=InsuranceThresholdSettingsView">Настройки порогов</a></li>
                                        {/if}
                                        {if $manager->login != 'Andreeva.EV'}
                                            <li {if $module == 'SettingsSmsNoticeApproveView'}class="active"{/if}><a href="settings_sms_notice_approve">Автоматическая отправка смс</a></li>
                                        {/if}
                                        <li {if $module == 'AutoConfirmSettingsView'}class="active"{/if}><a href="auto_confirm_settings">Автоодобрения</a></li>
                                        <li {if $module == 'PartnerHrefView'}class="active"{/if}><a href="partner_href">Ссылки отказной</a></li>
                                        <li {if $module == 'StopListWebIdView'}class="active"{/if}><a href="stop_list_web_id">Стоп-лист web-id</a></li>
                                        <li {if $module == 'ValidateMoratoriumView'}class="active"{/if}><a class="" href="moratorium_validate" aria-expanded="false"><span class="hide-menu">Валидация контактов</span></a></li>
                                        {if in_array('collection_sms_settings', $manager->permissions)}
                                            <li {if $module == 'SettingsSmsCollection'}class="active"{/if}><a class="" href="settings_sms_collection" aria-expanded="false"><span class="hide-menu">Настройки смс взыскание</span></a></li>
                                        {/if}
                                        {if in_array($manager->role, ['developer', 'opr', 'ts_operator']) || in_array($manager->login, ['Voronoy.IYU', 'admin'])}
                                            <li {if $module == 'ScoristaLeadgidSettingsView'}class="active"{/if}><a href="scorista_leadgid_settings">Лидгены в скористе</a></li>
                                        {/if}
                                        <li {if $module == 'JuicescoreCriteriaView'}class="active"{/if}><a href="juicescore_criteria">JuiceScore критерии</a></li>
                                        <li {if $module == 'LeadPriceView'}class="active"{/if}><a href="lead_price">Цены по вебам</a></li>
                                        <li {if $module == 'VkSendingSettingsView'}class="active"{/if}><a href="vk_sending_settings">Рассылка ВК</a></li>
                                        <li {if $module == 'ProlongationSettingsView'}class="active"{/if}><a href="prolongation_settings">Настройки пролонгации</a></li>
                                        <li {if $module == 'SprVersionsView'}class="active"{/if}><a href="spr_versions">Версии СПР</a></li>
                                        {if in_array('declined_traffic_settings', $manager->permissions)}
                                            <li {if $module == 'DeclinedTrafficSettingsView'}class="active"{/if}><a href="declined_traffic_settings">Управление отказным трафиком</a></li>
                                        {/if}
                                        <li {if $module == 'VoxQueuesSettingsView'}class="active"{/if}>
                                            <a href="vox_queues_settings">Очереди статистики операторов</a>
                                        </li>
                                        <li {if $module == 'VoxOperatorsSettingsView'}class="active"{/if}>
                                            <a href="vox_operators_settings">Настройки операторов Vox</a>
                                        </li>
                                        <li {if $module == 'VoxSiteDncView'}class="active"{/if}>
                                            <a href="vox_site_dnc">Vox DNC по сайтам</a>
                                        </li>
                                        <li {if $module == 'UsedeskSettingsView'}class="active"{/if}>
                                            <a href="usedesk_settings">Настройки Usedesk</a>
                                        </li>
                                    </ul>
                                </li>
                            {/if}

                            {if in_array('settings', $manager->permissions) || in_array('ticket_settings', $manager->permissions) || in_array('sms_templates', $manager->permissions)}
                                <li {if in_array($module, ['SmsTemplates', 'MaratoriumsView', 'ReasonsView'])}class="active"{/if}>
                                    <a class="has-arrow" href="#settings" aria-expanded="false"><i class="mdi mdi-book-open-page-variant"></i><span class="hide-menu">Справочники</span></a>
                                    <ul aria-expanded="false" id="settings" class="collapse">
                                        <li {if $module == 'MaratoriumsView'}class="active"{/if}><a href="maratoriums">Моратории</a></li>
                                        <li {if $module == 'ReasonsView' && $type=='reject'}class="active"{/if}><a href="reasons/reject">Причины отказа</a></li>
                                        <li {if $module == 'ReasonsView' && $type=='waiting'}class="active"{/if}><a href="reasons/waiting">Причины ожидания</a></li>
                                        {if in_array('sms_templates', $manager->permissions)}
                                        <li {if $module == 'SmsTemplatesView'}class="active"{/if}><a href="sms_templates">Шаблоны сообщений</a></li>
                                        {/if}
                                        <li {if $module == 'PaymentExitpoolVariantsView'}class="active"{/if}><a href="payment_exitpool_variants">Просрочка ответы</a></li>
                                        <li {if $module == 'TicketReasonsView' || $module=='TicketStatusesView' || $module=='TicketSubjectsView'}class="active"{/if}>
                                            <a class="has-arrow" href="#ticket_settings" aria-expanded="false"><span class="hide-menu">Настройки тикетов</span></a>
                                            <ul aria-expanded="false" id="ticket_settings" class="collapse">
                                                <li {if $module == 'TicketReasonsView'}class="active"{/if}><a href="ticket_reasons">Причины закрытия</a></li>
                                                <li {if $module == 'TicketStatusesView'}class="active"{/if}><a href="ticket_statuses">Статусы</a></li>
                                                <li {if $module == 'TicketSubjectsView'}class="active"{/if}><a href="ticket_subjects">Темы</a></li>
                                                <li {if $module == 'TicketsTagListView'}class="active"{/if}><a href="ticket_subjects">Теги</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            {/if}

                            {if in_array('missings', $manager->permissions) ||  in_array('exitpools', $manager->permissions) || in_array('calls_report', $manager->permissions)}
                                <li class="nav-small-cap">Аналитика</li>
                            {/if}
                            
                            {if in_array('exitpools', $manager->permissions) || in_array('order_time_report', $manager->permissions) || in_array('calls_report', $manager->permissions)}
                                <li {if in_array($module, ['TotalReportView', 'BankReportView', 'ReportMissingsView', 'OrderTimeReportView', 'TrafficReportView', 'LeadgidReportView', 'ShortReportView', 'CvReportView', 'LeadReportView', 'CallsReportView', 'FinzenFunnelReportView'])}class="active"{/if}>
                                    <a class="has-arrow" href="settings" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span class="hide-menu">Отчеты</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        {if  in_array('exitpools', $manager->permissions)}
                                        <li {if in_array($module, ['TotalReportView'])}class="active"{/if}>
                                            <a class="" href="total_report" aria-expanded="false">
                                                <span class="hide-menu">Общий отчет</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['CRLKRejectOrderReportView'])}class="active"{/if}>
                                                <a class="" href="multipolis_pays_report" aria-expanded="false">
                                                    <span class="hide-menu">Отчёт о движении допа Консьерж сервиса</span>
                                                </a>
                                        </li>
                                        <li {if in_array($module, ['ShortReportView'])}class="active"{/if}>
                                            <a class="" href="short_report" aria-expanded="false">
                                                <span class="hide-menu">Краткий отчет</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['CvReportView'])}class="active"{/if}>
                                            <a class="" href="cv_report" aria-expanded="false">
                                                <span class="hide-menu">Общая воронка</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['LeadReportView'])}class="active"{/if}>
                                            <a class="" href="lead_report" aria-expanded="false">
                                                <span class="hide-menu">Воронка по партнерам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['LeadgidReportView'])}class="active"{/if}>
                                            <a class="" href="leadgid_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет Leadgens</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['TrafficReportView'])}class="active"{/if}>
                                            <a class="" href="traffic_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по траффику</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['OrdersReportView'])}class="active"{/if}>
                                            <a class="" href="orders_report" aria-expanded="false">
                                                <span class="hide-menu">Воронка по заявкам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FinzenFunnelReportView'])}class="active"{/if}>
                                            <a class="" href="finzen_funnel_report" aria-expanded="false">
                                                <span class="hide-menu">Воронка ШКД ФинДзен</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {if  in_array('nbki_reports', $manager->permissions)}
                                        <li {if in_array($module, ['NbkiReportsView'])}class="active"{/if}>
                                            <a class="" href="nbki_reports" aria-expanded="false">
                                                <span class="hide-menu">Отчеты для НБКИ</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {if  in_array('calls_report', $manager->permissions)}
                                        <li {if in_array($module, ['CallsReportView'])}class="active"{/if}>
                                            <a class="" href="calls_report" aria-expanded="false">                                                
                                                <span class="hide-menu">Отчет по звонкам</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {if  in_array('order_time_report', $manager->permissions)}
                                        <li {if in_array($module, ['OrderTimeReportView'])}class="active"{/if}>
                                            <a class="" href="order_time_report" aria-expanded="false">                                                
                                                <span class="hide-menu">Отчет по этапам</span>
                                            </a>
                                        </li>
                                        {/if}
                                        <li {if in_array($module, ['RejectReportView'])}class="active"{/if}>
                                            <a class="" href="reject_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по отказам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FunnelReturnsNKReportView'])}class="active"{/if}>
                                            <a class="" href="funnel_returns_nk_report" aria-expanded="false">
                                                <span class="hide-menu">Воронка по возвратам НК</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['MissingsCCReportView'])}class="active"{/if}>
                                            <a class="" href="missings_cc_report" aria-expanded="false">
                                                <span class="hide-menu">Отвалы - эффективность КЦ</span>
                                            </a>
                                        </li>

                                        <li {if in_array($module, ['AdditionalOrdersReportView'])}class="active"{/if}>
                                            <a class="" href="additional_orders_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по допам (покупки и возвраты)</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['RefundExtraServicesReportView'])}class="active"{/if}>
                                            <a class="" href="extra_services_refunds" aria-expanded="false">
                                                <span class="hide-menu">Отчет по допам (возвраты)</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ApprovedOrdersReportView'])}class="active"{/if}>
                                            <a class="" href="approved_orders" aria-expanded="false">
                                                <span class="hide-menu">Отчёт - Одобренные заявки</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['VerifiersReportView'])}class="active"{/if}>
                                            <a class="" href="virifiers_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по верификаторам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['BankReportView'])}class="active"{/if}>
                                            <a class="" href="bank_report" aria-expanded="false">
                                                <span class="hide-menu">Отчетность</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['BankruptcyReportView'])}class="active"{/if}>
                                            <a class="" href="bankruptcy_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по банкротствам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ProlongationReportView'])}class="active"{/if}>
                                            <a class="" href="prolongation_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по пролонгации</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['VerificationsGroupReportView'])}class="active"{/if}>
                                            <a class="" href="verifications_group_report" aria-expanded="false">
                                                <span class="hide-menu">Время обработки заявок верификаторами</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['StoristaReportView'])}class="active"{/if}>
                                            <a class="" href="storista_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по отказным скориста</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['RejectReasonReportView'])}class="active"{/if}>
                                            <a class="" href="reject_reason_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по причинам отказа</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            {/if}

                            {if in_array('portfolio_report', $manager->permissions)}
                                <li {if in_array($module, ['ReportPortfolioView'])}class="active"{/if}>
                                    <a class="has-arrow" href="settings" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span class="hide-menu">Отчеты бухгалтеру</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        {if  in_array('portfolio_report', $manager->permissions)}
                                        <li {if in_array($module, ['ReportPortfolioView'])}class="active"{/if}>
                                            <a class="" href="report_portfolio" aria-expanded="false">                                                
                                                <span class="hide-menu">Портфель</span>
                                            </a>
                                        </li>
                                        {/if}
                                        <li {if $module == 'CDReportView'}class="active"{/if}><a class="" href="cd_report" aria-expanded="false"><span class="hide-menu">Статистика по КД</span></a></li>
                                        <li {if $module == 'CDMultipolisView'}class="active"{/if}><a class="" href="cd_multipolis" aria-expanded="false"><span class="hide-menu">Статистика консьерж сервис</span></a></li>
                                        <li {if $module == 'CDTvMedicalView'}class="active"{/if}><a class="" href="cd_tv_medical" aria-expanded="false"><span class="hide-menu">Статистика по телемедицине</span></a></li>
                                    </ul>
                                </li>
                            {/if}
                            {if in_array('marketing_analyst', $manager->permissions) || in_array('marketing_analyst_junior', $manager->permissions) || in_array('boss_cc', $manager->permissions)}
                                <li {if in_array($module, ['LeadgidReportView'])}class="active"{/if}>
                                    <a class="has-arrow" href="settings" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span class="hide-menu">Аналитика маркетологу</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        <li
                                                {if in_array($module, [
                                                        'OrderRatingNKReportView',
                                                        'OrderRatingReportView',
                                                        'CRLKRejectOrderReportView',
                                                        'MultipolisReportView',
                                                        'MultipolisStatisticsReportView',
                                                        'CDMultipolisView',
                                                        'DopConversionReportView',
                                                        'CDReportView',
                                                        'CDTvMedicalView',
                                                        'CreditRatingPaysReportView',
                                                        'CDUsersAmountReportView',
                                                        'FinzenFunnelReportView'
                                                    ])} class="active"
                                                {/if}
                                        >
                                            <a class="has-arrow" href="#dops_settings" aria-expanded="false"><span class="hide-menu">Допы</span></a>
                                            <ul aria-expanded="false" id="dops_settings" class="collapse">
                                                <li {if in_array($module, ['CDPenaltyReportView'])}class="active"{/if}>
                                                    <a class="" href="cd_penalty_report" aria-expanded="false">
                                                        <span class="hide-menu">Отчёт - штрафной КД</span>
                                                    </a>
                                                </li>
                                                <li {if in_array($module, ['FinzenFunnelReportView'])}class="active"{/if}>
                                                    <a class="" href="finzen_funnel_report" aria-expanded="false">
                                                        <span class="hide-menu">Воронка ШКД ФинДзен</span>
                                                    </a>
                                                </li>
                                                <li {if in_array($module, ['OrderRatingNKReportView'])}class="active"{/if}>
                                                    <a class="" href="order_rating_nk_report" aria-expanded="false">
                                                        <span class="hide-menu">Кредитный рейтинг новый клиент</span>
                                                    </a>
                                                </li>
                                                <li {if in_array($module, ['OrderRatingRejectReportView'])}class="active"{/if}>
                                                    <a class="" href="order_reject_rating_report" aria-expanded="false">
                                                        <span class="hide-menu">Кредитный рейтинг "Почему отказ"</span>
                                                    </a>
                                                </li>

                                                <li {if in_array($module, ['OrderRatingReportView'])}class="active"{/if}>
                                                    <a class="" href="order_rating_report" aria-expanded="false">
                                                        <span class="hide-menu">Кредитный рейтинг покупка из лк</span>
                                                    </a>
                                                </li>

                                                {*<li {if in_array($module, ['CRLKRejectOrderReportView'])}class="active"{/if}>
                                                    <a class="" href="cr_lk_reject_order_report" aria-expanded="false">
                                                        <span class="hide-menu">Кредитный рейтинг "Почему отказ"</span>
                                                    </a>
                                                </li>*}

                                                <li {if in_array($module, ['MultipolisReportView'])}class="active"{/if}>
                                                    <a class="" href="multipolis_report" aria-expanded="false">
                                                        <span class="hide-menu">Отчёт по консьерж сервису</span>
                                                    </a>
                                                </li>

                                                <li {if in_array($module, ['MultipolisStatisticsReportView'])}class="active"{/if}>
                                                    <a class="" href="multipolis_statistics_report" aria-expanded="false">
                                                        <span class="hide-menu">Статистика по консьерж сервису</span>
                                                    </a>
                                                </li>
                                                <li {if $module == 'CDMultipolisView'}class="active"{/if}>
                                                    <a class="" href="cd_multipolis" aria-expanded="false">
                                                        <span class="hide-menu">КОнсьерж сервис(н)</span>
                                                    </a>
                                                </li>
                                                <li {if in_array($module, ['DopConversionReportView'])}class="active"{/if}>
                                                    <a class="" href="dop_conversion_report" aria-expanded="false">
                                                        <span class="hide-menu">Конверсия в допы</span>
                                                    </a>
                                                </li>
                                                <li {if $module == 'CDReportView'}class="active"{/if}>
                                                    <a class="" href="cd_report" aria-expanded="false">
                                                        <span class="hide-menu">Кредитный доктор(н)</span>
                                                    </a>
                                                </li>
                                                <li {if $module == 'CDTvMedicalView'}class="active"{/if}>
                                                    <a class="" href="cd_tv_medical" aria-expanded="false">
                                                        <span class="hide-menu">Телемедицина(н)</span>
                                                    </a>
                                                </li>

                                                <li {if $module == 'CreditRatingPaysReportView'}class="active"{/if}>
                                                    <a class="" href="credit_rating_pays_report" aria-expanded="false">
                                                        <span class="hide-menu">Отчёт по всем продажам КР</span>
                                                    </a>
                                                </li>
                                                <li {if in_array($module, ['CDUsersAmountReportView'])}class="active"{/if}>
                                                    <a class="" href="cdoctor_users_amount_report" aria-expanded="false">
                                                        <span class="hide-menu">КД Доступность ступеней обучения</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li {if in_array($module, ['PostBackSettingsView'])}class="active"{/if}>
                                            <a class="" href="post_back_settings" aria-expanded="false">
                                                <span class="hide-menu">Настройки постбеков</span>
                                            </a>
                                        </li>
                                        {if in_array('marketing_analyst', $manager->permissions)}
                                        <li {if in_array($module, ['LeadgidReportView'])}class="active"{/if}>
                                            <a class="" href="leadgid_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет Leadgens</span>
                                            </a>
                                        </li>
                                        {/if}
                                        <li {if in_array($module, ['NewYearCodesView'])}class="active"{/if}>
                                            <a class="" href="new_year_codes" aria-expanded="false">
                                                <span class="hide-menu">Коды участников розыгрыша</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['NewYearCodesView'])}class="active"{/if}>
                                            <a class="" href="link_to_safe_flow" aria-expanded="false">
                                                <span class="hide-menu">Ссылка на безопасное флоу</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FunnelLoansNewClientCrmView']) || in_array('boss_cc', $manager->permissions)}class="active"{/if}>
                                            <a class="" href="funnel_loans_new_client_crm" aria-expanded="false">
                                                <span class="hide-menu">Воронка займы Новый клиент из CRM</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FunnelLoansOldClientCrmView']) || in_array('boss_cc', $manager->permissions)}class="active"{/if}>
                                            <a class="" href="funnel_loans_old_client_crm" aria-expanded="false">
                                                <span class="hide-menu">Воронка займы Повторный клиент из CRM</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FunnelLoansOldClientSmsCrmView']) || in_array('boss_cc', $manager->permissions)}class="active"{/if}>
                                            <a class="" href="funnel_loans_old_client_sms_crm" aria-expanded="false">
                                                <span class="hide-menu">Воронка займы ПК смс</span>
                                            </a>
                                        </li>
                                        {if in_array('marketing_analyst', $manager->permissions)}
                                        <li {if in_array($module, ['ReportPartnerHrefView'])}class="active"{/if}>
                                            <a class="" href="report_partner_href" aria-expanded="false">
                                                <span class="hide-menu">Отказной трафик по ссылкам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['DiscountInsureView'])}class="active"{/if}>
                                            <a class="" href="discount_insure" aria-expanded="false">
                                                <span class="hide-menu">Скидки на страховку</span>
                                            </a>
                                        </li>
                                        {/if}
                                        <li {if in_array($module, ['WebMasterReportView'])}class="active"{/if}>
                                            <a class="" href="web_master_report" aria-expanded="false">
                                                <span class="hide-menu">Анализ веб-мастеров</span>
                                            </a>
                                        </li>
                                        {if in_array('marketing_analyst', $manager->permissions)}
                                        <li {if in_array($module, ['NKtoPKReportView'])}class="active"{/if}>
                                            <a class="" href="nk_to_pk_report" aria-expanded="false">
                                                <span class="hide-menu">Переход НК в ПК</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ApprovedOrders'])}class="active"{/if}>
                                            <a class="" href="approved_orders" aria-expanded="false">
                                                <span class="hide-menu">Одобренные заявки</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FunnelLoansPromoCodeView'])}class="active"{/if}>
                                            <a class="" href="funnel_loans_promocode" aria-expanded="false">
                                                <span class="hide-menu">Воронка займы промокод</span>
                                            </a>
                                        </li>
                                        {/if}
                                        <li {if in_array($module, ['ApprovedOrdersReportView'])}class="active"{/if}>
                                            <a class="" href="approved_orders_report" aria-expanded="false">
                                                <span class="hide-menu">Займы по одобренным</span>
                                            </a>
                                        </li>
                                        <li {if $module == 'SmsSendingReportView'}class="active"{/if}>
                                            <a class="" href="sms_sending_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт для отправки смс</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['BankruptcyReportView'])}class="active"{/if}>
                                            <a class="" href="bank_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по банкротствам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['RefusenikReportView'])}class="active"{/if}>
                                            <a class="" href="refusenik_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по отказникам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['PdnReportView'])}class="active"{/if}>
                                            <a class="" href="pdn_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по ПДН</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['PdnEditView'])}class="active"{/if}>
                                            <a class="" href="pdn_edit" aria-expanded="false">
                                                <span class="hide-menu">Редактировать ПДН</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['Ping3OrdersReportView'])}class="active"{/if}>
                                            <a class="" href="ping3_orders_report" aria-expanded="false">
                                                <span class="hide-menu">Заявки PING 3</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            {/if}

                            {if in_array('missings', $manager->permissions)}
                                <li {if in_array($module, ['МissingsView'])}class="active"{/if}>
                                    <a class="" href="missings" aria-expanded="false">
                                        <i class="mdi mdi-sleep"></i>
                                        <span class="hide-menu">Отвалы</span>
                                    </a>
                                </li>
                            {/if}

                            {if in_array('missings', $manager->permissions)}
                                <li {if in_array($module, ['NotReceivedLoansView'])}class="active"{/if}>
                                    <a class="" href="not_received_loans" aria-expanded="false">
                                        <i class="mdi mdi-sleep"></i>
                                        <span class="hide-menu">Неполученные займы</span>
                                    </a>
                                </li>
                            {/if}

                            {if in_array('exitpools', $manager->permissions)}
                                <li {if in_array($module, ['ExitpoolsView', 'PaymentExitpoolsView'])}class="active"{/if}>
                                    <a class="has-arrow" href="#" aria-expanded="false"><i class="mdi mdi-assistant"></i><span class="hide-menu">Опросы</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        <li {if $module == 'ExitpoolsView'}class="active"{/if}><a href="exitpools">Получение</a></li>
                                        <li {if $module == 'PaymentExitpoolsView'}class="active"{/if}><a href="payment_exitpools">Просрочка</a></li>
                                    </ul>
                                </li>
                            {/if}

                            {if in_array('verification', $manager->permissions)}
                                <li {if in_array($module, ['VerificationView'])}class="active"{/if}>
                                    <a class="" href="verification" aria-expanded="false">
                                        <i class="mdi mdi-blogger"></i>
                                        <span class="hide-menu">Верификаторы</span>
                                    </a>
                                </li>
                            {/if}

                            {if in_array('create_managers', $manager->permissions) && !in_array('discharges_reports', $manager->permissions)}
                                <li {if in_array($module, ['SmsInfoView'])}class="active"{/if}>
                                    <a class="" href="sms_info" aria-expanded="false">
                                        <i class="mdi mdi-cellphone"></i>
                                        <span class="hide-menu">Информация smsc.ru</span>
                                    </a>
                                </li>
                            {/if}
                            {if in_array('create_managers', $manager->permissions) && !in_array('discharges_reports', $manager->permissions)}
                                <li {if in_array($module, ['QuestFormView'])}class="active"{/if}>
                                    <a class="" href="quest_form" aria-expanded="false">
                                        <i class="mdi mdi-incognito"></i>
                                        <span class="hide-menu">Настройки формы регистрации</span>
                                    </a>
                                </li>
                            {/if}

                            {if in_array('boss_cc', $manager->permissions)}
                                <li {if in_array($module, ['ReportMissingsView', 'ReportMissingsNewView', 'CCManagersView'])}class="active"{/if}>
                                    <a class="has-arrow" href="settings" aria-expanded="false"><i class="mdi mdi-file-chart"></i><span class="hide-menu">Контактный центр</span></a>
                                    <ul aria-expanded="false" class="collapse">
                                        <li {if in_array($module, ['AdditionalOrdersReportView'])}class="active"{/if}>
                                            <a class="" href="additional_orders_report" aria-expanded="false">
                                                <span class="hide-menu">Отчет по допам (покупки и возвраты)</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['RefundExtraServicesReportView'])}class="active"{/if}>
                                            <a class="" href="extra_services_refunds" aria-expanded="false">
                                                <span class="hide-menu">Отчет по допам (возвраты)</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ReportMissingsView'])}class="active"{/if}>
                                            <a class="" href="report_missings" aria-expanded="false">
                                                <span class="hide-menu">Отвалы отчет</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ReportMissingsNewView'])}class="active"{/if}>
                                            <a class="" href="report_missings_new" aria-expanded="false">
                                                <span class="hide-menu">Отвалы отчет Новый</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['CCManagersView'])}class="active"{/if}>
                                            <a class="" href="cc_managers" aria-expanded="false">
                                                <span class="hide-menu">Менеджеры Исходящего КЦ</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FunnelLoansPromoCodeView'])}class="active"{/if}>
                                            <a class="" href="funnel_loans_promocode" aria-expanded="false">
                                                <span class="hide-menu">Список промокодов</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['MissingsCCReportView'])}class="active"{/if}>
                                            <a class="" href="missings_cc_report" aria-expanded="false">
                                                <span class="hide-menu">Отвалы - эффективность КЦ</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['FunnelLoansNewClientCrmView']) || in_array('boss_cc', $manager->permissions)}class="active"{/if}>
                                            <a class="" href="funnel_loans_new_client_crm" aria-expanded="false">
                                                <span class="hide-menu">Воронка займы Новый клиент из CRM</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ReportPartnerHrefView'])}class="active"{/if}>
                                            <a class="" href="report_partner_href" aria-expanded="false">
                                                <span class="hide-menu">Отказной трафик по ссылкам</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['ApprovedOrdersReportView'])}class="active"{/if}>
                                            <a class="" href="approved_orders_report" aria-expanded="false">
                                                <span class="hide-menu">Займы по одобренным</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['MultipolisReportView'])}class="active"{/if}>
                                            <a class="" href="multipolis_report" aria-expanded="false">
                                                <span class="hide-menu">Отчёт по консьерж сервису</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['MultipolisStatisticsReportView'])}class="active"{/if}>
                                            <a class="" href="multipolis_statistics_report" aria-expanded="false">
                                                <span class="hide-menu">Статистика по консьерж серису</span>
                                            </a>
                                        </li>
                                        <li {if $module == 'CDReportView'}class="active"{/if}>
                                            <a class="" href="cd_report" aria-expanded="false">
                                                <span class="hide-menu">Кредитный доктор(н)</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['DopConversionReportView'])}class="active"{/if}>
                                            <a class="" href="dop_conversion_report" aria-expanded="false">
                                                <span class="hide-menu">Конверсия в допы</span>
                                            </a>
                                        </li>
                                        <li {if in_array($module, ['MigrateToMBView'])}class="active"{/if}>
                                            <a class="" href="migrate_mb" aria-expanded="false">
                                                <span class="hide-menu">Миграция в MindBox</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            {/if}
                            {if in_array('export_photos', $manager->permissions)}
                                <li {if in_array($module, ['ExportPhotosView'])}class="active"{/if}>
                                    <a class="" href="export-photos" aria-expanded="false">
                                        <i class="mdi mdi-account-multiple-outline"></i>
                                        <span class="hide-menu">Выгрузка фото</span>
                                    </a>
                                </li>
                            {/if}
                        </ul>
                        <div  class="hide-menu">
                        </div>
                    </nav>
                    <!-- End Sidebar navigation -->
                </div>
                <!-- End Sidebar scroll-->
                <!-- Bottom points>
                <div class="sidebar-footer">
                    <a href="" class="link" data-toggle="tooltip" title="Settings"><i class="ti-settings"></i></a>
                    <a href="" class="link" data-toggle="tooltip" title="Email"><i class="mdi mdi-gmail"></i></a>
                    <a href="logout" class="link" data-toggle="tooltip" title="Выход"><i class="mdi mdi-power"></i></a>
                </div>
                <!-- End Bottom points-->
            </aside>
            <!-- ============================================================== -->
            <!-- End Left Sidebar - style you can find in sidebar.scss  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->


            <!-- Page wrapper  -->
            <!-- ============================================================== -->
            {include file="tickets/mangoDialog.tpl"}
            {$content}
            <!-- ============================================================== -->
            <!-- End Page wrapper  -->
            <!-- ============================================================== -->
        </div>
        <audio id="ticketSound" src="/design/audio/notification.mp3" preload="auto"></audio>
        <!-- ============================================================== -->
        <!-- End Wrapper -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- All Jquery -->
        <!-- ============================================================== -->
        <script src="design/{$settings->theme|escape}/assets/plugins/jquery/jquery.min.js"></script>
        <script src="design/{$settings->theme|escape}/js/jquery-ui.min.js"></script>
    <!--script src="design/{$settings->theme|escape}/js/apps/eventlogs.app.js?v=1.01"></script-->

        <!-- Bootstrap tether Core JavaScript -->
        <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap/js/popper.min.js"></script>
        <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        <!-- slimscrollbar scrollbar JavaScript -->
        <script src="design/{$settings->theme|escape}/js/jquery.slimscroll.js"></script>
        <!--Wave Effects -->
        <script src="design/{$settings->theme|escape}/js/waves.js"></script>
        <!--Menu sidebar -->
        <script src="design/{$settings->theme|escape}/js/sidebarmenu.js"></script>
        <!--stickey kit -->
        <script src="design/{$settings->theme|escape}/assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>

        <script src="design/{$settings->theme|escape}/assets/plugins/sweetalert2/dist/sweetalert2.all.min.js"></script>
        <!--Custom JavaScript -->
        <script src="design/{$settings->theme|escape}/js/custom.min.js"></script>
        <script src="design/{$settings->theme|escape}/js/cust.js?v=1.02"></script>
        <script src="design/{$settings->theme|escape}/js/apps/mango_call.app.js?v=1.02"></script>

        {$smarty.capture.page_scripts}
        <!-- Style switcher -->
        <script src="design/{$settings->theme|escape}/js/apps/run_scorings.app.js?v=1.04"></script>

        <link rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/autocomplete/styles.css?v=1.01" />
        <script src="design/{$settings->theme|escape}/assets/plugins/autocomplete/jquery.autocomplete-min.js?v=1.01"></script>
        <script src="design/{$settings->theme|escape}/js/apps/dadata.app.js?v=1.041"></script>
        <script src="design/{$settings->theme|escape}/js/apps/ticket-sound.js?v=1.3"></script>
        <!-- ============================================================== -->
        <script src="design/{$settings->theme|escape}/assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
        {include file="notifications.tpl"}
    </body>

</html>
