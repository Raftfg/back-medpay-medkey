<?php
$mtn = $mtn;
$ion6 = $ion6;
$transactionId = $transactionId;
$objet = $objet;
$numero = $numero;
$prix = $prix;
$nom = $nom;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Mtn paiement</title>
    <link rel="icon" type="image/x-icon"  href="" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/retour/css/fontawesome.css" rel="stylesheet">
     <link href="/retour/css/brands.css" rel="stylesheet">
    <link href="/retour/css/solid.css" rel="stylesheet">
    <link href="/retour/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/libs/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/libs/bootstrap-toggle/css/bootstrap-toggle.min.css" />
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/libs/alertify/css/alertify.css" />
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/libs/alertify/css/alertify-bootstrap-3.css" />
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/libs/bootstrap-select/css/bootstrap-select.min.css" />
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/css/style.css" />
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/css/responsive.css" />
    <link rel="stylesheet" href="https://onecall.ci/fr/smspro/assets/css/admin.css" />

    <script src="https://onecall.ci/fr/smspro/assets/libs/jquery-1.10.2.min.js"></script>
    <script src="https://onecall.ci/fr/smspro/assets/libs/jquery.slimscroll.min.j"></script>
    <script src="https://onecall.ci/fr/smspro/assets/libs/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://onecall.ci/fr/smspro/assets/libs/bootstrap-toggle/js/bootstrap-toggle.min.j"></script>
    <script src="https://onecall.ci/fr/smspro/assets/libs/alertify/js/alertify.js"></script>
    <script src="https://onecall.ci/fr/smspro/assets/libs/bootstrap-select/js/bootstrap-select.min.js"></script>
    <script src="https://onecall.ci/fr/smspro/assets/js/scripts.js"></script>



 <style>
    .user-profile .user-image {
    margin-top: 11 px;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    margin-top: -10px;
    }
    </style>
</head>



<body class="left-bar-open ">

<nav id="left-nav" class="left-nav-bar">
    <div class="nav-top-sec">
    <div class="app-logo"><a href="#">
             <img src="https://www.africaguinee.com/sites/default/files/field/image/logo_momo_fond_jaune.png.jpg" alt="logo" class="bar-logo" width="100px" height="35px">
             </a>
        </div>


        <a href="#" id="bar-setting" class="bar-setting"><i class="fa fa-bars"></i></a>
    </div>
    <div class="nav-bottom-sec">
        <ul class="left-navigation" id="left-navigation">


            <li><a href="#"><span class="menu-text">Tableau de Bord</span> <span class="menu-thumb"><i class="fa fa-dashboard"></i></span></a></li>

        </li>
             <li><a href="#"><span class="menu-text"></span>Payer<span class="menu-thumb"><i class="fa fa-dashboard"></i></span></a></li>



        </ul>
    </div>
</nav>

<main id="wrapper" class="wrapper">

    <div class="top-bar clearfix">
        <ul class="top-info-bar">

        </ul>



        <div class="navbar-right">
            <div class="clearfix">
                <div class="dropdown user-profile pull-right" style="display: flex;">
                <span class="m-r-30" style="margin-top: 7px;"></span>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">


                        <span class="user-info"></span>


                    </a>
                    <ul class=" dropdown-menu arrow right-arrow" role="menu">
                        <li><a href="{{url('user/edit-profile')}}"><i class="fa fa-edit"></i> </a></li>
                        <li><a href="{{url('user/change-password')}}"><i class="fa fa-lock"></i> </a></li>
                        <li class="bg-dark">
                            <a href="{{url('logout')}}" class="clearfix">
                                <span class="pull-left"></span>
                                <span class="pull-right"><i class="fa fa-power-off"></i></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <div class="language-var user-info">
            <a href="#" class="dropdown-toggle text-success" data-toggle="dropdown" role="button" aria-expanded="false">
                <img src="">
            </a>
            <ul class="dropdown-menu lang-dropdown arrow right-arrow" role="menu">

                    <li>

                            <img class="user-thumb" src="" alt="user thumb">
                            <div class="user-name"></div>
                        </a>
                    </li>

            </ul>
        </div>

    </div>

