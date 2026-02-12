<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <title>Facture</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* Ajoutez d'autres styles CSS au besoin */
        }

        table td,
        table th {
            padding: 0.4rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
            font-family: "Montserrat" !important;
        }
        
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row mb-2 mr-0 ml-0 col-sm-12">
            <div class="col-sm-4"></div>
            <div class="col-sm-12 d-flex justify-content-between">
                <img src="{{asset('images/logo.png') }}" alt="logo" class="" style="width: 130px !important; height: 50px !important" />
                <img src="{{asset('images/santee.png') }}" alt="logo" class="" style="width: 150px !important; height: 50px !important" />
            </div>
        </div>

        <hr style="background-color: rgb(156, 151, 151)" class="mt-n2" />

        <p>facture prestatation</p>

        <div class="row mb-1">
            <div class="col-sm-6">
                <h4>Partie versante :</h4>
                <p>
                    <strong>SID sid</strong>
                </p>
                <p>togdoud</p>
                <p>67756677</p>
                <p>
                    <strong>IPP :</strong> 222 |
                    <strong> IEP : 44 </strong>
                </p>
            </div>
            <div class="col-sm-6 text-right">
                <h4>Facture N° 3443</h4>
                <p>
                    
                <strong>Date : {{ isset($factures[0]['created_at']) ? \Carbon\Carbon::parse($factures[0]['created_at'])->format('Y-m-d H:i:s') : 'N/A' }}</strong>
                </p>
                <p><strong>LOKOSSA ATHIEME</strong></p>
                <p>Code postal 09 6 51 03</p>
                <p>En allant à LINK Hotel</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead style="padding: 3px !important; height: 10px">
                    <tr style="padding: 3px !important">
                        <!-- <th>Mode</th> -->
                        <th>code</th>
                        <th>Désignation</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                @php
                $total = 0; // Initialisez la variable totale
            @endphp

                    @foreach($factures as $facture)
                    <tr>
                        <!-- <td>{{ $facture['type'] }}</td> -->
                        <td>{{ $facture['code'] }}</td>
                        <td>{{ $facture['designation'] }}</td>
                        <td>{{ $facture['quantite'] }}</td>
                        <td>{{ $facture['prix'] }}</td>
                        <td>{{ $facture['montant'] }}</td>

                    </tr>
                    @php
                    $total += $facture['montant']; 
                @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">
                            <strong>Montant Total (+) </strong>
                        </td>
                        <td>
                            <strong>{{ $total }} FCFA</strong>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="5" class="text-right">
                            <strong> Total Prise en charge (-)</strong>
                        </td>
                        <td>
                            <strong>0 FCFA</strong>
                        </td>
                    </tr>


                    <tr>
                        <td colspan="5" class="text-right" style="background-color: rgb(213, 219, 218)">
                            <strong>Montant Total Versé</strong>
                        </td>
                        <td style="background-color: rgb(213, 219, 218)">
                            <strong>0 FCFA</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row mt-1">
            <div class="col-sm-6">

                <p>
                    la présente facture à la somme de :
                    <strong style="text-transform: capitalize">1000
                        FCFA
                    </strong>
                </p>

                <div>
                </div>
            </div>
            <div class="col-sm-6 text-right">
                <p><strong> Le Caissier </strong></p>
                <!-- <img src="@/assets/images/signature.png" alt="logo" class="" style="width: 80px" /> -->
                <p><strong> Félicien DAGBOGBO </strong></p>
            </div>
        </div>
    </div>

</body>

</html>