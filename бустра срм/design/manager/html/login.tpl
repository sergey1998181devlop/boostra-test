{$wrapper='' scope=parent}
<!DOCTYPE html>
<html lang="ru">

<head>
    <base href="{$config->root_url}/" />
    
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Вход в панель управления</title>
	
    <!-- Bootstrap Core CSS -->
    <link href="design/{$settings->theme|escape}/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="design/{$settings->theme|escape}/css/style.css?v=1.07" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="design/{$settings->theme|escape}/css/colors/megna-dark.css?v=1.02" id="theme" rel="stylesheet">
    <!-- Custom CSS -->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="fix-header card-no-border fix-sidebar">
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
    <section id="wrapper">
        <div class="login-register" style="background-image:url(design/{$settings->theme|escape}/assets/images/background/login-register.jpg);">        
            <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" id="loginform" method="POST">
                    <h3 class="box-title mb-3">Вход</h3>
                    
                    {if $error}
                    <div class="alert alert-danger">
                        {if $error == 'login_incorrect'}Логин или пароль не совпадают
                        {else}{$error}{/if}
                    </div>
                    {/if}
                    
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" required="" name="login" value="{$login|escape}" placeholder="Логин"> </div>
                        </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input class="form-control" type="password" required="" name="password" placeholder="Пароль"> </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="checkbox checkbox-primary float-left pt-0">
                                <input id="checkbox-signup" type="checkbox" name="remember" value="1">
                                <label for="checkbox-signup"> Запомнить меня </label>
                            </div> 
                            {*}
                            <a href="javascript:void(0)" id="to-recover" class="text-dark float-right"><i class="fa fa-lock mr-1"></i> Забыли пароль?</a> 
                            {*}
                        </div>
                    </div>
                    <div class="form-group text-center mt-3">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Войти</button>
                        </div>
                    </div>
                </form>
                <form class="form-horizontal" id="recoverform" action="index.html">
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <h3>Recover Password</h3>
                            <p class="text-muted">Enter your Email and instructions will be sent to you! </p>
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" required="" placeholder="Email"> </div>
                    </div>
                    <div class="form-group text-center mt-3">
                        <div class="col-xs-12">
                            <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
          </div>
        </div>
        
    </section>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="design/{$settings->theme|escape}/assets/plugins/jquery/jquery.min.js"></script>
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
    <!--Custom JavaScript -->
    <script src="design/{$settings->theme|escape}/js/custom.min.js"></script>
    <!-- ============================================================== -->
    
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="design/{$settings->theme|escape}/assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
</body>

</html>