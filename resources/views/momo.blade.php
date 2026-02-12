

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>payer</title>
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
    
    .top-bar {
    background-color: #FFFFFF;
    min-height: 61px;
    position: fixed;
    width: 100%;
    top: 0px;
    left: 0px;
    box-shadow: 0px 0px 3px #efefef;
    z-index: 888;
    border-bottom: 1px solid #EFEFEF;
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
             <li><a href="#"><span class="menu-text"></span>PAYER<span class="menu-thumb"><i class="fa fa-dashboard"></i></span></a></li>
           
            

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

<br><br><br><br>
<div class="row">
             <div class="col-lg-3"></div>
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title"align="center"></h3>
                        </div>
                        <div class="panel-body" align="center">
                      
                       <h3>MTN MOBILE PAIEMENT </h3>
                        
                            
                        </div>
                            <div class="row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6" align="center">
                        <!-- test -->

                        <div id="frontendEditor">
                    
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#home" aria-controls="home" role="tab" data-toggle="tab">
                                <img src="https://www.africaguinee.com/sites/default/files/field/image/logo_momo_fond_jaune.png.jpg" width="70px" height="50px">
                                </a>
                            </li>
                            
                            
                        </ul>


        <div class="tab-content">
           
            <div role="tabpanel" class="tab-pane" id="messages">
            <!-- paiement Mtn -->
            <br>
             
            </div>
            <div role="tabpanel" class="tab-pane" id="settings">

                <!-- paiement orange -->
            <br>
            
            </div>
            <div role="tabpanel" class="tab-pane active" id="home">
            <form action="{{url('/smspayer_meth')}}" method="post">
                {{ csrf_field() }}
        
                    <!-- paiement mtn -->
                     <br>
                                   <label>Nom</label>
                                    <input type="text" class="form-control" name="nom"  required>
                                    <input type="text" class="form-control" name="prenom"  required>
                                    <label>Numero de Telephone</label>
                                    <input type="tel" class="form-control"  name="telephone"  placeholder="Téléphone: 229........" pattern="[0-9]{11}" required="Veuillez rentrer un numero a 10 chiffres svp">
                                    <input type="text" class="form-control" placeholder="Montant" name="montant">
                                    <input type="text" class="form-control" placeholder="objet" name="objet">
                                     <label style="color:red;">Pour confirmer le paiement Mtn Money, merci de taper #133#, choisir l'option 1, l'option 1 puis l'option 1. Puis entrez votre code secret et validez. </label>
                                 <br>
                                <button type="submit" class="btn btn-primary"  id="bt_get_signature">Payer</button>
                                
        
            </form>
                               
            <br>
            
            
            </div>
            
           
        </div>

        </div>


        </div>

                    </div>
                        
                    </div>
                    <br>
                        <div class="row">
                    <div class="col-lg-3"></div>
      