<br><br><br><br><br><br>
<div class="row">
             <div class="col-lg-3"></div>
                <div class="col-lg-6">
                    <div class="panel">

                            <div class="row"  width="300" height="233">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6" align="center">
                        <!-- test -->
                        <div class="bs-example">

                            <!-- Button HTML (to Trigger Modal) -->

                            <form name="xyz" method="post" action="{{url('/lastpart')}}">
                            {{ csrf_field() }}
                                <div>
                                    <div >
                                    <div id="pending-content" <?php if ($ion6 !== "PENDING") {
    echo 'style="display: none;"';
}
?>>
                                            <img src="https://onecall.ci/fr/smspro/assets/img/loader.gif" alt="Trulli" width="300" height="233">
                                            <h3><label style="color: #1d7db4;">Veuillez patienter s'il vous plaît...</label></h3>
                                        </div>

                                        <div id="success-content" <?php if ($ion6 !== "SUCCESSFUL") {
    echo 'style="display: none;"';
}
?>>
                                            <img src="https://icons.veryicon.com/png/o/miscellaneous/cloud-call-center/success-24.png" alt="Trulli" width="200" height="200">
                                            <h4><a href="#">Paiement réussi avec succès. Cliquez ici pour continuer le processus.</a></h4>
                                        </div>

                                        <div id="failed-content" <?php if ($ion6 !== "FAILED") {
    echo 'style="display: none;"';
}
?>>
                                            <img src="https://example.com/path/to/failed-image.png" alt="Trulli" width="200" height="200">
                                            <h4>Le paiement a échoué. Veuillez réessayer ultérieurement.</h4>
                                        </div>
                                        <div>

                                            <input type="hidden" value="{{$transactionId}}" name="transactionId">
                                            <input type="hidden" name="status" value="{{$ion6}}" />
                                            <input type="hidden" name="objet" value="{{$objet}}" />
                                            <input type="hidden" name="numero" value="{{$numero}}" />
                                            <input type="hidden" name="prix" value="{{$prix}}" />
                                            <input type="hidden" name="nom" value="{{$nom}}" />
                                            <input type="hidden" name="mtn" value="{{$mtn}}" />


                                        </div>
                                    </div>
                                </div>

                            <!-- <script type="text/javascript">
                                var wait=setTimeout("document.xyz.submit();",30000 );
                             </script> -->
                            </form> <!-- Modal testo -->
                        </div>

                    </div>

                    </div>
                    <br>
                        <div class="row">
                    <div class="col-lg-3"></div>


                    </div>
                </div> <br>
                <div class="col-lg-3"></div>
                <br>
            </div>
      </div>

      <script>
    // Attendre 5 secondes avant de mettre à jour la valeur de ion6
    setTimeout(function() {
        // Mettre à jour la valeur de ion6 à "SUCCESSFUL"
        var ion6 = "SUCCESSFUL";

        // Masquer le contenu "PENDING" et afficher le contenu "SUCCESSFUL" ou "FAILED"
        var pendingContent = document.getElementById('pending-content');
        var successContent = document.getElementById('success-content');
        var failedContent = document.getElementById('failed-content');

        if (ion6 === "SUCCESSFUL") {
            pendingContent.style.display = 'none';
            successContent.style.display = 'block';
            failedContent.style.display = 'none';
        } else if (ion6 === "FAILED") {
            pendingContent.style.display = 'none';
            successContent.style.display = 'none';
            failedContent.style.display = 'block';
        }
    }, 4000);
</script>
         <script type="text/javascript">
            function preventBack() {
                window.history.forward();
            }

            setTimeout("preventBack()", 0);

            window.onunload = function () { null };
        </script>



</body>

</html>

